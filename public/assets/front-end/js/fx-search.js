/* fx-search.js — grouped autocomplete for fx search forms.
   Behaviors: 250ms debounce, abort stale requests, keyboard navigation,
   error state with Retry, full-page GET submit for "see all results". */
"use strict";

(function () {
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll("[data-fx-search-form]").forEach(initSearchForm);
    });

    function initSearchForm(form) {
        var input = form.querySelector("[data-fx-search-input]");
        var suggest = form.querySelector("[data-fx-suggest]");
        if (!input || !suggest) return;
        var suggestUrl = form.getAttribute("data-suggest-url");
        if (!suggestUrl) return;

        var timer = null;
        var controller = null;
        var activeIndex = -1;
        var items = [];

        input.addEventListener("input", function () {
            clearTimeout(timer);
            var q = input.value.trim();
            if (q.length < 2) {
                close();
                return;
            }
            timer = setTimeout(function () { fetchSuggest(q); }, 250);
        });

        input.addEventListener("keydown", function (e) {
            if (suggest.hidden) return;
            if (e.key === "ArrowDown") {
                e.preventDefault();
                move(1);
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                move(-1);
            } else if (e.key === "Enter") {
                if (activeIndex >= 0 && items[activeIndex]) {
                    e.preventDefault();
                    window.location.href = items[activeIndex].url;
                }
            } else if (e.key === "Escape") {
                close();
            }
        });

        document.addEventListener("click", function (e) {
            if (!form.contains(e.target)) close();
        });
        form.addEventListener("submit", function () { close(); });

        function move(dir) {
            if (!items.length) return;
            activeIndex = (activeIndex + dir + items.length) % items.length;
            render(items, suggest.dataset.query || "");
            var el = suggest.querySelectorAll("[role=option]")[activeIndex];
            if (el) {
                el.focus();
                input.setAttribute("aria-activedescendant", el.id);
            }
        }

        function open() {
            suggest.hidden = false;
            input.setAttribute("aria-expanded", "true");
        }

        function close() {
            suggest.hidden = true;
            input.setAttribute("aria-expanded", "false");
            input.removeAttribute("aria-activedescendant");
            activeIndex = -1;
            items = [];
        }

        function fetchSuggest(q) {
            if (controller) controller.abort();
            controller = new AbortController();
            fetch(suggestUrl + "?q=" + encodeURIComponent(q), {
                signal: controller.signal,
                headers: { "Accept": "application/json" },
            })
                .then(function (r) {
                    if (!r.ok) throw new Error("bad response");
                    return r.json();
                })
                .then(function (data) {
                    if (input.value.trim() !== q) return; // stale
                    render(data, q);
                    open();
                })
                .catch(function (err) {
                    if (err && err.name === "AbortError") return;
                    renderError(q);
                    open();
                });
        }

        function optionHtml(item, idx, label) {
            return '<a role="option" tabindex="-1" id="' + suggest.id + '-opt-' + idx + '" href="' + item.url + '"' +
                ' class="fx-suggest__option"' + (idx === activeIndex ? ' aria-selected="true"' : '') + '>' +
                (item.thumbnail ? '<img src="' + item.thumbnail + '" alt="" loading="lazy" width="40" height="40">' : "") +
                '<span class="fx-suggest__text"><strong>' + escapeHtml(item.name) + '</strong>' +
                (item.tag ? '<small>' + escapeHtml(item.tag) + '</small>' : "") +
                (item.price ? '<small>' + escapeHtml(item.price) + '</small>' : "") +
                '</span></a>';
        }

        function render(data, q) {
            suggest.dataset.query = q;
            var html = "";
            items = [];
            var idx = 0;

            if (data.products && data.products.length) {
                html += '<div class="fx-suggest__group" role="group" aria-label="Listings"><p class="fx-suggest__label">Listings</p>';
                data.products.forEach(function (p) {
                    html += optionHtml(p, idx, "listing");
                    items.push({ url: p.url });
                    idx++;
                });
                html += "</div>";
            }
            if (data.categories && data.categories.length) {
                html += '<div class="fx-suggest__group" role="group" aria-label="Categories"><p class="fx-suggest__label">Categories</p>';
                data.categories.forEach(function (c) {
                    html += '<a role="option" tabindex="-1" id="' + suggest.id + '-opt-' + idx + '" href="' + c.url + '" class="fx-suggest__option">' +
                        '<span class="fx-suggest__text"><strong>' + escapeHtml(c.name) + '</strong>' +
                        (c.count ? '<small>' + c.count + ' listings</small>' : "") + '</span></a>';
                    items.push({ url: c.url });
                    idx++;
                });
                html += "</div>";
            }
            if (data.providers && data.providers.length) {
                html += '<div class="fx-suggest__group" role="group" aria-label="Providers"><p class="fx-suggest__label">Providers</p>';
                data.providers.forEach(function (s) {
                    html += '<a role="option" tabindex="-1" id="' + suggest.id + '-opt-' + idx + '" href="' + s.url + '" class="fx-suggest__option">' +
                        (s.image ? '<img src="' + s.image + '" alt="" loading="lazy" width="40" height="40">' : "") +
                        '<span class="fx-suggest__text"><strong>' + escapeHtml(s.name) + '</strong><small>Provider</small></span></a>';
                    items.push({ url: s.url });
                    idx++;
                });
                html += "</div>";
            }

            if (!idx) {
                html = '<p class="fx-suggest__empty">No matches for &ldquo;' + escapeHtml(q) + '&rdquo;.</p>';
            }
            html += '<div class="fx-suggest__foot"><button type="submit" class="fx-suggest__all">See all results for &ldquo;' +
                escapeHtml(q) + '&rdquo;</button></div>';
            suggest.innerHTML = html;
        }

        function renderError(q) {
            items = [];
            activeIndex = -1;
            suggest.innerHTML = '<div class="fx-suggest__error" role="alert">' +
                '<p>Search suggestions are unavailable right now.</p>' +
                '<button type="button" class="fx-btn fx-btn-sm fx-btn-outline" data-fx-suggest-retry>Retry</button></div>';
            var retry = suggest.querySelector("[data-fx-suggest-retry]");
            if (retry) retry.addEventListener("click", function () { fetchSuggest(q); });
        }

        function escapeHtml(str) {
            return String(str == null ? "" : str)
                .replace(/&/g, "&amp;").replace(/</g, "&lt;")
                .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }
    }
})();
