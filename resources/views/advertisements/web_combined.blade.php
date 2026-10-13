@extends('layouts.app')

@php
    $publicationLabel = $publication === 'lahipita' ? 'Lahipita' : 'Hitad';
    $pageTitle = $publicationLabel . ' Web Combined Ads';
    $currentRole = strtolower(trim((string) data_get(session('user'), 'role', '')));
    $showAuditColumns = $currentRole === 'administrative level';
@endphp

@section('title', $pageTitle)

@section('content')
<div style="min-height: calc(100vh - 220px); display:flex; flex-direction:column;">
    <div class="container mt-4">
        <h2 class="mb-3">{{ $pageTitle }}</h2>

        <style>
            .ads-card { border-radius: 12px; box-shadow: 0 6px 18px rgba(18,38,63,0.06); overflow: hidden; }
            .ads-table thead th { background: #f8fafc; border-bottom: 2px solid #e9eef4; font-weight: 600; color: #5b6b7a; }
            .ads-table td, .ads-table th { vertical-align: middle; }
            .ads-table tbody tr:hover { background: rgba(99,102,241,0.04); }
            .viewed-row > td { background-color: #f3f4f6 !important; }
            .viewed-row:hover > td { background-color: #e9ecef !important; }
        </style>

    <div class="card ads-card">
        <div class="card-body p-0">
        <div class="table-responsive p-3">
            <table class="table table-hover ads-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ad ID</th>
                        <th>Publication</th>
                        <th>Customer</th>
                        <th>Category</th>
                        <th>Publish Date</th>
                        @if($showAuditColumns)
                            <th>Viewed By</th>
                        @endif
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ads as $ad)
                        <tr class="{{ $ad->viewed_by_admin_id ? 'viewed-row' : '' }}">
                            <td>#{{ $ad->id }}</td>
                            <td>{{ $ad->publication === 'lahipita' ? 'Lahipita' : 'Hitad Print' }}</td>
                            <td>{{ $ad->customer_name }}</td>
                            <td>{{ $ad->category_name ?: '—' }}</td>
                            <td>{{ $ad->publish_date ? \Illuminate\Support\Carbon::parse($ad->publish_date)->format('Y-m-d') : '—' }}</td>
                            @if($showAuditColumns)
                            <td>
                                @if($ad->viewed_by_admin_id)
                                    <span class="badge bg-success">
                                        <i class="bx bx-user-check"></i>
                                        {{ $ad->viewed_admin_name ?? 'Admin' }}
                                    </span>
                                    @if($ad->viewed_at)
                                        <small class="text-muted d-block mt-1">
                                            {{ \Illuminate\Support\Carbon::parse($ad->viewed_at)->format('Y-m-d H:i') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">Not Viewed</span>
                                @endif
                            </td>
                            @endif
                            <td class="text-end">
                                @php
                                    $viewUrl = $ad->publication === 'lahipita'
                                        ? url('/advertisements/lahipita/approved/' . $ad->id . '/view')
                                        : url('/advertisements/hitad/approved/' . $ad->id . '/view');
                                @endphp
                                <form method="POST" action="{{ $viewUrl }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-info">
                                        <i class="bx bx-show"></i>
                                        {{ $ad->viewed_by_admin_id ? 'Retrieve' : 'View' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showAuditColumns ? 7 : 6 }}" class="text-center text-muted py-5">No web-combined advertisements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                @include('advertisements._pagination_count', ['ads' => $ads])
                {{ $ads->links() }}
            </div>
        </div>
        </div>
    </div>
    </div>
</div>
@endsection
