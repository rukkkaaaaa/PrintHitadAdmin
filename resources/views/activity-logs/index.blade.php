@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Activity Logs</h2>
        <span class="badge bg-primary">{{ $activities->total() }} Records</span>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 90px;">Log ID</th>
                        <th>Admin</th>
                        <th>Activity</th>
                        <th style="width: 190px;">Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>{{ $activity->id }}</td>
                            <td>{{ $activity->admin_name ?? 'Unknown admin' }}</td>
                            <td>{{ $activity->task }}</td>
                            <td>
                                {{ $activity->created_at
                                    ? \Carbon\Carbon::parse($activity->created_at)->format('Y-m-d H:i:s')
                                    : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No activity has been recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activities->hasPages())
            <div class="card-body border-top">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
