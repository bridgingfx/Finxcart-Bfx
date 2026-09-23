@extends('layouts.admin.app')

@section('title', translate('freelancer_specialization_setup'))

@section('content')
    @php
        $oldNames = old('name', []);
        $oldDescriptions = old('description', []);
        $specializationRowCount = 1;
        foreach ($languages as $languageIndex => $language) {
            if (isset($oldNames[$languageIndex]) && is_array($oldNames[$languageIndex])) {
                $specializationRowCount = max($specializationRowCount, count($oldNames[$languageIndex]));
            }
        }
    @endphp

    <div class="content container-fluid">
        <div class="mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.freelancer.specializations.index') }}" class="btn btn-primary btn-outline-primary btn-sm">
                    <i class="fi fi-sr-arrow-left"></i>
                </a>
                <h2 class="h1 mb-0 d-flex gap-10">
                    <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/brand-setup.png') }}" alt="">
                    {{ translate('freelancer_specialization_setup') }}
                </h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-start">
                        <form action="{{ route('admin.freelancer.specializations.store') }}" method="POST" novalidate>
                            @csrf
                            <input type="hidden" name="is_active" value="1">
                            <div class="row mb-4">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label" for="freelancer_category_id">
                                            {{ translate('freelancer_category') }}<span class="text-danger">*</span>
                                        </label>
                                        <div class="select-wrapper">
                                            <select class="form-select" name="freelancer_category_id" id="freelancer_category_id" required>
                                                <option disabled {{ old('freelancer_category_id') ? '' : 'selected' }}>{{ translate('select_freelancer_category') }}</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category['id'] }}" {{ (int)old('freelancer_category_id') === (int)$category['id'] ? 'selected' : '' }}>
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

                                    <div class="tab-content" id="pills-tabContent">
                                        @foreach($languages as $languageIndex => $lang)
                                            <div class="form-group tab-pane fade {{ $lang == $defaultLanguage ? 'show active' : '' }}" id="{{ $lang }}-form" aria-labelledby="{{ $lang }}-link" role="tabpanel">
                                                <div class="specialization-name-list"
                                                     data-specialization-name-list
                                                     data-lang-index="{{ $languageIndex }}"
                                                     data-lang-code="{{ strtoupper($lang) }}"
                                                     data-default-language="{{ $lang == $defaultLanguage ? '1' : '0' }}">
                                                    @for($rowIndex = 0; $rowIndex < $specializationRowCount; $rowIndex++)
                                                        <div class="specialization-name-row mb-3">
                                                            <div class="row g-2 align-items-end">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">
                                                                        {{ translate('freelancer_specialization') }}{{ $lang == $defaultLanguage ? '*' : '' }}
                                                                        ({{ strtoupper($lang) }})
                                                                    </label>
                                                                    <input type="text"
                                                                           name="name[{{ $languageIndex }}][]"
                                                                           class="form-control"
                                                                           maxlength="100"
                                                                           value="{{ $oldNames[$languageIndex][$rowIndex] ?? '' }}"
                                                                           placeholder="{{ translate('add_freelancer_specialization') }}"
                                                                           {{ $lang == $defaultLanguage ? 'required' : '' }}>
                                                                </div>
                                                                <div class="{{ $lang == $defaultLanguage ? 'col-md-5' : 'col-md-6' }}">
                                                                    <label class="form-label">
                                                                        {{ translate('small_description') }}
                                                                        ({{ strtoupper($lang) }})
                                                                    </label>
                                                                    <input type="text"
                                                                           name="description[{{ $languageIndex }}][]"
                                                                           class="form-control"
                                                                           maxlength="500"
                                                                           value="{{ $oldDescriptions[$languageIndex][$rowIndex] ?? '' }}"
                                                                           placeholder="{{ translate('small_description') }}">
                                                                </div>
                                                                @if($lang == $defaultLanguage)
                                                                    <div class="col-md-1">
                                                                        @if($rowIndex === 0)
                                                                            <button type="button" class="btn btn-outline-primary icon-btn add-specialization-row" title="{{ translate('add') }}">
                                                                                <i class="fi fi-rr-plus"></i>
                                                                            </button>
                                                                        @else
                                                                            <button type="button" class="btn btn-outline-danger icon-btn remove-specialization-row" title="{{ translate('delete') }}">
                                                                                <i class="fi fi-rr-trash"></i>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        @endforeach
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="priority">{{ translate('priority') }}
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                                  aria-label="{{ translate('the_lowest_number_will_get_the_highest_priority') }}"
                                                  data-bs-title="{{ translate('the_lowest_number_will_get_the_highest_priority') }}">
                                                <i class="fi fi-sr-info"></i>
                                            </span>
                                        </label>
                                        <div class="select-wrapper">
                                            <select class="form-select" name="priority" id="priority" required>
                                                <option disabled {{ old('priority') === null ? 'selected' : '' }}>{{ translate('set_Priority') }}</option>
                                                @for ($i = 0; $i <= 20; $i++)
                                                    <option value="{{ $i }}" {{ (string)old('priority') === (string)$i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <a href="{{ route('admin.freelancer.specializations.index') }}" class="btn btn-secondary">{{ translate('back') }}</a>
                                <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
                                <button type="submit" class="btn btn-primary">{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lists = document.querySelectorAll('[data-specialization-name-list]');
            const specializationLabel = @json(translate('freelancer_specialization'));
            const descriptionLabel = @json(translate('small_description'));
            const placeholder = @json(translate('add_freelancer_specialization'));
            const descriptionPlaceholder = @json(translate('small_description'));
            const deleteTitle = @json(translate('delete'));

            function rowCount() {
                return lists[0]?.querySelectorAll('.specialization-name-row').length || 1;
            }

            function buildRow(list) {
                const langIndex = list.dataset.langIndex;
                const langCode = list.dataset.langCode;
                const isDefault = list.dataset.defaultLanguage === '1';
                const wrapper = document.createElement('div');
                wrapper.className = 'specialization-name-row mb-3';
                wrapper.innerHTML = `
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">${specializationLabel}${isDefault ? '<span class="text-danger">*</span>' : ''} (${langCode})</label>
                            <input type="text" name="name[${langIndex}][]" class="form-control" maxlength="100" placeholder="${placeholder}" ${isDefault ? 'required' : ''}>
                        </div>
                        <div class="${isDefault ? 'col-md-5' : 'col-md-6'}">
                            <label class="form-label">${descriptionLabel} (${langCode})</label>
                            <input type="text" name="description[${langIndex}][]" class="form-control" maxlength="500" placeholder="${descriptionPlaceholder}">
                        </div>
                        ${isDefault ? `<div class="col-md-1"><button type="button" class="btn btn-outline-danger icon-btn remove-specialization-row" title="${deleteTitle}"><i class="fi fi-rr-trash"></i></button></div>` : ''}
                    </div>
                `;

                return wrapper;
            }

            function addRow() {
                lists.forEach(function (list) {
                    list.appendChild(buildRow(list));
                });
            }

            function removeRow(index) {
                if (rowCount() <= 1) {
                    return;
                }

                lists.forEach(function (list) {
                    list.querySelectorAll('.specialization-name-row')[index]?.remove();
                });
            }

            document.addEventListener('click', function (event) {
                const addButton = event.target.closest('.add-specialization-row');
                if (addButton) {
                    addRow();
                    return;
                }

                const removeButton = event.target.closest('.remove-specialization-row');
                if (removeButton) {
                    const row = removeButton.closest('.specialization-name-row');
                    const rows = Array.from(row.parentElement.querySelectorAll('.specialization-name-row'));
                    removeRow(rows.indexOf(row));
                }
            });
        });
    </script>
@endpush
