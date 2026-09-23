@extends('layouts.admin.app')


@section('content')
<div class="container">
    <h2>Influencer Contact Inquiries</h2>
    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Influencer</th>
                        <th>Customer</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inquiries as $inquiry)
                    <tr>
                        <td>{{ $inquiry->created_at->format('d M Y') }}</td>
                        <td>{{ $inquiry->influencer->name ?? 'Unknown' }}</td>
                        <td>
                            <strong>{{ $inquiry->customer_name }}</strong><br>
                            <small>{{ $inquiry->customer_email }}</small><br>
                            <small>{{ $inquiry->phone }}</small>
                        </td>
                        <td>{{ $inquiry->message }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            {{ $inquiries->links() }}
        </div>
    </div>
</div>
@endsection