@extends('layouts.admin.app')

@section('title', translate('freelancer_specialization_setup'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <h2 class="h1 mb-0 d-flex gap-10">
                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/brand-setup.png') }}" alt="">
                {{ translate('freelancer_specialization_setup') }}
            </h2>
            <a href="{{ route('admin.freelancer.specializations.create') }}" class="btn btn-primary">
                <i class="fi fi-rr-plus"></i>
                {{ translate('add_services') }}
            </a>
        </div>

        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                            <h3 class="mb-0">
                                {{ translate('freelancer_specializations') }}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $specializations->total() }}</span>
                            </h3>
                            <form action="{{ url()->current() }}" method="GET" novalidate>
                                <div class="input-group flex-grow-1 max-w-280">
                                    <input type="search" name="searchValue" class="form-control"
                                           placeholder="{{ translate('search_by_specialization_or_category') }}"
                                           value="{{ request('searchValue') }}">
                                    <div class="input-group-append search-submit">
                                        <button type="submit">
                                            <i class="fi fi-rr-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle">
                                <thead class="text-capitalize">
                                    <tr>
                                        <th>{{ translate('SL') }}</th>
                                        <th>{{ translate('freelancer_category') }}</th>
                                        <th>{{ translate('freelancer_specialization') }}</th>
                                        <th>{{ translate('small_description') }}</th>
                                        <th class="text-center">{{ translate('priority') }}</th>
                                        <th class="text-center">{{ translate('status') }}</th>
                                        <th class="text-center">{{ translate('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($specializations as $key => $specialization)
                                        <tr>
                                            <td>{{ $specializations->firstItem() + $key }}</td>
                                            <td>{{ $specialization?->category?->defaultname ?? translate('not_available') }}</td>
                                            <td>{{ $specialization['defaultname'] }}</td>
                                            <td>{{ $specialization['description'] ?: translate('not_available') }}</td>
                                            <td class="text-center">{{ $specialization['priority'] }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.freelancer.specializations.status') }}" method="post"
                                                      id="freelancer-specialization-status{{ $specialization['id'] }}-form" class="no-reload-form" novalidate>
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $specialization['id'] }}">
                                                    <label class="switcher mx-auto" for="freelancer-specialization-status{{ $specialization['id'] }}">
                                                        <input
                                                            class="switcher_input custom-modal-plugin"
                                                            type="checkbox" value="1" name="is_active"
                                                            id="freelancer-specialization-status{{ $specialization['id'] }}"
                                                            {{ $specialization['is_active'] ? 'checked' : '' }}
                                                            data-modal-type="input-change-form"
                                                            data-modal-form="#freelancer-specialization-status{{ $specialization['id'] }}-form"
                                                            data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/category-status-on.png') }}"
                                                            data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/category-status-off.png') }}"
                                                            data-on-title="{{ translate('Want_to_Turn_ON').' '.$specialization['defaultname'].' '. translate('status') }}"
                                                            data-off-title="{{ translate('Want_to_Turn_OFF').' '.$specialization['defaultname'].' '.translate('status') }}"
                                                            data-on-message="<p>{{ translate('freelancer_specialization_enabled_message') }}</p>"
                                                            data-off-message="<p>{{ translate('freelancer_specialization_disabled_message') }}</p>"
                                                            data-on-button-text="{{ translate('turn_on') }}"
                                                            data-off-button-text="{{ translate('turn_off') }}">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <a class="btn btn-outline-info icon-btn" title="{{ translate('edit') }}"
                                                       href="{{ route('admin.freelancer.specializations.edit', [$specialization['id']]) }}">
                                                        <i class="fi fi-sr-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger icon-btn"
                                                            title="{{ translate('delete') }}" data-bs-toggle="modal"
                                                            data-bs-target="#deleteFreelancerSpecializationModal{{ $specialization['id'] }}">
                                                        <i class="fi fi-rr-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="modal fade" id="deleteFreelancerSpecializationModal{{ $specialization['id'] }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-body text-center p-30">
                                                                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/delete.png') }}" width="80" class="mb-20" alt="">
                                                                <h3 class="mb-3">{{ translate('want_to_delete_this_freelancer_specialization') }}?</h3>
                                                                <p class="mb-4">{{ translate('you_will_not_be_able_to_revert_this_once_it_is_deleted.') }}</p>
                                                                <form action="{{ route('admin.freelancer.specializations.delete') }}" method="post" novalidate>
                                                                    @csrf
                                                                    <input type="hidden" name="id" value="{{ $specialization['id'] }}">
                                                                    <div class="d-flex justify-content-center gap-3">
                                                                        <button type="button" class="btn btn-secondary min-w-120" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                                                                        <button type="submit" class="btn btn-danger min-w-120">{{ translate('Yes,_Delete') }}</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4">
                            <div class="d-flex justify-content-lg-end">
                                {{ $specializations->links() }}
                            </div>
                        </div>

                        @if(count($specializations) == 0)
                            @include('layouts.admin.partials._empty-state',['text'=>'no_freelancer_specialization_found'],['image'=>'default'])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
