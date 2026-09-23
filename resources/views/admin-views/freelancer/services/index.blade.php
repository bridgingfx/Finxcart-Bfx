@extends('layouts.admin.app')

@section('title', translate('freelancer_services'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0 d-flex gap-10">
                {{ translate('freelancer_services') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $services->total() }}</span>
            </h2>
            <a href="{{ route('admin.freelancer.services.create') }}" class="btn btn-primary text-nowrap d-inline-flex align-items-center gap-2">
                <i class="fi fi-rr-plus"></i>
                <span>{{ translate('add_service') }}</span>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="{{ route('admin.freelancer.services.index') }}" method="get" class="d-flex flex-wrap gap-2" novalidate>
                    <input type="text" class="form-control" name="searchValue" value="{{ request('searchValue') }}"
                           placeholder="{{ translate('search_by_service_or_freelancer') }}" style="max-width: 280px;">
                    <select name="status" class="form-control" style="max-width: 180px;" onchange="this.form.submit()">
                        <option value="">{{ translate('all_status') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ translate('active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ translate('inactive') }}</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fi fi-rr-search"></i></button>
                </form>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light thead-50 text-capitalize table-nowrap">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('service') }}</th>
                        <th>{{ translate('freelancer') }}</th>
                        <th>{{ translate('category') }}</th>
                        <th>{{ translate('price') }}</th>
                        <th>{{ translate('delivery') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($services as $key => $service)
                        <tr>
                            <td>{{ $services->firstItem() + $key }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $service->title }}</div>
                                <div class="text-muted fs-12 text-truncate" style="max-width: 320px;">{{ $service->description }}</div>
                            </td>
                            <td>
                                <a class="text-dark text-hover-primary fw-semibold" href="{{ route('admin.freelancer.accounts.view', $service->seller_id) }}">
                                    {{ $service->seller?->shop?->name ?? trim(($service->seller?->f_name ?? '') . ' ' . ($service->seller?->l_name ?? '')) }}
                                </a>
                                <div class="text-muted fs-12">{{ $service->seller?->email }}</div>
                            </td>
                            <td>
                                <div>{{ $service->category?->name ?? '-' }}</div>
                                <div class="text-muted fs-12">{{ $service->specialization?->name ?? '-' }}</div>
                            </td>
                            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $service->price), currencyCode: getCurrencyCode()) }}</td>
                            <td>{{ $service->delivery_time_days }} {{ translate('days') }}</td>
                            <td>
                                <span class="badge {{ $service->is_active ? 'badge-success text-bg-success' : 'badge-secondary text-bg-secondary' }}">
                                    {{ $service->is_active ? translate('active') : translate('inactive') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.freelancer.services.show', $service->id) }}"
                                       class="btn btn-sm btn-outline-info icon-btn" title="{{ translate('view') }}">
                                        <i class="fi fi-rr-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.freelancer.services.edit', $service->id) }}"
                                       class="btn btn-sm btn-outline-primary icon-btn" title="{{ translate('edit') }}">
                                        <i class="fi fi-rr-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">{{ translate('no_data_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $services->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
