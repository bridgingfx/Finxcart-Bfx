@php
    $service = $service ?? null;
@endphp
<script>
    (function () {
        const category = document.getElementById('freelancer-category-select');
        const specialization = document.getElementById('freelancer-specialization-select');
        function filterSpecializations() {
            const selectedCategory = category.value;
            Array.from(specialization.options).forEach(option => {
                if (!option.value) return;
                option.hidden = option.dataset.category !== selectedCategory;
                if (option.hidden && option.selected) specialization.value = '';
            });
        }
        category?.addEventListener('change', filterSpecializations);
        filterSpecializations();

        document.querySelectorAll('[data-tier-card]').forEach(function (card) {
            const toggle = card.querySelector('.fsp-tier-toggle');
            const body = card.querySelector('[data-tier-body]');
            toggle?.addEventListener('change', function () {
                card.classList.toggle('is-enabled', toggle.checked);
                body?.classList.toggle('is-disabled', !toggle.checked);
            });
        });

        const imageWrapper = document.getElementById('service-image-wrapper');
        const imageTemplate = document.getElementById('service-image-row-template');
        let imageIndex = {{ $service ? $service->images->count() : 0 }};

        function bindImageRow(row) {
            const input = row.querySelector('.service-image-input');
            const preview = row.querySelector('.service-image-preview');
            const icon = row.querySelector('.service-image-placeholder-icon');
            input.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                    icon.style.display = 'none';
                };
                reader.readAsDataURL(file);
            });
        }

        function addImageRow() {
            const html = imageTemplate.innerHTML.replaceAll('__INDEX__', imageIndex);
            const holder = document.createElement('div');
            holder.innerHTML = html.trim();
            const row = holder.firstElementChild;
            imageWrapper.appendChild(row);
            bindImageRow(row);
            imageIndex++;
        }

        document.getElementById('add-service-image-row').addEventListener('click', addImageRow);

        imageWrapper.addEventListener('click', function (event) {
            const removeBtn = event.target.closest('.remove-service-image-row');
            if (!removeBtn) return;
            removeBtn.closest('.service-image-row').remove();
        });

        @if(!$service)
            addImageRow();
        @endif

        const serviceForm = document.getElementById('freelancer-service-form');
        const imageError = document.querySelector('.service-image-validation-message');

        function hasServiceImage() {
            return Array.from(imageWrapper.querySelectorAll('.service-image-row')).some(function (row) {
                const idInput = row.querySelector('input[type="hidden"][name*="[id]"]');
                const fileInput = row.querySelector('.service-image-input');
                return Boolean(idInput?.value) || Boolean(fileInput?.files?.length);
            });
        }

        serviceForm?.addEventListener('submit', function (event) {
            if (hasServiceImage()) {
                imageError?.classList.add('d-none');
                return;
            }

            event.preventDefault();
            imageError?.classList.remove('d-none');
            imageWrapper.closest('.card')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
        const featureTemplate = document.getElementById('feature-row-template');
        const featureIndexes = {};
        document.querySelectorAll('[data-feature-list]').forEach(function (list) {
            featureIndexes[list.dataset.featureList] = list.querySelectorAll('.feature-row').length;
        });

        document.querySelectorAll('.add-feature-row').forEach(function (button) {
            button.addEventListener('click', function () {
                const tier = button.dataset.tier;
                const list = document.querySelector('[data-feature-list="' + tier + '"]');
                const index = featureIndexes[tier] || 0;
                const html = featureTemplate.innerHTML.replaceAll('__TIER__', tier).replaceAll('__INDEX__', index);
                const holder = document.createElement('div');
                holder.innerHTML = html.trim();
                list.appendChild(holder.firstElementChild);
                featureIndexes[tier] = index + 1;
            });
        });

        document.querySelectorAll('[data-feature-list]').forEach(function (list) {
            list.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('.remove-feature-row');
                if (!removeBtn) return;
                removeBtn.closest('.feature-row').remove();
            });
        });
    })();
</script>
