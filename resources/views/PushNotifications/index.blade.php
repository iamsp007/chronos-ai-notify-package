@extends('layouts.app')

@section('title', 'Push Subscribers')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h5>Push Notification Subscribers</h5>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Browser</th>
                        <th>Device</th>
                        <th>IP</th>
                        <th>Subscribed At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($subscriptions as $sub)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $sub->user->name ?? '-' }}</td>
                        <td>{{ ucfirst($sub->browser) }}</td>
                        <td>{{ ucfirst($sub->device) }}</td>
                        <td>{{ $sub->ip_address }}</td>
                        <td>{{ $sub->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <a href="{{ route('push.send.form', $sub->user_id) }}"
                               class="btn btn-sm btn-success">
                                Send
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            No subscriptions found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
