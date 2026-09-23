@extends('layouts.admin.app')
@section('title', 'Edit Ad')

@section('content')
<div class="content container-fluid">
    <h1>Edit Ad: {{ $ad->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Errors!</h4>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.ads.update', $ad) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name (Internal)</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $ad->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="type" class="form-label">Ad Type</label>
                            <select class="form-select" id="type" name="type" onchange="toggleAdFields()">
                                <option value="image" @selected(old('type', $ad->type) == 'image')>Image</option>
                                <option value="code" @selected(old('type', $ad->type) == 'code')>Code (e.g., AdSense)</option>
                                <option value="text" @selected(old('type', $ad->type) == 'text')>Text / HTML</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="destination_url_field">
                    <label for="destination_url" class="form-label">Destination URL</label>
                    <input type="url" class="form-control" id="destination_url" name="destination_url" value="{{ old('destination_url', $ad->destination_url) }}" placeholder="https://">
                </div>

                <div class="mb-3" id="image_path_field">
                    <label for="image_path" class="form-label">Image File (Optional: Replace)</label>
                    <input class="form-control" type="file" id="image_path" name="image_path" accept="image/*">
                    @if($ad->type == 'image' && $ad->image_path)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $ad->image_path) }}" alt="Current Ad" style="max-height: 100px; border-radius: 4px;">
                        </div>
                    @endif
                </div>

                <div class="mb-3" id="content_field">
                    <label for="content" class="form-label">Ad Content</label>
                    <textarea class="form-control" id="content" name="content" rows="5" placeholder="Enter your HTML, JS code, or text content here">{{ old('content', $ad->content) }}</textarea>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="ad_group_id" class="form-label">Ad Group (Optional)</label>
                            <select class="form-select" id="ad_group_id" name="ad_group_id">
                                <option value="">-- None --</option>
                                @foreach($adGroups as $id => $name)
                                    <option value="{{ $id }}" @selected(old('ad_group_id', $ad->ad_group_id) == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $ad->start_date?->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', $ad->end_date?->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                        @checked(old('is_active', $ad->is_active))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <a href="{{ route('admin.ads.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    function toggleAdFields() {
        const type = document.getElementById('type').value;
        const urlField = document.getElementById('destination_url_field');
        const imgField = document.getElementById('image_path_field');
        const contentField = document.getElementById('content_field');

        if (type === 'image') {
            urlField.style.display = 'block';
            imgField.style.display = 'block';
            contentField.style.display = 'none';
        } else if (type === 'code') {
            urlField.style.display = 'none';
            imgField.style.display = 'none';
            contentField.style.display = 'block';
        } else if (type === 'text') {
            urlField.style.display = 'block';
            imgField.style.display = 'none';
            contentField.style.display = 'block';
        }
    }
    document.addEventListener('DOMContentLoaded', toggleAdFields);
</script>
@endpush
