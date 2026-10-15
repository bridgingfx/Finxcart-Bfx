@extends('layouts.admin.app')
@section('title', 'Manage Ads')

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Manage Ads</h1>
        <a href="{{ route('admin.ads.create') }}" class="btn btn-primary">Create New Ad</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <h4 class="alert-heading">How to Use the Ad Module</h4>
        <p>This module uses <strong>shortcodes</strong> to place ads on your site. You have two ways to display ads:</p>
        <hr>
        <ol class="mb-0">
            <li>
                <strong>Ad Groups (Recommended):</strong>
                Go to the <a href="{{ route('admin.ad-groups.index') }}" class="alert-link">Ad Groups</a> page to get a group shortcode (e.g., <code>[ad group="sidebar"]</code>). This will <strong>rotate all ads</strong> assigned to that group. This is the best way to manage ad placements like sidebars or footers.
            </li>
            <li class="mt-2">
                <strong>Single Ads:</strong>
                Use a single ad's shortcode from the table below (e.g., <code>[ad id="5"]</code>) to <strong>always show one specific ad</strong>.
            </li>
            <li class="mt-2">
                <strong>How to Make it Work:</strong>
                In your website's frontend Blade files, you <strong>must</strong> wrap the content or shortcode with the <code>@ads()</code> directive.
                <br>
                <strong>Example 1 (Direct Shortcode):</strong> <code>@ ads('[ad group="sidebar"]')(remove space between @ and ads)</code>
                <br>
                <strong>Example 2 (In Post Content):</strong> <code>@ ads($post->body)(remove space between @ and ads)</code>
            </li>
        </ol>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Group</th>
                        <th>Shortcode <i class="bi bi-question-circle" title="Click to copy"></i></th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ads as $ad)
                        <tr>
                            <td>{{ $ad->name }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($ad->type) }}</span></td>
                            <td>{{ $ad->adGroup->name ?? 'N/A' }}</td>
                            <td><span class="shortcode" title="Click to copy">{{ $ad->shortcode }}</span></td>
                            <td>
                                @if ($ad->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.ads.edit', $ad) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.ads.destroy', $ad) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')" novalidate>
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
                            <td colspan="6" class="text-center">No ads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $ads->links() }}
        </div>
    </div>
</div>
@endsection
