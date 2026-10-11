/* fx-catalog.js — filters, drawers, quick preview, compare, wishlist for fx pages.
   Vanilla JS. Cart/variant behavior stays in custom.js; injected quick-preview
   cart buttons are handled by delegation scoped to the quick-preview body only
   (the page's own buttons already have direct handlers in custom.js). */
"use strict";

(function () {
    var COMPARE_KEY = "fx-compare-items";

    document.addEventListener("DOMContentLoaded", function () {
        initScrollRestore();
        initFilterSheet();
        initChipRemove();
        initWishlist();
        initQuickPreview();
        initCompare();
    });

    /* ---------- toast ---------- */
    function toast(message) {
        var zone = document.querySelector("[data-fx-toast-zone]");
        if (!zone) return;
        var el = document.createElement("div");
        el.className = "fx-toast";
        el.textContent = message;
        zone.appendChild(el);
        setTimeout(function () { el.classList.add("is-out"); }, 2600);
        setTimeout(function () { el.remove(); }, 3100);
    }

    /* ---------- scroll restoration across filter submits ---------- */
    function initScrollRestore() {
        var form = document.querySelector("[data-fx-filter-form]");
        if (form) {
            form.addEventListener("submit", function () {
                try {
                    sessionStorage.setItem("fx-scroll-url", location.href.split("#")[0]);
                    sessionStorage.setItem("fx-scroll-y", String(window.scrollY));
                } catch (e) { /* storage unavailable */ }
            });
        }
        try {
            var savedUrl = sessionStorage.getItem("fx-scroll-url");
            var savedY = sessionStorage.getItem("fx-scroll-y");
            if (savedUrl && savedY && savedUrl === location.href.split("#")[0]) {
                sessionStorage.removeItem("fx-scroll-url");
                sessionStorage.removeItem("fx-scroll-y");
                var y = parseInt(savedY, 10);
                if (!isNaN(y) && y > 0) requestAnimationFrame(function () { window.scrollTo(0, y); });
            }
        } catch (e) { /* storage unavailable */ }
    }

    /* ---------- generic accessible drawer ---------- */
    function makeDrawer(drawer, overlay, openTriggers, opts) {
        opts = opts || {};
        var toggleClass = opts.toggleClass || null;
        var lastFocus = null;
        var sentinelStart = null, sentinelEnd = null;

        function isOpen() {
            return toggleClass ? drawer.classList.contains(toggleClass) : !drawer.hidden;
        }

        function open(trigger) {
            lastFocus = trigger || document.activeElement;
            if (toggleClass) drawer.classList.add(toggleClass);
            else drawer.hidden = false;
            if (overlay) overlay.hidden = false;
            document.body.style.overflow = "hidden";
            trap();
            var first = drawer.querySelector("[data-fx-drawer-autofocus]") || drawer.querySelector("button, a, input");
            if (first) first.focus();
            if (opts.onOpen) opts.onOpen();
        }

        function close() {
            if (toggleClass) drawer.classList.remove(toggleClass);
            else drawer.hidden = true;
            if (overlay) overlay.hidden = true;
            document.body.style.overflow = "";
            release();
            if (lastFocus && lastFocus.focus) lastFocus.focus();
            if (opts.onClose) opts.onClose();
        }

        function trap() {
            sentinelStart = document.createElement("span");
            sentinelEnd = document.createElement("span");
            sentinelStart.tabIndex = 0;
            sentinelEnd.tabIndex = 0;
            sentinelStart.setAttribute("aria-hidden", "true");
            sentinelEnd.setAttribute("aria-hidden", "true");
            drawer.prepend(sentinelStart);
            drawer.append(sentinelEnd);
            sentinelStart.addEventListener("focus", function () {
                var focusables = focusable();
                if (focusables.length) focusables[focusables.length - 1].focus();
            });
            sentinelEnd.addEventListener("focus", function () {
                var focusables = focusable();
                if (focusables.length) focusables[0].focus();
            });
        }

        function focusable() {
            return Array.prototype.filter.call(
                drawer.querySelectorAll("button, a[href], input, select, textarea, [tabindex]:not([tabindex='-1'])"),
                function (el) { return !el.disabled && el.offsetParent !== null && el !== sentinelStart && el !== sentinelEnd; }
            );
        }

        function release() {
            if (sentinelStart) sentinelStart.remove();
            if (sentinelEnd) sentinelEnd.remove();
            sentinelStart = sentinelEnd = null;
        }

        (openTriggers || []).forEach(function (sel) {
            document.querySelectorAll(sel).forEach(function (btn) {
                btn.addEventListener("click", function () { open(btn); });
            });
        });
        drawer.querySelectorAll("[data-fx-drawer-close]").forEach(function (btn) {
            btn.addEventListener("click", close);
        });
        if (overlay) overlay.addEventListener("click", close);
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && isOpen()) close();
        });

        return { open: open, close: close, isOpen: isOpen };
    }

    /* ---------- mobile filter sheet ---------- */
    function initFilterSheet() {
        var rail = document.querySelector("[data-fx-filter-rail]");
        if (!rail) return;
        var overlay = document.querySelector("[data-fx-filter-overlay]");
        var drawer = makeDrawer(rail, overlay, ["[data-fx-open-filters]"], { toggleClass: "is-open" });
        rail.querySelectorAll("[data-fx-close-filters]").forEach(function (btn) {
            btn.addEventListener("click", function () { drawer.close(); });
        });
        // On desktop the rail is always visible; keep the drawer hidden state in sync.
        var mq = window.matchMedia("(max-width: 1023px)");
        function sync() {
            if (!mq.matches) {
                rail.classList.remove("is-open");
                document.body.style.overflow = "";
                if (overlay) overlay.hidden = true;
            }
        }
        mq.addEventListener ? mq.addEventListener("change", sync) : mq.addListener(sync);
        sync();
    }

    /* ---------- selected-filter chips ---------- */
    function initChipRemove() {
        document.querySelectorAll("[data-fx-chip-remove]").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var field = btn.getAttribute("data-fx-chip-remove");
                var value = btn.getAttribute("data-fx-chip-value");
                var url = new URL(location.href);
                if (field === "price") {
                    url.searchParams.delete("min_price");
                    url.searchParams.delete("max_price");
                } else if (value) {
                    var kept = url.searchParams.getAll(field + "[]").filter(function (v) { return v !== value; });
                    url.searchParams.delete(field + "[]");
                    kept.forEach(function (v) { url.searchParams.append(field + "[]", v); });
                } else {
                    url.searchParams.delete(field);
                }
                url.searchParams.delete("page");
                location.href = url.toString();
            });
        });
    }

    /* ---------- wishlist (signed-in backend; login modal when anonymous) ---------- */
    function initWishlist() {
        document.querySelectorAll(".fx-save-btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var productId = btn.getAttribute("data-product-id");
                if (!productId) return;
                if (typeof window.addWishlist === "function") {
                    window.addWishlist(productId, "");
                    // Optimistic toggle; the backend re-syncs counts via custom.js.
                    var saved = btn.getAttribute("aria-pressed") === "true";
                    btn.setAttribute("aria-pressed", saved ? "false" : "true");
                    btn.classList.toggle("is-saved", !saved);
                    var glyph = btn.querySelector("span");
                    if (glyph) glyph.textContent = saved ? "♡" : "♥";
                } else {
                    toast("Saving is unavailable right now.");
                }
            });
        });
    }

    /* ---------- quick preview drawer ---------- */
    function initQuickPreview() {
        var drawer = document.querySelector("[data-fx-qp-drawer]");
        var overlay = document.querySelector("[data-fx-qp-overlay]");
        var body = document.querySelector("[data-fx-qp-body]");
        if (!drawer || !body) return;

        var qp = makeDrawer(drawer, overlay, []);
        var aborter = null;

        // Delegated cart buttons for injected content (page buttons keep custom.js direct handlers).
        body.addEventListener("click", function (e) {
            var buy = e.target.closest(".product-buy-now-button");
            var add = e.target.closest(".product-add-to-cart-button");
            if (typeof window.addToCart !== "function") return;
            if (buy) {
                window.addToCart(buy.getAttribute("data-form") || ".fx-qp__form", buy.getAttribute("data-auth"), buy.getAttribute("data-route"));
                if (buy.getAttribute("data-auth") === "false") {
                    setTimeout(function () { location.href = buy.getAttribute("data-route"); }, 100);
                }
                return;
            }
            if (add) {
                var container = add.closest(".product-cart-option-container");
                var form = container ? container.querySelector(".addToCartDynamicForm") : null;
                window.addToCart(form || ".fx-qp__form");
            }
        });

        document.querySelectorAll("[data-fx-quick-preview]").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var productId = btn.getAttribute("data-fx-quick-preview");
                var url = btn.getAttribute("data-fx-preview-url");
                if (!productId || !url) return;
                qp.open(btn);
                if (aborter) aborter.abort();
                aborter = new AbortController();
                body.innerHTML = '<div class="fx-skeleton" style="height:220px"></div>' +
                    '<div class="fx-skeleton" style="height:20px;margin-top:12px"></div>' +
                    '<div class="fx-skeleton" style="height:20px;margin-top:8px;width:60%"></div>';
                fetch(url, { signal: aborter.signal, headers: { "Accept": "application/json" } })
                    .then(function (r) { if (!r.ok) throw new Error("bad response"); return r.json(); })
                    .then(function (data) {
                        if (data && data.ok && data.html) {
                            body.innerHTML = data.html;
                        } else {
                            throw new Error("empty");
                        }
                    })
                    .catch(function (err) {
                        if (err && err.name === "AbortError") return;
                        body.innerHTML = '<div class="fx-qp__error" role="alert"><p>Preview could not be loaded.</p>' +
                            '<button type="button" class="fx-btn fx-btn-sm fx-btn-outline" data-fx-qp-retry>Retry</button></div>';
                        var retry = body.querySelector("[data-fx-qp-retry]");
                        if (retry) retry.addEventListener("click", function () { btn.click(); });
                    });
            });
        });
        drawer.querySelectorAll("[data-fx-qp-close]").forEach(function (btn) {
            btn.addEventListener("click", function () { qp.close(); });
        });
    }

    /* ---------- compare (max 4, same listing type) ---------- */
    function loadCompare() {
        try {
            var raw = localStorage.getItem(COMPARE_KEY);
            var items = raw ? JSON.parse(raw) : [];
            return Array.isArray(items) ? items : [];
        } catch (e) { return []; }
    }

    function saveCompare(items) {
        try { localStorage.setItem(COMPARE_KEY, JSON.stringify(items)); } catch (e) { /* ignore */ }
    }

    function cardInfo(btn) {
        var card = btn.closest("[data-fx-card]");
        return {
            id: btn.getAttribute("data-product-id") || (card && card.getAttribute("data-product-id")),
            type: (card && card.getAttribute("data-fx-type")) || btn.getAttribute("data-fx-type") || "",
            name: (card && card.getAttribute("data-product-name")) || btn.getAttribute("data-product-name") || "Listing",
        };
    }

    function initCompare() {
        var tray = document.querySelector("[data-fx-compare-tray]");
        var drawer = document.querySelector("[data-fx-cmp-drawer]");
        if (!tray || !drawer) return;
        var overlay = document.querySelector("[data-fx-cmp-overlay]");
        var trayItems = tray.querySelector("[data-fx-tray-items]");
        var trayCount = tray.querySelector("[data-fx-tray-count]");
        var cmpBody = drawer.querySelector("[data-fx-cmp-body]");
        var cmp = makeDrawer(drawer, overlay, []);

        renderTray();

        document.querySelectorAll(".fx-compare-btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var info = cardInfo(btn);
                if (!info.id) return;
                var items = loadCompare();
                if (items.some(function (i) { return String(i.id) === String(info.id); })) {
                    toast("Already in compare.");
                    return;
                }
                if (items.length >= 4) {
                    toast("Compare is limited to 4 listings.");
                    return;
                }
                if (items.length > 0 && items[0].type !== info.type) {
                    toast("Compare works with listings of the same type. Clear the tray first.");
                    return;
                }
                items.push(info);
                saveCompare(items);
                renderTray();
                toast("Added to compare (" + items.length + " of 4).");
            });
        });

        tray.querySelector("[data-fx-tray-clear]").addEventListener("click", function () {
            saveCompare([]);
            renderTray();
        });

        tray.querySelector("[data-fx-tray-open]").addEventListener("click", function () {
            var items = loadCompare();
            if (!items.length) return;
            cmp.open();
            loadTable(items);
        });

        drawer.querySelectorAll("[data-fx-cmp-close]").forEach(function (btn) {
            btn.addEventListener("click", function () { cmp.close(); });
        });

        cmpBody.addEventListener("click", function (e) {
            var rm = e.target.closest("[data-fx-cmp-remove]");
            if (rm) {
                var id = rm.getAttribute("data-fx-cmp-remove");
                saveCompare(loadCompare().filter(function (i) { return String(i.id) !== String(id); }));
                renderTray();
                var items = loadCompare();
                if (items.length) loadTable(items);
                else cmp.close();
            }
        });

        function renderTray() {
            var items = loadCompare();
            tray.hidden = items.length === 0;
            trayItems.innerHTML = "";
            items.forEach(function (item) {
                var chip = document.createElement("span");
                chip.className = "fx-chip";
                chip.textContent = item.name.length > 28 ? item.name.slice(0, 28) + "…" : item.name;
                var x = document.createElement("button");
                x.type = "button";
                x.className = "fx-chip__x";
                x.setAttribute("aria-label", "Remove from compare");
                x.textContent = "×";
                x.addEventListener("click", function () {
                    saveCompare(loadCompare().filter(function (i) { return String(i.id) !== String(item.id); }));
                    renderTray();
                });
                chip.appendChild(x);
                trayItems.appendChild(chip);
            });
            if (trayCount) trayCount.textContent = items.length + " of 4 selected";
        }

        function loadTable(items) {
            var base = drawer.getAttribute("data-compare-url");
            if (!base) return;
            var params = items.map(function (i) { return "ids[]=" + encodeURIComponent(i.id); }).join("&");
            cmpBody.innerHTML = '<div class="fx-skeleton" style="height:300px"></div>';
            fetch(base + "?" + params, { headers: { "Accept": "application/json" } })
                .then(function (r) { if (!r.ok) throw new Error("bad response"); return r.json(); })
                .then(function (data) {
                    if (data && data.ok && data.html) {
                        cmpBody.innerHTML = data.html;
                        // Reconcile tray with server-enforced set (same type, max 4).
                        if (data.ids) {
                            var kept = loadCompare().filter(function (i) {
                                return data.ids.indexOf(parseInt(i.id, 10)) !== -1 || data.ids.indexOf(String(i.id)) !== -1;
                            });
                            saveCompare(kept);
                            renderTray();
                        }
                    } else {
                        throw new Error("empty");
                    }
                })
                .catch(function () {
                    cmpBody.innerHTML = '<div class="fx-qp__error" role="alert"><p>Comparison could not be loaded.</p>' +
                        '<button type="button" class="fx-btn fx-btn-sm fx-btn-outline" data-fx-cmp-retry>Retry</button></div>';
                    var retry = cmpBody.querySelector("[data-fx-cmp-retry]");
                    if (retry) retry.addEventListener("click", function () { loadTable(loadCompare()); });
                });
        }
    }
})();
