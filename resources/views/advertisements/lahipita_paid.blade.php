@extends('layouts.app')

@section('content')

<div style="min-height: calc(100vh - 220px); display:flex; flex-direction:column;">
<div class="container mt-4">
    <h2 class="mb-3">Lahipita - Paid Advertisements</h2>

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
        @media (max-width: 767px) { .action-btns .btn { margin-bottom: .35rem; } }
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
            @include('advertisements._filters', ['action' => url('/advertisements/lahipita/paid')])
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
                    <th>Amount</th>
                    <th>Publish Date</th>
                    <th>Payment Date</th>
                    <th>Payment Status</th>
                    <th>Approved By</th>
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

                        <td>Rs. {{ number_format($ad->amount, 2) }}</td>

                        <td>
                            {{ $ad->publish_date
                            ? \Carbon\Carbon::parse($ad->publish_date)->format('Y-m-d')
                                : '-'
                            }}
                        </td>

                        <td>{{ $ad->payment_date }}</td>

                        {{-- PAYMENT STATUS --}}
                        <td>
                            @include('partials.payment-status-badge', ['status' => $ad->payment_status])
                        </td>

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

                        {{-- ACTIONS --}}
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
                        <td colspan="8" class="text-center text-muted">
                            No Lahipita paid advertisements found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if(method_exists($ads, 'links'))
            <div class="mt-3 px-3">
                {{ $ads->links() }}
            </div>
        @endif

        </div>
      </div>
    </div>

</div>
</div>

{{-- DOWNLOAD CONFIRM SCRIPT --}}
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