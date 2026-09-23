@extends('layouts.admin.app')

@section('title', 'Edit Influencer')

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <i class="fi fi-rr-edit"></i> Edit Influencer
            </h2>
        </div>
        <a href="{{ route('admin.influencers.index') }}" class="btn btn-secondary">
            <i class="fi fi-rr-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.influencers.update', $influencer->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-4">
                            <label for="name" class="form-label font-weight-bold">Influencer Name</label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="{{ old('name', $influencer->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">
                                    <i class="fab fa-instagram text-danger"></i> Instagram Followers
                                </label>
                                <input type="number" name="instagram_followers" class="form-control" 
                                       value="{{ old('instagram_followers', $influencer->instagram_followers) }}">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">
                                    <i class="fab fa-youtube text-danger"></i> YouTube Subscribers
                                </label>
                                <input type="number" name="youtube_subscribers" class="form-control" 
                                       value="{{ old('youtube_subscribers', $influencer->youtube_subscribers) }}">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">
                                    <i class="fab fa-tiktok text-dark"></i> TikTok Followers
                                </label>
                                <input type="number" name="tiktok_followers" class="form-control" 
                                       value="{{ old('tiktok_followers', $influencer->tiktok_followers) }}">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="bio" class="form-label font-weight-bold">Bio / Description</label>
                            <textarea name="bio" id="bio" rows="5" class="form-control" required>{{ old('bio', $influencer->bio) }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm border">
                            <div class="card-body text-center">
                                <label class="form-label font-weight-bold mb-3">Profile Image</label>
                                
                                <div class="mb-3">
                                    <img id="viewer" 
                                       src="{{ asset('public/' . $influencer->profile_image) }}" 
                                         onerror="this.src='{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}'" 
                                         alt="Influencer Image" 
                                         style="width: 200px; height: 200px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd;">
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="profile_image" id="customFileEg1" class="custom-file-input"
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="customFileEg1">Choose new image</label>
                                </div>
                                <small class="text-muted mt-2 d-block">Ratio 1:1 (500 x 500 px) recommended</small>
                            </div>
                        </div>
                        
                        <div class="card mt-3 bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Current Stats</h5>
                                <hr>
                                <p class="mb-1"><strong>Total Reach:</strong> {{ number_format($influencer->total_reach) }}</p>
                                <p class="mb-1"><strong>Rating:</strong> ★ {{ $influencer->avg_rating }}</p>
                                <p class="mb-0"><strong>Reviews:</strong> {{ $influencer->rating_count }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.influencers.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Influencer</button>
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