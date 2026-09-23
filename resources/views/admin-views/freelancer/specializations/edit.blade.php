@extends('layouts.admin.app')

@section('title', translate('edit_freelancer_specialization'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.freelancer.specializations.index') }}" class="btn btn-primary btn-outline-primary btn-sm">
                    <i class="fi fi-sr-arrow-left"></i>
                </a>
                <h2 class="h1 mb-0 d-flex gap-10">
                    <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/brand-setup.png') }}" alt="">
                    {{ translate('edit_freelancer_specialization') }}
                </h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-start">
                        <form action="{{ route('admin.freelancer.specializations.update', [$specialization['id']]) }}" method="POST" novalidate>
                            @csrf
                            <div class="row mb-4">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label" for="freelancer_category_id">
                                            {{ translate('freelancer_category') }}<span class="text-danger">*</span>
                                        </label>
                                        <div class="select-wrapper">
                                            <select class="form-select" name="freelancer_category_id" id="freelancer_category_id" required>
                                                <option disabled>{{ translate('select_freelancer_category') }}</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category['id'] }}" {{ (int)$specialization['freelancer_category_id'] === (int)$category['id'] ? 'selected' : '' }}>
                                                        {{ $category['defaultname'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="table-responsive w-auto overflow-y-hidden mb-4">
                                        <div class="position-relative nav--tab-wrapper">
                                            <ul class="nav nav-pills nav--tab lang_tab" id="pills-tab" role="tablist">
                                                @foreach($languages as $lang)
                                                    <li class="nav-item px-0">
                                                        <a data-bs-toggle="pill" data-bs-target="#{{ $lang }}-form" role="tab" class="nav-link px-2 {{ $lang == $defaultLanguage ? 'active' : '' }}" id="{{ $lang }}-link">
                                                            {{ ucfirst(getLanguageName($lang)) . ' (' . strtoupper($lang) . ')' }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="nav--tab__prev">
                                                <button type="button" class="btn btn-circle border-0 bg-white text-primary">
                                                    <i class="fi fi-sr-angle-left"></i>
                                                </button>
                                            </div>
                                            <div class="nav--tab__next">
                                                <button type="button" class="btn btn-circle border-0 bg-white text-primary">
                                                    <i class="fi fi-sr-angle-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $translatedNames = [];
                                        $translatedDescriptions = [];
                                        foreach ($specialization['translations'] as $translation) {
                                            if ($translation->key === 'name') {
                                                $translatedNames[$translation->locale] = $translation->value;
                                            }
                                            if ($translation->key === 'description') {
                                                $translatedDescriptions[$translation->locale] = $translation->value;
                                            }
                                        }
                                    @endphp

                                    <div class="tab-content" id="pills-tabContent">
                                        @foreach($languages as $lang)
                                            <div class="form-group tab-pane fade {{ $lang == $defaultLanguage ? 'show active' : '' }}" id="{{ $lang }}-form" aria-labelledby="{{ $lang }}-link" role="tabpanel">
                                                <div class="form-group">
                                                    <label class="form-label">{{ translate('freelancer_specialization') }}<span class="text-danger">*</span> ({{ strtoupper($lang) }})</label>
                                                    <input type="text" name="name[]" class="form-control" maxlength="100"
                                                           value="{{ $lang == $defaultLanguage ? $specialization['name'] : ($translatedNames[$lang] ?? '') }}"
                                                           placeholder="{{ translate('add_freelancer_specialization') }}" {{ $lang == $defaultLanguage ? 'required' : '' }}>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">{{ translate('small_description') }} ({{ strtoupper($lang) }})</label>
                                                    <input type="text" name="description[]" class="form-control" maxlength="500"
                                                           value="{{ $lang == $defaultLanguage ? $specialization['description'] : ($translatedDescriptions[$lang] ?? '') }}"
                                                           placeholder="{{ translate('small_description') }}">
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        @endforeach
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="priority">{{ translate('priority') }}</label>
                                        <div class="select-wrapper">
                                            <select class="form-select" name="priority" id="priority" required>
                                                @for ($i = 0; $i <= 20; $i++)
                                                    <option value="{{ $i }}" {{ (int)$specialization['priority'] === $i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="is_active">{{ translate('status') }}</label>
                                        <div class="select-wrapper">
                                            <select class="form-select" name="is_active" id="is_active">
                                                <option value="1" {{ $specialization['is_active'] ? 'selected' : '' }}>{{ translate('active') }}</option>
                                                <option value="0" {{ !$specialization['is_active'] ? 'selected' : '' }}>{{ translate('inactive') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <a href="{{ route('admin.freelancer.specializations.index') }}" class="btn btn-secondary">{{ translate('back') }}</a>
                                <button type="submit" class="btn btn-primary">{{ translate('update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
