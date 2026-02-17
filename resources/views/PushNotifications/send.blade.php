@extends('layouts.app')

@section('title', 'Send Push Notification')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h5>Send Notification to {{ $user->name }}</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('push.send') }}">
                @csrf

                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="body" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Redirect URL</label>
                    <input type="url" name="url" class="form-control" value="{{ url('/') }}">
                </div>

                <button class="btn btn-primary">
                    Send Notification
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
