@extends('layouts.app')

@section('title', 'Translation Queue')

@section('content')
@php
    $currentRole = strtolower(trim((string) data_get(session('user'), 'role', '')));
    $showAuditColumns = $currentRole === 'administrative level';
@endphp
<div style="min-height: calc(100vh - 220px); display:flex; flex-direction:column;">
    <div class="container mt-4">
        <h2 class="mb-3">Translation Queue</h2>

        <style>
            .ads-card { border-radius: 12px; box-shadow: 0 6px 18px rgba(18,38,63,0.06); overflow: hidden; }
            .ads-table thead th { background: #f8fafc; border-bottom: 2px solid #e9eef4; font-weight: 600; color: #5b6b7a; }
            .ads-table td, .ads-table th { vertical-align: middle; }
            .ads-table tbody tr:hover { background: rgba(99,102,241,0.04); }
            .viewed-row > td { background-color: #f3f4f6 !important; }
            .viewed-row:hover > td { background-color: #e9ecef !important; }
        </style>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <div class="card ads-card">
        <div class="card-body p-0">
        <div class="table-responsive p-3">
            <table class="table table-hover ads-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ad ID</th>
                        <th>Publication</th>
                        <th>Category</th>
                        <th>Status</th>
                        @if($showAuditColumns)
                            <th>Translated &amp; Approved By</th>
                        @endif
                        <th>Sent</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advertisements as $advertisement)
                        @php
                            $categoryName = $advertisement->category_name_en ?: $advertisement->category_name_si ?: '—';
                        @endphp
                        <tr class="{{ $advertisement->translated_advertisement_description_done ? 'viewed-row' : '' }}">
                            <td>#{{ $advertisement->id }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $advertisement->publication ?? '')) }}</td>
                            <td>{{ $categoryName }}</td>
                            <td>
                                @if($advertisement->translated_advertisement_description_done)
                                    <span class="badge bg-label-success">Translated</span>
                                @else
                                    <span class="badge bg-label-warning">Pending</span>
                                @endif
                            </td>
                            @if($showAuditColumns)
                            <td>
                                @if($advertisement->translated_advertisement_description_done && $advertisement->translated_approved_by_name)
                                    <span class="badge bg-success">
                                        <i class="bx bx-user-check"></i>
                                        {{ $advertisement->translated_approved_by_name }}
                                    </span>
                                    @if($advertisement->approved_at)
                                        <small class="text-muted d-block mt-1">
                                            {{ \Illuminate\Support\Carbon::parse($advertisement->approved_at)->format('Y-m-d H:i') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @endif
                            <td>{{ \Illuminate\Support\Carbon::parse($advertisement->translation_requested_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('translation.queue.show', $advertisement->id) }}" class="btn btn-sm {{ $advertisement->translated_advertisement_description_done ? 'btn-outline-secondary' : 'btn-primary' }}">
                                    {{ $advertisement->translated_advertisement_description_done ? 'Retrieve' : 'Translate' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showAuditColumns ? 7 : 6 }}" class="text-center text-muted py-5">No advertisements have been sent for translation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                @include('advertisements._pagination_count', ['ads' => $advertisements])
                {{ $advertisements->links() }}
            </div>
        </div>
        </div>
    </div>
    </div>
</div>
@endsection
