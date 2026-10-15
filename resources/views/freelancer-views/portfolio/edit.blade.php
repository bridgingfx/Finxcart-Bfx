@extends('layouts.freelancer.app')

@section('title', translate('edit_portfolio_item'))

@push('css_or_js')
    @include('freelancer-views.portfolio.partials._styles')
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('edit_portfolio_item') }}</h2>
        </div>

        <form action="{{ route('freelancer.portfolio.update', [$item->id]) }}" method="post" enctype="multipart/form-data" novalidate>
            @csrf
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
                                    <div class="form-text">{{ translate('only_one_portfolio_item_can_be_active_at_a_time_setting_this_one_active_will_deactivate_the_others') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('freelancer-views.portfolio.partials._gallery')

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('freelancer.portfolio.index') }}" class="btn btn-secondary">{{ translate('cancel') }}</a>
                <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('script')
    @include('freelancer-views.portfolio.partials._script')
@endpush
