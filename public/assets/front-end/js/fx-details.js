/* fx-details.js — gallery, sticky purchase bar, package selection, share.
   Vanilla JS; depends on jQuery + existing custom.js cart/variant hooks only for actions. */
"use strict";

(function () {
    document.addEventListener("DOMContentLoaded", function () {
        initGallery();
        initStickyBar();
        initPackageSelect();
        initShare();
    });

    /* ---------- Gallery ---------- */
    function initGallery() {
        var gallery = document.querySelector("[data-fx-gallery]");
        if (!gallery) return;
        var images = Array.prototype.slice.call(gallery.querySelectorAll("[data-fx-gallery-img]"));
        if (images.length <= 1) return;
        var thumbs = Array.prototype.slice.call(gallery.querySelectorAll("[data-fx-gallery-thumb]"));
        var count = gallery.querySelector("[data-fx-gallery-count]");
        var index = 0;

        function show(i) {
            index = (i + images.length) % images.length;
            images.forEach(function (img, n) { img.hidden = n !== index; });
            thumbs.forEach(function (t, n) {
                t.setAttribute("aria-current", n === index ? "true" : "false");
            });
            if (count) count.textContent = (index + 1) + " / " + images.length;
        }

        gallery.querySelector("[data-fx-gallery-prev]").addEventListener("click", function () { show(index - 1); });
        gallery.querySelector("[data-fx-gallery-next]").addEventListener("click", function () { show(index + 1); });
        thumbs.forEach(function (t, n) {
            t.addEventListener("click", function () { show(n); });
        });

        // Keyboard
        gallery.addEventListener("keydown", function (e) {
            if (e.key === "ArrowLeft") show(index - 1);
            if (e.key === "ArrowRight") show(index + 1);
        });

        // Touch swipe
        var main = gallery.querySelector("[data-fx-gallery-main]");
        var startX = null;
        main.addEventListener("touchstart", function (e) {
            startX = e.changedTouches[0].clientX;
        }, { passive: true });
        main.addEventListener("touchend", function (e) {
            if (startX === null) return;
            var dx = e.changedTouches[0].clientX - startX;
            startX = null;
            if (Math.abs(dx) > 48) show(index + (dx < 0 ? 1 : -1));
        }, { passive: true });

        // Tap to enlarge
        var overlay = document.querySelector("[data-fx-gallery-overlay]");
        var enlargeImg = overlay && overlay.querySelector("[data-fx-gallery-enlarge-img]");
        var lastFocus = null;
        function openEnlarge() {
            if (!overlay || !enlargeImg) return;
            lastFocus = document.activeElement;
            enlargeImg.src = images[index].src;
            overlay.hidden = false;
            document.body.style.overflow = "hidden";
            overlay.querySelector("[data-fx-gallery-enlarge-close]").focus();
        }
        function closeEnlarge() {
            if (!overlay) return;
            overlay.hidden = true;
            document.body.style.overflow = "";
            if (lastFocus) lastFocus.focus();
        }
        images.forEach(function (img) { img.addEventListener("click", openEnlarge); });
        if (overlay) {
            overlay.addEventListener("click", function (e) {
                if (e.target === overlay || e.target.hasAttribute("data-fx-gallery-enlarge-close")) closeEnlarge();
            });
            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape" && !overlay.hidden) closeEnlarge();
            });
        }
    }

    /* ---------- Sticky purchase bar ---------- */
    function initStickyBar() {
        var bar = document.querySelector("[data-fx-stickybar]");
        var actions = document.querySelector("[data-fx-buy-actions]");
        if (!bar || !actions) return;

        var actionsVisible = true;
        var keyboardOpen = false;

        if ("IntersectionObserver" in window) {
            new IntersectionObserver(function (entries) {
                actionsVisible = entries[0].isIntersecting;
                update();
            }, { threshold: 0.2 }).observe(actions);
        } else {
            actionsVisible = false;
        }

        if (window.visualViewport) {
            var baseHeight = window.visualViewport.height;
            window.visualViewport.addEventListener("resize", function () {
                keyboardOpen = window.visualViewport.height < baseHeight * 0.75;
                update();
            });
        }

        function update() {
            var show = !actionsVisible && !keyboardOpen;
            bar.hidden = !show;
            bar.classList.toggle("is-visible", show);
            bar.setAttribute("aria-hidden", show ? "false" : "true");
        }
    }

    /* ---------- Package selection ---------- */
    function initPackageSelect() {
        document.querySelectorAll("[data-fx-select-package]").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var key = btn.getAttribute("data-fx-select-package");
                var form = document.querySelector(".add-to-cart-details-form");
                if (!form) return;
                var radio = form.querySelector('input[name="variant_key"][value="' + key.replace(/"/g, "") + '"]');
                if (radio) {
                    radio.checked = true;
                    if (window.jQuery) jQuery(radio).trigger("change");
                    var panel = document.querySelector("[data-fx-price-panel]");
                    if (panel) panel.scrollIntoView({ behavior: "smooth", block: "center" });
                    if (window.toastr) toastr.success(btn.dataset.selectedLabel || key);
                }
            });
        });
    }

    /* ---------- Share / copy link ---------- */
    function initShare() {
        var shareBtn = document.querySelector(".product-native-share");
        var copyBtn = document.querySelector(".product-copy-link");
        if (shareBtn) {
            shareBtn.addEventListener("click", async function () {
                var data = { title: this.dataset.title, text: this.dataset.title, url: this.dataset.url };
                if (navigator.share) {
                    try { await navigator.share(data); return; } catch (e) { if (e.name === "AbortError") return; }
                }
                if (copyBtn) copyBtn.click();
            });
        }
        if (copyBtn) {
            copyBtn.addEventListener("click", async function () {
                var label = document.querySelector(".product-share-copy-label");
                try {
                    await navigator.clipboard.writeText(this.dataset.url);
                } catch (e) {
                    var tmp = document.createElement("input");
                    tmp.value = this.dataset.url;
                    document.body.appendChild(tmp);
                    tmp.select();
                    document.execCommand("copy");
                    tmp.remove();
                }
                if (label) {
                    label.style.display = "block";
                    setTimeout(function () { label.style.display = "none"; }, 2200);
                }
                if (window.toastr) toastr.success(this.dataset.copied || "Link copied");
            });
        }
    }
})();
