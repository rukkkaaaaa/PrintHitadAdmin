@extends('layouts.app')

@section('content')

@php

    $currentRole = strtolower(
        trim((string) data_get(session('user'), 'role', ''))
    );

    $hideAuditColumns = $currentRole === 'super admin';

@endphp

<div style="min-height: calc(100vh - 220px); display:flex; flex-direction:column;">
<div class="container mt-4">
    <h2 class="mb-3">All Print Advertisements</h2>

    <style>
        /* Unified page tweaks for all advertisement tables */
        .ads-card { border-radius: 12px; box-shadow: 0 6px 18px rgba(18,38,63,0.06); overflow: hidden; }
        .ads-table thead th { background: #f8fafc; border-bottom: 2px solid #e9eef4; font-weight:600; color:#5b6b7a; }
        .ads-table tbody tr:hover { background: rgba(99,102,241,0.04); }
        .ads-table td, .ads-table th { vertical-align: middle; }
        .ads-table td .text-muted { display:block; }
        .badge-pill { border-radius: 999px; padding: .35rem .6rem; font-weight:600; }
        .action-btns .btn { margin-right: .35rem; }
        .search-input .form-control { border-right: 0; }
        .search-input .input-group-text { background: transparent; border-left: 0; }
        .table-responsive { padding: 0.75rem 1rem; }
        @media (max-width: 767px) {
            .action-btns .btn { margin-bottom: .35rem; }
        }
        .approved-ad-row > td {
        background-color: #d1e7dd !important;
        }

        .approved-ad-row:hover > td {
        background-color: #badbcc !important;
        }
    </style>

    <!-- Search + actions -->
    <div class="row mb-3">
        <div class="col-12 mb-2">
            <form action="{{ url('/all-print-ads') }}" method="GET">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" name="customer_name" class="form-control" placeholder="Customer name"
                               value="{{ request('customer_name') }}">
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="publish_date_from" class="form-control" title="Publish date from"
                               value="{{ request('publish_date_from', request('publish_date')) }}">
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="publish_date_to" class="form-control" title="Publish date to"
                               value="{{ request('publish_date_to') }}">
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="title" class="form-control" placeholder="Ad title"
                               value="{{ request('title') }}">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Search</button>
                    </div>
                </div>

                <div class="row g-2 mt-2 align-items-center">
                    <div class="col-md-2">
                        <input type="text" name="phone" class="form-control" placeholder="Phone"
                               value="{{ request('phone') }}">
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="email" class="form-control" placeholder="Email"
                               value="{{ request('email') }}">
                    </div>

                    <div class="col-md-4">
                        <input type="text" name="category" class="form-control" placeholder="Category name"
                               value="{{ request('category') }}">
                    </div>
                    <!-- <div class="col-md-8 text-end">
                        <a href="{{ url('/advertisements/create') }}" class="btn btn-success me-2">
                            <i class="bx bx-plus"></i> New Ad
                        </a>
                        <a href="#" class="btn btn-outline-secondary">
                            <i class="bx bx-cloud-download"></i> Export
                        </a>
                    </div> -->
                </div>
            </form>
        </div>
        <div class="col-md-4 col-sm-12 text-md-end text-sm-start">
            {{-- kept for compatibility on larger screens, hidden by default because controls moved above --}}

        </div>
    </div>

    <div class="card ads-card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover ads-table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Category</th>
                    <th>Publication</th>
                    <th>Publish Date</th>
                    <th>Payment</th>
                    @unless($hideAuditColumns)
                    <th>Approved By</th>
                @endunless
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ads as $ad)
                    <tr class="{{ !empty($ad->approved_at) ? 'approved-ad-row' : '' }}">
                    <tr>
                        <td>{{ $ad->id }}</td>
                        <td>{{ $ad->customer_name }}</td>
                        <td>{{ $ad->category_name }}</td>

                        <td>{{ $ad->publication_label ?? $ad->publication }}</td>

                        <td>{{ $ad->publish_date }}</td>

                        <td>
                            @include('partials.payment-status-badge', ['status' => $ad->payment_status])
                        </td>

                        @unless($hideAuditColumns)
                        <td>
                            @if(!empty($ad->approved_by_admin_id))

                                <span class="badge bg-success">
                                 <i class="bx bx-check-circle"></i>
                                    {{ $ad->approved_admin_name ?? 'Admin' }}
                                </span>

                            @if(!empty($ad->approved_at))
                                <small class="text-muted d-block mt-1">
                                {{ \Carbon\Carbon::parse($ad->approved_at)->format('Y-m-d H:i') }}
                            </small>
                            @endif

                         @else

                            <span class="text-muted">
                                Not Approved
                                </span>

                            @endif
                        </td>
                        @endunless

                        <td class="action-btns">
                            <a href="{{ url('/advertisements/' . $ad->id . '/view') }}" class="btn btn-sm btn-outline-info">
                                <i class="bx bx-show"></i>
                            </a>

                            <a href="{{ url('/advertisements/' . $ad->id . '/edit') }}" class="btn btn-sm btn-outline-warning">
                                <i class="bx bx-edit-alt"></i>
                            </a>

                            <button class="btn btn-sm btn-outline-success"
                                onclick="confirmDownload(this)"
                                data-download-url="{{ url('/advertisements/' . $ad->id . '/download') }}">
                                <i class="bx bx-download"></i>
                            </button>

                            <!--button class="btn btn-sm btn-outline-success" onclick="confirmDownload({{ $ad->id }})">
                                <i class="bx bx-download"></i>
                            </button -->
                            
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="{{ $hideAuditColumns ? 7 : 8 }}" class="text-center text-muted">
                            No advertisements found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3 px-3">
            @include('advertisements._pagination_count', ['ads' => $ads])
            {{ $ads->links() }}
        </div>

        </div>
      </div>
    </div>

</div>
</div>

<script>
	function confirmDownload(btn) {
    if (confirm("Do you want to download the ad details?")) {
        window.location.href = btn.dataset.downloadUrl;
    }
}
/** function confirmDownload(adId) {
    if (confirm("Do you want to download the ad details?")) {
        window.location.href = "/advertisements/" + adId + "/download";
    }
} */
</script>

@endsection