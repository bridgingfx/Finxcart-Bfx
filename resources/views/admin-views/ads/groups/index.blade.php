@extends('layouts.admin.app')
@section('title', 'Ad Groups')

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Ad Groups</h1>
        <a href="{{ route('admin.ad-groups.create') }}" class="btn btn-primary">Create New Group</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Shortcode <i class="bi bi-question-circle" title="Click to copy"></i></th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($adGroups as $group)
                        <tr>
                            <td>{{ $group->name }}</td>
                            <td>{{ $group->slug }}</td>
                            <td><span class="shortcode" title="Click to copy">{{ $group->shortcode }}</span></td>
                            <td>
                                @if ($group->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.ad-groups.edit', $group) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.ad-groups.destroy', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')" novalidate>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No ad groups found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $adGroups->links() }}
        </div>
    </div>
</div>
@endsection
