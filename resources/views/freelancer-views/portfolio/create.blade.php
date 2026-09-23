@extends('layouts.freelancer.app')

@section('title', translate('add_portfolio_item'))

@push('css_or_js')
    @include('freelancer-views.portfolio.partials._styles')
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('add_portfolio_item') }}</h2>
            <p class="text-muted mb-0">{{ translate('showcase_a_project_you_are_proud_of') }}</p>
        </div>

        <form action="{{ route('freelancer.portfolio.store') }}" method="post" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="card portfolio-form-card mb-3">
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('image') }}</label>
                            <label class="portfolio-image-drop d-block" id="portfolioImageDrop">
                                <img id="portfolioImagePreview" alt="">
                                <div class="drop-placeholder" id="portfolioImagePlaceholder">
                                    <i class="tio-add-photo"></i>
                                    <div class="mt-2 text-muted small">{{ translate('click_to_upload_a_cover_image') }}</div>
                                </div>
                                <input type="file" name="image" id="portfolioImageInput" class="d-none" accept=".webp,.jpg,.jpeg,.png">
                            </label>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">{{ translate('title') }} *</label>
                                    <input class="form-control" name="title" maxlength="255" value="{{ old('title') }}" required
                                           placeholder="{{ translate('ex') }}: {{ translate('e_commerce_website_redesign') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ translate('completed_at') }}</label>
                                    <input class="form-control" type="date" name="completed_at" value="{{ old('completed_at') }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">{{ translate('description') }}</label>
                                    <textarea class="form-control" name="description" rows="4"
                                              placeholder="{{ translate('what_was_the_project_about_and_what_did_you_deliver') }}">{{ old('description') }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">{{ translate('skills') }} / {{ translate('tags') }} <span class="text-muted">(Add multiple Specialization)</span></label>
                                    <div class="tag-input-wrapper" id="tagInputWrapper">
                                        <input type="text" id="tagInput" placeholder="{{ translate('type_a_skill_and_press_enter') }}">
                                    </div>
                                    <input type="hidden" name="tags" id="tagsHidden" value="{{ old('tags') }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">{{ translate('project_url') }}</label>
                                    <input class="form-control" name="project_url" value="{{ old('project_url') }}" placeholder="https://">
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
