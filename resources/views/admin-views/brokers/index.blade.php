@extends('layouts.admin.app')
@section('title', 'Manage Brokers')
@push('script')
    <script>
        // Check if there is a success session and trigger Toastr
        @if(session('success'))
            // Try using the theme's default toastr if available
            if (typeof toastr !== 'undefined') {
                toastr.success("{{ session('success') }}");
            } else {
                // Fallback to standard browser alert if toastr isn't loaded
                alert("{{ session('success') }}");
            }
        @endif
        
        // Check for errors (e.g. validation failed)
        @if($errors->any())
            @foreach($errors->all() as $error)
                if (typeof toastr !== 'undefined') {
                    toastr.error("{{ $error }}");
                }
            @endforeach
        @endif
    </script>
@endpush
@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h1 mb-0 text-capitalize"><i class="fi fi-rr-briefcase"></i> Brokers</h2>
        <a href="{{ route('admin.brokers.reviews') }}" class="btn btn-outline-primary">
            <i class="fi fi-rr-comment-text"></i> Manage Reviews
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Add New Broker</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.brokers.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Broker Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Logo</label>
                        <input type="file" name="logo" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-primary font-weight-bold">Referral / Affiliate URL</label>
                    <input type="url" name="affiliate_url" class="form-control" placeholder="https://broker.com/register?ref=MY_ID" required>
                </div>

                <div class="row border rounded p-2 mb-3 bg-light mx-1">
                    <div class="col-12"><h6 class="text-primary">System Authority Scores (0 - 10)</h6></div>
                    <div class="col-md-4 mb-3">
                        <label>License Score (Weight 50%)</label>
                        <input type="number" step="0.1" max="10" name="score_license" class="form-control" placeholder="8.5" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Software Score (Weight 30%)</label>
                        <input type="number" step="0.1" max="10" name="score_software" class="form-control" placeholder="7.0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Stability Score (Weight 20%)</label>
                        <input type="number" step="0.1" max="10" name="score_stability" class="form-control" placeholder="9.0" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3"><label>Regulations</label><input type="text" name="regulations" class="form-control"></div>
                    <div class="col-md-4 mb-3"><label>Min Deposit</label><input type="text" name="min_deposit" class="form-control"></div>
                    <div class="col-md-4 mb-3"><label>Platforms</label><input type="text" name="platforms" class="form-control"></div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Broker</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap card-table">
                <thead class="thead-light">
                    <tr>
                        <th>Broker</th>
                        <th>System Rating</th>
                        <th>User Rating</th>
                        <th>Affiliate Link</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brokers as $broker)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{asset($broker->logo) }}" class="rounded border mr-2" width="40">
                                {{ $broker->name }}
                            </div>
                        </td>
                        <td><span class="badge badge-soft--primary" style="font-size:12px">{{ $broker->system_rating }} / 10</span></td>
                        <td><span class="badge badge-soft-warning" style="font-size:12px">★ {{ $broker->user_rating }}</span></td>
                        <td><a href="{{ $broker->affiliate_url }}" target="_blank" class="btn btn-outline-info btn-sm">Test Link</a></td>
                        <td>
                            <form action="{{ route('admin.brokers.destroy', $broker->id) }}" method="POST" class="d-inline" novalidate>
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</button>
                            </form>
                            <a href="{{ route('admin.brokers.edit', $broker->id) }}" class="btn btn-outline-info btn-sm">
                                <i class="fi fi-sr-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection