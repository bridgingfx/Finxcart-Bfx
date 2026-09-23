@extends('layouts.admin.app')

@section('title', 'Edit Broker')
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
        <div>
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <i class="fi fi-rr-edit"></i> Edit Broker
            </h2>
        </div>
        <a href="{{ route('admin.brokers.index') }}" class="btn btn-secondary">
            <i class="fi fi-rr-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.brokers.update', $broker->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-7">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Broker Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $broker->name) }}" required>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="text-primary font-weight-bold">Referral / Affiliate URL</label>
                                <input type="url" name="affiliate_url" class="form-control" value="{{ old('affiliate_url', $broker->affiliate_url) }}" required>
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-3 bg-light">
                            <h5 class="text-primary mb-3">System Authority Scores (0 - 10)</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>License (50%)</label>
                                    <input type="number" step="0.1" max="10" name="score_license" class="form-control" value="{{ old('score_license', $broker->score_license) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Software (30%)</label>
                                    <input type="number" step="0.1" max="10" name="score_software" class="form-control" value="{{ old('score_software', $broker->score_software) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Stability (20%)</label>
                                    <input type="number" step="0.1" max="10" name="score_stability" class="form-control" value="{{ old('score_stability', $broker->score_stability) }}" required>
                                </div>
                            </div>
                            <div class="text-right">
                                <small class="text-muted">Current System Rating: <strong>{{ $broker->system_rating }}/10</strong></small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Regulations</label>
                                <input type="text" name="regulations" class="form-control" value="{{ old('regulations', $broker->regulations) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Min Deposit</label>
                                <input type="text" name="min_deposit" class="form-control" value="{{ old('min_deposit', $broker->min_deposit) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Platforms</label>
                                <input type="text" name="platforms" class="form-control" value="{{ old('platforms', $broker->platforms) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description', $broker->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card shadow-sm border">
                            <div class="card-body text-center">
                                <label class="font-weight-bold mb-3">Broker Logo</label>
                                <div class="mb-3">
                                    <img id="viewer" 
                                         src="{{ asset($broker->logo) }}" 
                                         onerror="this.src='{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}'"
                                         class="rounded border" 
                                         style="width: 200px; height: auto;">
                                </div>
                                <div class="custom-file text-left">
                                    <input type="file" name="logo" id="customFileEg1" class="custom-file-input" accept="image/*">
                                    <label class="custom-file-label" for="customFileEg1">Change Logo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update Broker</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#customFileEg1").change(function () {
            readURL(this);
        });
    </script>
@endpush