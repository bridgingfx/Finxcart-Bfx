document.addEventListener("DOMContentLoaded", function () {
    initCategoryRowRepeater();
});

function initCategoryRowRepeater() {
    let $wrapper = $("#category-rows-wrapper");
    if ($wrapper.length === 0) {
        return;
    }

    let url = $wrapper.data("get-categories-url");
    let allCategoriesUrl = $wrapper.data("get-all-categories-url");
    let placeholders = {
        category: $wrapper.data("placeholder-category"),
        sub_category: $wrapper.data("placeholder-sub-category"),
        sub_sub_category: $wrapper.data("placeholder-sub-sub-category"),
    };
    let loadingText = $wrapper.data("placeholder-loading") || "Loading...";
    let selectWordText = $wrapper.data("placeholder-select") || "Select";

    function placeholderOption(level) {
        return '<option value="">' + placeholders[level] + "</option>";
    }

    // Row 1 (the main category — whether pre-filled on the edit page or empty
    // on add) keeps this exact AJAX-per-selection behavior, unchanged from
    // before multi-category support existed. loadLevel() is only ever called
    // for that first row now — see buildLevelFromCache() below for every row
    // added afterward via "+ Add another category".
    function loadLevel($select, parentId, selectedId, level) {
        if (!parentId) {
            $select.prop("disabled", true).html(placeholderOption(level));
            return $.Deferred().resolve().promise();
        }
        // Show a "Loading..." option immediately instead of leaving the select
        // showing its previous (now stale) options while disabled — on a cold
        // PHP worker this request can take a few hundred ms, and a disabled
        // dropdown with old-looking content reads as the page being stuck
        // rather than a request genuinely in flight.
        $select.prop("disabled", true).html('<option value="">' + loadingText + '</option>');
        return $.get(url, { parent_id: parentId, sub_category: selectedId || "" }).done(function (data) {
            $select.prop("disabled", false).html(data.select_tag);
        });
    }

    // ---- Background prefetch of the full category tree, for row 2+ ----
    // The tree is small (a few hundred rows total across all 3 levels), so one
    // request fetched in the background as soon as the page opens is enough to
    // build every additional row's dropdowns instantly and locally — no
    // network round-trip, no loading state — while row 1 keeps behaving
    // exactly as it always has.
    let categoriesByParent = null; // parent_id -> [{id, name}], populated once the prefetch resolves
    let categoriesById = null;     // id -> {id, parent_id, position, name}
    let prefetchPromise = null;

    function prefetchAllCategories() {
        if (!allCategoriesUrl || prefetchPromise) {
            return prefetchPromise || $.Deferred().reject().promise();
        }
        prefetchPromise = $.get(allCategoriesUrl).done(function (data) {
            let list = (data && data.categories) || [];
            categoriesByParent = {};
            categoriesById = {};
            list.forEach(function (cat) {
                categoriesById[cat.id] = cat;
                let key = String(cat.parent_id);
                if (!categoriesByParent[key]) {
                    categoriesByParent[key] = [];
                }
                categoriesByParent[key].push(cat);
            });
        });
        return prefetchPromise;
    }

    // Mirrors ProductService::getCategoryDropdown()'s HTML shape exactly, so a
    // row built from the cache looks identical to one built from the AJAX
    // endpoint (same placeholder wording, same disabled/selected option).
    function optionsHtmlFor(parentId, selectedId) {
        let children = (categoriesByParent && categoriesByParent[String(parentId)]) || [];
        let html = '<option value="0" disabled selected>---' + selectWordText + '---</option>';
        children.forEach(function (cat) {
            html += '<option value="' + cat.id + '"' + (String(cat.id) === String(selectedId) ? ' selected' : '') + '>' + cat.name + '</option>';
        });
        return html;
    }

    // Fills a row's sub-category/sub-sub-category selects from the already-
    // fetched in-memory tree — synchronous, no request, no loading state.
    function buildLevelFromCache($select, parentId, selectedId, level) {
        if (!parentId || !categoriesByParent) {
            $select.prop("disabled", true).html(placeholderOption(level));
            return;
        }
        let hasChildren = !!(categoriesByParent[String(parentId)] && categoriesByParent[String(parentId)].length);
        if (!hasChildren) {
            $select.prop("disabled", true).html(placeholderOption(level));
            return;
        }
        $select.prop("disabled", false).html(optionsHtmlFor(parentId, selectedId));
    }

    function collectedIds() {
        let ids = [];
        $wrapper.find(".category-level-select").each(function () {
            let val = $(this).val();
            if (val && val !== "0") {
                ids.push(val);
            }
        });
        return Array.from(new Set(ids));
    }

    function syncHiddenInputs() {
        let $form = $wrapper.closest("form");
        $form.find("input.category-hidden-input").remove();
        collectedIds().forEach(function (id) {
            $form.append($("<input>", { type: "hidden", class: "category-hidden-input", name: "categories[]", value: id }));
        });
    }

    function updateRemoveButtons() {
        let $rows = $wrapper.find(".category-row");
        $rows.find(".remove-category-row").toggle($rows.length > 1);
    }

    // isPrimaryRow: true only for the very first row on the page (pre-filled
    // on edit, or empty on add) — that row alone keeps the original
    // AJAX-per-selection behavior. Every row added afterward via the "+ Add
    // another category" button builds instantly from the prefetched tree.
    function bindRow($row, isPrimaryRow) {
        let $category = $row.find('[data-level="category"]');
        let $subCategory = $row.find('[data-level="sub_category"]');
        let $subSubCategory = $row.find('[data-level="sub_sub_category"]');

        if (isPrimaryRow) {
            $category.on("change", function () {
                loadLevel($subCategory, $(this).val(), null, "sub_category").then(function () {
                    $subSubCategory.prop("disabled", true).html(placeholderOption("sub_sub_category"));
                    syncHiddenInputs();
                });
            });

            $subCategory.on("change", function () {
                loadLevel($subSubCategory, $(this).val(), null, "sub_sub_category").then(syncHiddenInputs);
            });
        } else {
            $category.on("change", function () {
                buildLevelFromCache($subCategory, $(this).val(), null, "sub_category");
                $subSubCategory.prop("disabled", true).html(placeholderOption("sub_sub_category"));
                syncHiddenInputs();
            });

            $subCategory.on("change", function () {
                buildLevelFromCache($subSubCategory, $(this).val(), null, "sub_sub_category");
                syncHiddenInputs();
            });
        }

        $subSubCategory.on("change", syncHiddenInputs);

        $row.find(".remove-category-row").on("click", function () {
            if ($wrapper.find(".category-row").length > 1) {
                $row.remove();
                updateRemoveButtons();
                syncHiddenInputs();
            }
        });
    }

    function addRow(prefill, isPrimaryRow) {
        let $row = $(".category-row-template .category-row").clone();
        $wrapper.append($row);
        bindRow($row, isPrimaryRow);

        if (prefill && prefill.category_id) {
            $row.find('[data-level="category"]').val(prefill.category_id);
            if (isPrimaryRow) {
                let subCategoryLoad = loadLevel($row.find('[data-level="sub_category"]'), prefill.category_id, prefill.sub_category_id, "sub_category");
                if (prefill.sub_category_id) {
                    let subSubCategoryLoad = loadLevel($row.find('[data-level="sub_sub_category"]'), prefill.sub_category_id, prefill.sub_sub_category_id, "sub_sub_category");
                    $.when(subCategoryLoad, subSubCategoryLoad).then(syncHiddenInputs);
                } else {
                    subCategoryLoad.then(syncHiddenInputs);
                }
            } else {
                // Additional pre-filled rows (editing a product that already has
                // several categories assigned): fill instantly once the
                // background prefetch has resolved, same as a fresh manually
                // added row — no per-row AJAX call.
                prefetchAllCategories().then(function () {
                    buildLevelFromCache($row.find('[data-level="sub_category"]'), prefill.category_id, prefill.sub_category_id, "sub_category");
                    if (prefill.sub_category_id) {
                        buildLevelFromCache($row.find('[data-level="sub_sub_category"]'), prefill.sub_category_id, prefill.sub_sub_category_id, "sub_sub_category");
                    }
                    syncHiddenInputs();
                });
            }
        }

        updateRemoveButtons();
        return $row;
    }

    let selectedRowsData = [];
    try {
        let dataEl = document.getElementById("selected-category-rows-data");
        selectedRowsData = dataEl ? JSON.parse(dataEl.textContent || "[]") : [];
    } catch (e) {
        selectedRowsData = [];
    }

    // Kick off the background prefetch immediately — by the time a vendor
    // clicks "+ Add another category" (a deliberate, not-instant action) it's
    // essentially always already resolved.
    prefetchAllCategories();

    if (selectedRowsData.length > 0) {
        selectedRowsData.forEach(function (row, index) {
            addRow(row, index === 0);
        });
    } else {
        addRow(null, true);
    }

    $("#add-category-row-btn").on("click", function () {
        addRow(null, false);
    });

    syncHiddenInputs();
}
