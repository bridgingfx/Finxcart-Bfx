"use strict";

/**
 * Shared "Load More" / "Show Less" behavior for product grids.
 * Mirrors the Latest Products section's proven pattern (fetch -> append -> rebind
 * card handlers) so every section gets the same on-demand loading without
 * re-fetching anything already on the page.
 *
 * config:
 *   moreBtnId, lessBtnId, containerId  - element ids (required)
 *   url                                 - endpoint to fetch (required)
 *   paramName                           - 'page' or 'offset' query param to advance (default 'offset')
 *   startValue                          - value of paramName for the *next* fetch (default: current item count)
 *   step                                 - how much to advance paramName by each click (default: current item count)
 *   htmlField                           - response field containing the HTML to append (default 'html')
 *   totalField                          - response field with the grand total count, used to compute hasMore
 *                                          when the response has no explicit `hasMore` boolean
 *   extraParams                         - optional function returning an object of extra query params
 */
function initLoadMoreSection(config) {
    var moreBtn = document.getElementById(config.moreBtnId);
    var lessBtn = document.getElementById(config.lessBtnId);
    var container = document.getElementById(config.containerId);
    if (!moreBtn || !lessBtn || !container) {
        return;
    }

    var paramName = config.paramName || 'offset';
    var htmlField = config.htmlField || 'html';
    var initialCount = container.children.length;
    var step = config.step || initialCount || 12;
    var nextValue = config.startValue != null ? config.startValue : initialCount;
    var startValue = nextValue;

    function bindNewCards(cards) {
        cards.forEach(function (card) {
            card.querySelectorAll('.product-action-add-wishlist').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (typeof addWishlist === 'function') {
                        addWishlist(btn.getAttribute('data-product-id'));
                    }
                });
            });
            card.querySelectorAll('.stopPropagation').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            });
            card.querySelectorAll('.action-product-quick-view').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (typeof productQuickView === 'function') {
                        productQuickView(btn.getAttribute('data-product-id'));
                    }
                });
            });
            card.querySelectorAll('.clickable').forEach(function (el) {
                el.addEventListener('click', function () {
                    var link = el.querySelector('a');
                    if (link) {
                        window.location = link.getAttribute('href');
                    }
                });
            });
        });
    }

    moreBtn.addEventListener('click', function () {
        moreBtn.disabled = true;

        var params = new URLSearchParams(config.extraParams ? config.extraParams() : {});
        params.set(paramName, nextValue);

        fetch(config.url + '?' + params.toString(), {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                var beforeCount = container.children.length;
                container.insertAdjacentHTML('beforeend', data[htmlField] || '');
                bindNewCards(Array.prototype.slice.call(container.children, beforeCount));
                if (window.AOS) {
                    AOS.refresh();
                }
                if (typeof renderQuickViewFunction === 'function') {
                    renderQuickViewFunction();
                }

                var afterCount = container.children.length;
                var gotNewItems = afterCount > beforeCount;

                nextValue = paramName === 'page' ? nextValue + 1 : nextValue + step;

                var hasMore;
                if (typeof data.hasMore === 'boolean') {
                    hasMore = data.hasMore;
                } else if (config.totalField && typeof data[config.totalField] === 'number') {
                    hasMore = afterCount < data[config.totalField];
                } else {
                    hasMore = gotNewItems;
                }

                moreBtn.disabled = false;
                if (gotNewItems) {
                    lessBtn.classList.remove('d-none');
                }
                if (!hasMore || !gotNewItems) {
                    moreBtn.classList.add('d-none');
                }
            })
            .catch(function () {
                moreBtn.disabled = false;
            });
    });

    lessBtn.addEventListener('click', function () {
        Array.prototype.slice.call(container.children, initialCount).forEach(function (card) {
            card.remove();
        });

        nextValue = startValue;
        moreBtn.classList.remove('d-none');
        lessBtn.classList.add('d-none');
    });
}
