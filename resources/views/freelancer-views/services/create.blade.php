@extends('layouts.freelancer.app')

@section('title', translate('add_service'))

@push('css_or_js')
    @include('freelancer-views.services.partials._styles')
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('add_service') }}</h2>
            <p class="text-muted mb-0">{{ translate('create_one_service_at_a_time_with_its_own_images_and_pricing') }}</p>
        </div>

        <form id="freelancer-service-form" action="{{ route('freelancer.services.store') }}" method="post" enctype="multipart/form-data" novalidate>
            @csrf
            @error('portfolio')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="card fsp-section-card mb-3">
                <div class="fsp-section-header">
                    <i class="tio-briefcase"></i>
                    <div>
                        <h5>{{ translate('basic_information') }}</h5>
                        <p>{{ translate('tell_buyers_what_this_service_is_about') }}</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                {{ translate('freelancer_category') }} *
                                <button type="button" class="btn btn-outline-primary btn-sm fsp-add-new-btn" data-toggle="modal" data-target="#addFreelancerCategoryModal">
                                    <i class="tio-add"></i> {{ translate('new_category') }}
                                </button>
                            </label>
                            <div class="input-group">
                                <select class="form-control" id="freelancer-category-select" name="freelancer_category_id" required>
                                    <option value="">{{ translate('select_freelancer_category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" data-owner="{{ (int)$category->seller_id === (int)auth('freelancer')->id() ? '1' : '0' }}" {{ (int)old('freelancer_category_id') === (int)$category->id ? 'selected' : '' }}>
                                            {{ $category->defaultname }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger d-none" id="remove-freelancer-category-btn" title="{{ translate('remove_this_category') }}">
                                        <i class="tio-delete"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                {{ translate('freelancer_specialization') }} *
                                <button type="button" class="btn btn-outline-primary btn-sm fsp-add-new-btn" data-toggle="modal" data-target="#addFreelancerSpecializationModal">
                                    <i class="tio-add"></i> {{ translate('new_specialization') }}
                                </button>
                            </label>
                            <div class="input-group">
                                <select class="form-control" id="freelancer-specialization-select" name="freelancer_specialization_id" required>
                                    <option value="">{{ translate('select_freelancer_specialization') }}</option>
                                    @foreach($specializations as $specialization)
                                        <option value="{{ $specialization->id }}" data-category="{{ $specialization->freelancer_category_id }}" data-owner="{{ (int)$specialization->seller_id === (int)auth('freelancer')->id() ? '1' : '0' }}" {{ (int)old('freelancer_specialization_id') === (int)$specialization->id ? 'selected' : '' }}>
                                            {{ $specialization->defaultname }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger d-none" id="remove-freelancer-specialization-btn" title="{{ translate('remove_this_specialization') }}">
                                        <i class="tio-delete"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ translate('title') }} *</label>
                            <input class="form-control" name="title" maxlength="150" value="{{ old('title') }}" required
                                   placeholder="{{ translate('Ex: I_will_build_a_modern_responsive_website') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ translate('service') }}</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            @include('freelancer-views.services.partials._images')

            @include('freelancer-views.services.partials._pricing-tiers')

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('freelancer.services.index') }}" class="btn btn-secondary">{{ translate('cancel') }}</a>
                <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
            </div>
        </form>
    </div>

    @include('freelancer-views.services._category-specialization-modals')
@endsection

@push('script')
    @include('freelancer-views.services.partials._script')
@endpush
