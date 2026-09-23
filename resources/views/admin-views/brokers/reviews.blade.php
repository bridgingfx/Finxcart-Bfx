@extends('layouts.admin.app')
@section('title', 'Broker Reviews')
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
    <div class="mb-3"><h2 class="h1 mb-0">Broker Reviews</h2></div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap card-table">
                <thead class="thead-light">
                    <tr>
                        <th>Status</th>
                        <th>Broker</th>
                        <th>Reviewer</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviews as $review)
                    <tr>
                        <td>
                            @if($review->is_approved)
                                <span class="badge badge-success">Live</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>{{ $review->broker->name }}</td>
                        <td>{{ $review->reviewer_name }}</td>
                        <td>★ {{ $review->rating }}</td>
                        <td class="text-wrap" style="max-width: 300px;">{{ $review->comment }}</td>
                        <td>
                            @if(!$review->is_approved)
                                <a href="{{ route('admin.brokers.reviews.approve', $review->id) }}" class="btn btn-success btn-sm">Approve</a>
                            @endif
                            <form action="{{ route('admin.brokers.reviews.delete', $review->id) }}" method="POST" class="d-inline" novalidate>
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $reviews->links() }}</div>
    </div>
</div>
@endsection