@extends('layouts.admin.app')


@section('content')
<div class="container">
    <h2>Add New Influencer</h2>
    <div class="card mt-3">
        <div class="card-body">
            <form action="{{ route('admin.influencers.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Profile Image</label>
                        <input type="file" name="profile_image" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label><i class="fab fa-instagram"></i> Instagram Followers</label>
                        <input type="number" name="instagram_followers" class="form-control" value="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label><i class="fab fa-youtube"></i> YouTube Subscribers</label>
                        <input type="number" name="youtube_subscribers" class="form-control" value="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label><i class="fab fa-tiktok"></i> TikTok Followers</label>
                        <input type="number" name="tiktok_followers" class="form-control" value="0">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Bio / Description</label>
                    <textarea name="bio" class="form-control" rows="4"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Save Influencer</button>
            </form>
        </div>
    </div>
</div>
@endsection