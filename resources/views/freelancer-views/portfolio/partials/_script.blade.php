@php
    $item = $item ?? null;
@endphp
<script>
    (function () {
        const dropInput = document.getElementById('portfolioImageInput');
        const preview = document.getElementById('portfolioImagePreview');
        const placeholder = document.getElementById('portfolioImagePlaceholder');
        dropInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                if (placeholder) {
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        });

        const tagWrapper = document.getElementById('tagInputWrapper');
        const tagInput = document.getElementById('tagInput');
        const tagsHidden = document.getElementById('tagsHidden');
        let tags = (tagsHidden.value || '').split(',').map(t => t.trim()).filter(Boolean);

        function renderTags() {
            tagWrapper.querySelectorAll('.tag-chip').forEach(chip => chip.remove());
            tags.forEach((tag, index) => {
                const chip = document.createElement('span');
                chip.className = 'tag-chip';
                chip.innerHTML = tag.replace(/</g, '&lt;') + ' <span class="remove-tag" data-index="' + index + '">&times;</span>';
                tagWrapper.insertBefore(chip, tagInput);
            });
            tagsHidden.value = tags.join(',');
        }

        tagInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                const value = tagInput.value.trim().replace(/,$/, '');
                if (value && !tags.includes(value)) {
                    tags.push(value);
                    renderTags();
                }
                tagInput.value = '';
            } else if (event.key === 'Backspace' && !tagInput.value && tags.length) {
                tags.pop();
                renderTags();
            }
        });

        tagWrapper.addEventListener('click', function (event) {
            const removeBtn = event.target.closest('.remove-tag');
            if (!removeBtn) { tagInput.focus(); return; }
            tags.splice(Number(removeBtn.dataset.index), 1);
            renderTags();
        });

        renderTags();

        const galleryWrapper = document.getElementById('gallery-wrapper');
        const galleryTemplate = document.getElementById('gallery-row-template');
        let galleryIndex = {{ $item ? $item->galleryItems->count() : 0 }};

        function bindGalleryRow(row) {
            const input = row.querySelector('.gallery-image-input');
            const preview = row.querySelector('.gallery-preview');
            const icon = row.querySelector('.gallery-placeholder-icon');
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

        function addGalleryRow() {
            const html = galleryTemplate.innerHTML.replaceAll('__INDEX__', galleryIndex);
            const holder = document.createElement('div');
            holder.innerHTML = html.trim();
            const row = holder.firstElementChild;
            galleryWrapper.appendChild(row);
            bindGalleryRow(row);
            galleryIndex++;
        }

        document.getElementById('add-gallery-row').addEventListener('click', addGalleryRow);

        galleryWrapper.addEventListener('click', function (event) {
            const removeBtn = event.target.closest('.remove-gallery-row');
            if (!removeBtn) return;
            removeBtn.closest('.gallery-row').remove();
        });
    })();
</script>
