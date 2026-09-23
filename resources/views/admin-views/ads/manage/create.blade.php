@extends('layouts.admin.app')
@section('title', 'Create Ad')

@section('content')
<div class="content container-fluid">
    <h1>Create Ad</h1>

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

    <div class="alert alert-light border" role="alert">
        <h5 class="alert-heading">Creating a New Ad</h5>
        <p>Fill out the fields below. The form will automatically show/hide fields based on the <strong>Ad Type</strong> you select.</p>
        <ul>
            <li><strong>Image:</strong> A standard clickable banner (e.g., JPG, PNG, GIF).</li>
            <li><strong>Code:</strong> For third-party ad code like Google AdSense. This type is <strong>not</strong> clickable (the code handles its own clicks).</li>
            <li><strong>Text:</strong> Simple text or HTML content that will be wrapped in a clickable link.</li>
        </ul>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.ads.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name (Internal)</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            <div class="form-text">An internal name for you to identify this ad (e.g., "Homepage Summer Sale Banner").</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="type" class="form-label">Ad Type</label>
                            <select class="form-select" id="type" name="type" onchange="toggleAdFields()">
                                <option value="image" @selected(old('type') == 'image')>Image</option>
                                <option value="code" @selected(old('type') == 'code')>Code (e.g., AdSense)</option>
                                <option value="text" @selected(old('type') == 'text')>Text / HTML</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="destination_url_field">
                    <label for="destination_url" class="form-label">Destination URL</label>
                    <input type="url" class="form-control" id="destination_url" name="destination_url" value="{{ old('destination_url') }}" placeholder="https://">
                    <div class="form-text">The URL the user will be redirected to when they click the ad. (Used for <strong>Image</strong> and <strong>Text</strong> types).</div>
                </div>

                <div class="mb-3" id="image_path_field">
                    <label for="image_path" class="form-label">Image File</label>
                    <input class="form-control" type="file" id="image_path" name="image_path" accept="image/*">
                    <div class="form-text">Required only for "Image" ads.</div>
                </div>

                <div class="mb-3" id="content_field">
                    <label for="content" class="form-label">Ad Content</label>
                    <textarea class="form-control" id="content" name="content" rows="5" placeholder="Enter your HTML, JS code, or text content here">{{ old('content') }}</textarea>
                    <div class="form-text">Required for "Code" or "Text" ads. Paste your AdSense code, custom HTML, or plain text here.</div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="ad_group_id" class="form-label">Ad Group (Optional)</label>
                            <select class="form-select" id="ad_group_id" name="ad_group_id">
                                <option value="">-- None --</option>
                                @foreach($adGroups as $id => $name)
                                    <option value="{{ $id }}" @selected(old('ad_group_id') == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Assigning this ad to a group will allow it to be rotated using the group's shortcode.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}">
                            <div class="form-text">Schedule this ad to start showing at a future date.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="{{ old('end_date') }}">
                            <div class="form-text">Schedule this ad to stop showing after this date.</div>
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">Active</Signature>
                    <div class="form-text">An ad must be active to be displayed.</div>
                </div>

                <a href="{{ route('admin.ads.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create</button>
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
