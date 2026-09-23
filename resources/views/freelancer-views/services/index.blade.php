@extends('layouts.freelancer.app')

@section('title', translate('manage_services'))

@push('css_or_js')
    <style>
        .fl-service-thumb {
            width: 52px; height: 40px; border-radius: 8px; object-fit: cover; background: #eef2f7;
        }
        .fl-service-thumb-placeholder {
            width: 52px; height: 40px; border-radius: 8px; background: #f6f8fb;
            display: flex; align-items: center; justify-content: center; color: #c3ccd6; font-size: 16px;
        }
        .fl-service-title { font-weight: 700; color: #1f2937; font-size: 13px; }
        .fl-service-meta { font-size: 12px; color: #9ca3af; }
        .fl-service-price { font-weight: 800; color: #17395e; }
        table.card-table tbody tr { transition: background .15s ease; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('manage_services') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $services->count() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="px-3 py-4 d-flex justify-content-end">
                <a href="{{ route('freelancer.services.create') }}" id="add-service-btn"
                   data-approved="{{ auth('freelancer')->user()?->status === 'approved' ? '1' : '0' }}"
                   class="btn btn--primary text-nowrap">
                    <i class="tio-add"></i>
                    {{ translate('add_service') }}
                </a>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light thead-50 text-capitalize table-nowrap">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('service') }}</th>
                        <th>{{ translate('category') }}</th>
                        <th>{{ translate('price') }}</th>
                        <th>{{ translate('delivery_time_days') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($services as $key => $service)
                        @php($cover = $service->images->first() ?? null)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-10">
                                    @if($cover)
                                        <img class="fl-service-thumb" alt="" src="{{ getStorageImages(path: $cover->image_full_url, type: 'backend-profile') }}">
                                    @else
                                        <span class="fl-service-thumb-placeholder"><i class="tio-photo-gallery-outlined"></i></span>
                                    @endif
                                    <span class="fl-service-title">{{ $service->title }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fl-service-title fw-normal">{{ $service->category?->defaultname }}</div>
                                <div class="fl-service-meta">{{ $service->specialization?->defaultname }}</div>
                            </td>
                            <td class="fl-service-price">{{ $service->price !== null ? webCurrencyConverter($service->price) : '-' }}</td>
                            <td>{{ $service->delivery_time_days ? $service->delivery_time_days . ' ' . translate('days') : '-' }}</td>
                            <td>
                                <form action="{{ route('freelancer.services.status-update', [$service->id]) }}" method="post" novalidate>
                                    @csrf
                                    <label class="switcher mx-auto">
                                        <input type="checkbox" class="switcher_input" onchange="this.form.submit()"
                                               {{ $service->is_active ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="btn btn-outline-info btn-sm square-btn"
                                       href="{{ route('freelancer.services.view', [$service->id]) }}"
                                       title="{{ translate('view') }}">
                                        <i class="tio-eye"></i>
                                    </a>
                                    <a class="btn btn-outline--primary btn-sm square-btn"
                                       href="{{ route('freelancer.services.edit', [$service->id]) }}"
                                       title="{{ translate('edit') }}">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <a class="btn btn-outline-danger btn-sm square-btn delete-data"
                                       data-id="freelancer-service-{{ $service->id }}"
                                       title="{{ translate('delete') }}"
                                       href="javascript:">
                                        <i class="tio-delete"></i>
                                    </a>
                                    <form action="{{ route('freelancer.services.destroy', [$service->id]) }}"
                                          method="post" id="freelancer-service-{{ $service->id }}" novalidate>
                                        @csrf @method('delete')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($services->count() == 0)
                @include('layouts.vendor.partials._empty-state', ['text' => 'no_service_found', 'image' => 'default'])
            @endif
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.getElementById('add-service-btn')?.addEventListener('click', function (e) {
            if (this.dataset.approved !== '1') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: '{{ translate('Not_Approved_Yet') }}',
                    text: '{{ translate('your_documents_must_be_approved_by_admin_before_adding_services') }}',
                    confirmButtonText: '{{ translate('OK') }}',
                });
            }
        });

        @if(session('freelancer_kyc_popup_message'))
            Swal.fire({
                icon: 'warning',
                title: '{{ translate('Not_Approved_Yet') }}',
                text: '{{ session('freelancer_kyc_popup_message') }}',
                confirmButtonText: '{{ translate('OK') }}',
            });
        @endif
    </script>
@endpush
