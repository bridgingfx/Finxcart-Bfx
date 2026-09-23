@extends('layouts.admin.app')

@section('title', translate('freelancer_audit_log'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0 d-flex gap-10">
                {{ translate('freelancer_audit_log') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $logs->total() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="{{ route('admin.freelancer.audit-logs.index') }}" method="get" class="d-flex flex-wrap gap-2" novalidate>
                    <select name="action" class="form-control" style="max-width: 220px;" onchange="this.form.submit()">
                        <option value="">{{ translate('all_actions') }}</option>
                        @foreach($actions as $actionOption)
                            <option value="{{ $actionOption }}" {{ request('action') === $actionOption ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $actionOption)) }}
                            </option>
                        @endforeach
                    </select>
                    <select name="actor_type" class="form-control" style="max-width: 160px;" onchange="this.form.submit()">
                        <option value="">{{ translate('all_actors') }}</option>
                        @foreach(['customer', 'seller', 'admin', 'system'] as $actorType)
                            <option value="{{ $actorType }}" {{ request('actor_type') === $actorType ? 'selected' : '' }}>
                                {{ ucfirst($actorType) }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" class="form-control" name="subject_type" value="{{ request('subject_type') }}"
                           placeholder="{{ translate('subject_type') }}" style="max-width: 200px;">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" style="max-width: 160px;">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" style="max-width: 160px;">
                    <button type="submit" class="btn btn-primary"><i class="fi fi-rr-search"></i></button>
                    <a href="{{ route('admin.freelancer.audit-logs.index') }}" class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                </form>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light thead-50 text-capitalize table-nowrap">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('actor') }}</th>
                        <th>{{ translate('action') }}</th>
                        <th>{{ translate('subject') }}</th>
                        <th>{{ translate('before') }}</th>
                        <th>{{ translate('after') }}</th>
                        <th>{{ translate('timestamp') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($logs as $key => $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $key }}</td>
                            <td>
                                <span class="badge badge-soft-secondary text-capitalize">{{ $log->actor_type }}</span>
                                @if($log->actor_id)
                                    <span class="text-muted">#{{ $log->actor_id }}</span>
                                @endif
                            </td>
                            <td class="text-capitalize">{{ str_replace('_', ' ', $log->action) }}</td>
                            <td>
                                <span class="text-muted">{{ class_basename($log->subject_type) }}</span> #{{ $log->subject_id }}
                            </td>
                            <td style="min-width: 220px; max-width: 280px;">
                                @if($log->before_state)
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($log->before_state as $stateKey => $stateValue)
                                            <div class="fs-11 text-break">
                                                <span class="text-muted">{{ str_replace('_', ' ', $stateKey) }}:</span>
                                                {{ is_array($stateValue) ? json_encode($stateValue) : $stateValue }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="min-width: 220px; max-width: 280px;">
                                @if($log->after_state)
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($log->after_state as $stateKey => $stateValue)
                                            <div class="fs-11 text-break">
                                                <span class="text-muted">{{ str_replace('_', ' ', $stateKey) }}:</span>
                                                {{ is_array($stateValue) ? json_encode($stateValue) : $stateValue }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $log->created_at?->format('d M, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">{{ translate('no_data_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
