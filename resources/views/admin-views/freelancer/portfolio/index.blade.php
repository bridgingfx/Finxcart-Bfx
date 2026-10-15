@extends('layouts.admin.app')

@section('title', translate('freelancer_portfolio'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0 d-flex gap-10">
                {{ translate('freelancer_portfolio') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $portfolioItems->total() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="{{ route('admin.freelancer.portfolio.index') }}" method="get" class="d-flex flex-wrap gap-2" novalidate>
                    <input type="text" class="form-control" name="searchValue" value="{{ request('searchValue') }}"
                           placeholder="{{ translate('search_by_portfolio_or_freelancer') }}" style="max-width: 280px;">
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
                        <th>{{ translate('portfolio') }}</th>
                        <th>{{ translate('freelancer') }}</th>
                        <th>{{ translate('completed_at') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($portfolioItems as $key => $item)
                        <tr>
                            <td>{{ $portfolioItems->firstItem() + $key }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img width="48" height="48" class="rounded object-fit-cover" src="{{ getStorageImages(path: $item->image_full_url, type: 'backend-profile') }}" alt="">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $item->title }}</div>
                                        <div class="text-muted fs-12 text-truncate" style="max-width: 320px;">{{ $item->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a class="text-dark text-hover-primary fw-semibold" href="{{ route('admin.freelancer.accounts.view', $item->seller_id) }}">
                                    {{ $item->seller?->shop?->name ?? trim(($item->seller?->f_name ?? '') . ' ' . ($item->seller?->l_name ?? '')) }}
                                </a>
                                <div class="text-muted fs-12">{{ $item->seller?->email }}</div>
                            </td>
                            <td>{{ $item->completed_at?->format('d M, Y') ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $item->is_active ? 'badge-success text-bg-success' : 'badge-secondary text-bg-secondary' }}">
                                    {{ $item->is_active ? translate('active') : translate('inactive') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.freelancer.portfolio.show', $item->id) }}"
                                       class="btn btn-sm btn-outline-info icon-btn" title="{{ translate('view') }}">
                                        <i class="fi fi-rr-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.freelancer.portfolio.edit', $item->id) }}"
                                       class="btn btn-sm btn-outline-primary icon-btn" title="{{ translate('edit') }}">
                                        <i class="fi fi-rr-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ translate('no_data_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $portfolioItems->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
