{{--
    contact_modal.blade.php — fx contact-inquiry modal for influencers.
    Vanilla JS (no Bootstrap/jQuery dependency). Opened by any element with
    [data-fx-contact-influencer][data-id][data-name]. Posts to influencers.contact.
--}}
<div class="fx-overlay" id="fx-contact-influencer-overlay" hidden>
    <div class="fx-modal" role="dialog" aria-modal="true" aria-labelledby="fx-contact-influencer-title">
        <button type="button" class="fx-modal__close" data-fx-contact-close aria-label="{{ translate('close') }}">&times;</button>
        <h2 id="fx-contact-influencer-title" style="font-size:20px; font-weight:700; margin:0 0 var(--fx-s2); padding-inline-end: var(--fx-s8);">
            {{ translate('contact') }} <span id="fx-contact-influencer-name">{{ translate('influencer') }}</span>
        </h2>
        <p class="fx-meta" style="margin:0 0 var(--fx-s4);">{{ translate('your_inquiry_is_sent_to_our_team') }}</p>
        <form action="{{ route('influencers.contact') }}" method="POST" class="fx-form" novalidate>
            @csrf
            <input type="hidden" name="influencer_id" id="fx-contact-influencer-id" value="">
            <div class="fx-field">
                <label for="fx-contact-name">{{ translate('your_name') }}</label>
                <input type="text" id="fx-contact-name" name="customer_name" required autocomplete="name">
            </div>
            <div class="fx-field">
                <label for="fx-contact-email">{{ translate('your_email') }}</label>
                <input type="email" id="fx-contact-email" name="customer_email" required autocomplete="email">
            </div>
            <div class="fx-field">
                <label for="fx-contact-phone">{{ translate('phone_optional') }}</label>
                <input type="tel" id="fx-contact-phone" name="phone" autocomplete="tel">
            </div>
            <div class="fx-field">
                <label for="fx-contact-message">{{ translate('message') }}</label>
                <textarea id="fx-contact-message" name="message" rows="3" required></textarea>
            </div>
            <div style="display:flex; gap: var(--fx-s2); justify-content:flex-end;">
                <button type="button" class="fx-btn fx-btn-ghost" data-fx-contact-close>{{ translate('cancel') }}</button>
                <button type="submit" class="fx-btn">{{ translate('send_message') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var overlay = document.getElementById('fx-contact-influencer-overlay');
    var nameEl = document.getElementById('fx-contact-influencer-name');
    var idEl = document.getElementById('fx-contact-influencer-id');
    if (!overlay) return;

    function openModal(id, name) {
        idEl.value = id || '';
        nameEl.textContent = name || '';
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
        var first = overlay.querySelector('input[name="customer_name"]');
        if (first) first.focus();
    }
    function closeModal() {
        overlay.hidden = true;
        document.body.style.overflow = '';
    }

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-fx-contact-influencer]');
        if (trigger) {
            openModal(trigger.getAttribute('data-id'), trigger.getAttribute('data-name'));
            return;
        }
        if (e.target.closest('[data-fx-contact-close]') || e.target === overlay) {
            closeModal();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !overlay.hidden) closeModal();
    });
})();
</script>
