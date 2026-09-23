@extends('layouts.admin.app')

@section('title', translate('edit_portfolio_item'))

@push('css_or_js')
    <style>
        .portfolio-form-card { border-radius: 14px; overflow: hidden; }
        .portfolio-image-drop {
            border: 2px dashed #d7dde5; border-radius: 12px; padding: 24px; text-align: center;
            cursor: pointer; transition: border-color .15s ease; background: #fafbfc;
        }
        .portfolio-image-drop:hover { border-color: #1684cf; }
        .portfolio-image-drop img { max-width: 100%; max-height: 220px; border-radius: 8px; }
        .tag-input-wrapper {
            display: flex; flex-wrap: wrap; gap: 6px; align-items: center;
            border: 1px solid #d7dde5; border-radius: 8px; padding: 6px 8px; min-height: 44px;
        }
        .tag-input-wrapper input { border: none; outline: none; flex: 1; min-width: 120px; padding: 4px; }
        .tag-chip {
            display: inline-flex; align-items: center; gap: 6px; background: #eef3f8; color: #1c2b3a;
            border-radius: 999px; padding: 4px 10px; font-size: 13px; font-weight: 500;
        }
        .tag-chip .remove-tag { cursor: pointer; color: #6c757d; font-weight: 700; }
        .tag-chip .remove-tag:hover { color: #dc3545; }
        .gallery-row { border: 1px solid #e7ebf0; border-radius: 10px; padding: 12px; margin-bottom: 10px; }
        .gallery-row .gallery-thumb-drop {
            border: 2px dashed #d7dde5; border-radius: 8px; height: 90px; width: 100%;
            display: flex; align-items: center; justify-content: center; cursor: pointer;
            background: #fafbfc; overflow: hidden;
        }
        .gallery-row .gallery-thumb-drop img { max-width: 100%; max-height: 100%; object-fit: cover; display: none; }
        .gallery-row .gallery-thumb-drop i { font-size: 22px; color: #9aa5b1; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('edit_portfolio_item') }}</h2>
        </div>

        <form action="{{ route('admin.freelancer.portfolio.update', [$item->id]) }}" method="post" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <div class="card portfolio-form-card mb-3">
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('image') }}</label>
                            <label class="portfolio-image-drop d-block" id="portfolioImageDrop">
                                <img id="portfolioImagePreview" src="{{ getStorageImages(path: $item->image_full_url, type: 'backend-profile') }}" alt="">
                                <input type="file" name="image" id="portfolioImageInput" class="d-none" accept=".webp,.jpg,.jpeg,.png">
                            </label>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">{{ translate('title') }} *</label>
                                    <input class="form-control" name="title" maxlength="255" value="{{ old('title', $item->title) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ translate('completed_at') }}</label>
                                    <input class="form-control" type="date" name="completed_at" value="{{ old('completed_at', $item->completed_at?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">{{ translate('description') }}</label>
                                    <textarea class="form-control" name="description" rows="4">{{ old('description', $item->description) }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">{{ translate('skills') }} / {{ translate('tags') }} <span class="text-muted">(Add multiple Specialization)</span></label>
                                    <div class="tag-input-wrapper" id="tagInputWrapper">
                                        <input type="text" id="tagInput" placeholder="{{ translate('type_a_skill_and_press_enter') }}">
                                    </div>
                                    <input type="hidden" name="tags" id="tagsHidden" value="{{ old('tags', implode(',', $item->tags ?? [])) }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">{{ translate('project_url') }}</label>
                                    <input class="form-control" name="project_url" value="{{ old('project_url', $item->project_url) }}" placeholder="https://">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ translate('status') }}</label>
                                    <select class="form-control" name="is_active">
                                        <option value="1" {{ old('is_active', (int)$item->is_active) == 1 ? 'selected' : '' }}>{{ translate('active') }}</option>
                                        <option value="0" {{ old('is_active', (int)$item->is_active) == 0 ? 'selected' : '' }}>{{ translate('inactive') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card portfolio-form-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">{{ translate('additional_project_images') }}</h4>
                        <p class="text-muted small mb-0">{{ translate('add_more_screenshots_each_with_its_own_optional_link') }}</p>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-gallery-row">
                        <i class="fi fi-rr-plus"></i> {{ translate('add_image') }}
                    </button>
                </div>
                <div class="card-body" id="gallery-wrapper">
                    @foreach($item->galleryItems as $galleryItem)
                        <div class="row g-2 align-items-center gallery-row">
                            <div class="col-md-3">
                                <label class="gallery-thumb-drop">
                                    <img alt="" class="gallery-preview" src="{{ getStorageImages(path: $galleryItem->image_full_url, type: 'backend-profile') }}" style="display:block">
                                    <i class="fi fi-rr-picture gallery-placeholder-icon" style="display:none"></i>
                                    <input type="file" class="d-none gallery-image-input" name="gallery[{{ $loop->index }}][image]" accept=".webp,.jpg,.jpeg,.png">
                                </label>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label mb-1">{{ translate('project_url') }}</label>
                                <input type="url" class="form-control" name="gallery[{{ $loop->index }}][url]" value="{{ $galleryItem->url }}" placeholder="https://">
                                <input type="hidden" name="gallery[{{ $loop->index }}][id]" value="{{ $galleryItem->id }}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-gallery-row"><i class="fi fi-rr-trash"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.freelancer.portfolio.index') }}" class="btn btn-secondary">{{ translate('cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ translate('save') }}</button>
            </div>
        </form>
    </div>

    <template id="gallery-row-template">
        <div class="row g-2 align-items-center gallery-row">
            <div class="col-md-3">
                <label class="gallery-thumb-drop">
                    <img alt="" class="gallery-preview">
                    <i class="fi fi-rr-picture gallery-placeholder-icon"></i>
                    <input type="file" class="d-none gallery-image-input" name="gallery[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png">
                </label>
            </div>
            <div class="col-md-8">
                <label class="form-label mb-1">{{ translate('project_url') }}</label>
                <input type="url" class="form-control" name="gallery[__INDEX__][url]" placeholder="https://">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm remove-gallery-row"><i class="fi fi-rr-trash"></i></button>
            </div>
        </div>
    </template>
@endsection

@push('script')
    <script>
        (function () {
            const dropInput = document.getElementById('portfolioImageInput');
            const preview = document.getElementById('portfolioImagePreview');
            dropInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (event) { preview.src = event.target.result; };
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
            let galleryIndex = {{ $item->galleryItems->count() }};

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
@endpush
