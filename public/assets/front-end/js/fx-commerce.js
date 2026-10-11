/* fx-commerce.js — presentation helpers for cart, checkout, auth, account.
 * Additive only: wraps existing flows, never replaces backend behavior.
 * Loaded per-page after the page's own scripts. */
(function () {
    "use strict";

    /* ---------- 1. Confirm before remove (cart) ----------
     * Presentation-only: asks for confirmation, then calls the existing
     * removal flow. No re-add/undo: there is no safe restore endpoint, and
     * re-adding could lose variant/option state. */
    function bindCartRemoveConfirm() {
        if (typeof window.removeProductFromCartList !== "function") return;
        if (window.__fxRemoveWrapped) return;
        window.__fxRemoveWrapped = true;

        var originalRemove = window.removeProductFromCartList;

        window.removeProductFromCartList = function (key) {
            var $row = document.querySelector('.fx-cart-item[data-cart-key="' + key + '"]');
            var name = $row ? (($row.querySelector(".fx-cart-item__name") || {}).textContent || "").trim() : "";
            var msg = window.__fxRemoveConfirmText || "Remove this item from your cart?";
            if (name) msg = msg + "\n" + name.slice(0, 80);
            if (window.confirm(msg)) {
                originalRemove(key);
            }
        };

        // Explicit remove buttons (same path).
        document.addEventListener("click", function (e) {
            var btn = e.target.closest ? e.target.closest(".fx-remove-key") : null;
            if (btn && btn.getAttribute("data-remove-key")) {
                e.preventDefault();
                window.removeProductFromCartList(btn.getAttribute("data-remove-key"));
            }
        });
    }

    /* ---------- 2. OTP auto-advance ---------- */
    function bindOtpInputs() {
        var inputs = Array.prototype.slice.call(document.querySelectorAll("[data-otp-input]"));
        if (!inputs.length) return;
        inputs.forEach(function (input, idx) {
            input.addEventListener("input", function () {
                input.value = input.value.replace(/[^0-9]/g, "").slice(0, 1);
                if (input.value && inputs[idx + 1]) inputs[idx + 1].focus();
                syncOtpHidden();
            });
            input.addEventListener("keydown", function (e) {
                if (e.key === "Backspace" && !input.value && inputs[idx - 1]) {
                    inputs[idx - 1].focus();
                }
            });
            input.addEventListener("paste", function (e) {
                var text = (e.clipboardData || window.clipboardData).getData("text") || "";
                text = text.replace(/[^0-9]/g, "");
                if (!text) return;
                e.preventDefault();
                for (var i = 0; i < text.length && idx + i < inputs.length; i++) {
                    inputs[idx + i].value = text[i];
                }
                var next = inputs[Math.min(idx + text.length, inputs.length - 1)];
                if (next) next.focus();
                syncOtpHidden();
            });
        });
        function syncOtpHidden() {
            var hidden = document.querySelector("[data-otp-hidden]");
            if (hidden) hidden.value = inputs.map(function (i) { return i.value; }).join("");
        }
    }

    /* ---------- 3. Dismissible inline notices ---------- */
    function bindNotices() {
        document.addEventListener("click", function (e) {
            var btn = e.target.closest ? e.target.closest("[data-fx-dismiss]") : null;
            if (btn) {
                var notice = btn.closest(".fx-notice");
                if (notice) notice.remove();
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    function init() {
        bindCartRemoveConfirm();
        bindOtpInputs();
        bindNotices();
    }
})();
