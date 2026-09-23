@extends('layouts.admin.app')
@section('title', 'Create Ad Group')

@section('content')
<div class="content container-fluid">
    <h1>Create Ad Group</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.ad-groups.store') }}" method="POST" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug (Unique)</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}" required>
                    <div class="form-text">Used for the shortcode, e.g., [ad group="your-slug"]</div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description (Internal)</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <a href="{{ route('admin.ad-groups.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
</div>
@endsection
