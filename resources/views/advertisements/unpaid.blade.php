@extends('layouts.app')

@section('content')
<div style="min-height: calc(100vh - 220px); display:flex; flex-direction:column;">
<div class="container mt-4">
    <h2 class="mb-3">Unpaid Advertisements</h2>

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
    </style>

    <!-- Search + actions -->
    <div class="row mb-3">
        <div class="col-md-8 col-sm-12 mb-2">
            <form action="{{ url('/advertisements/unpaid') }}" method="GET">
                <div class="input-group search-input">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by ad title or customer name..."
                           value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
        <div class="col-md-4 col-sm-12 text-md-end text-sm-start">
            <a href="{{ url('/advertisements/create') }}" class="btn btn-success me-2">
                <i class="bx bx-plus"></i> New Ad
            </a>
            <a href="#" class="btn btn-outline-secondary">
                <i class="bx bx-cloud-download"></i> Export
            </a>
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
                    <th>Title</th>
                    <th>District</th>
                    <th>City</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ads as $ad)
                    <tr>
                        <td>{{ $ad->id }}</td>
                        <td>{{ $ad->customer_name }}</td>
                        <td>{{ $ad->category_name }}</td>

                        <td>
                            {{ \Illuminate\Support\Str::limit($ad->advertisement_description, 40) }}
                        </td>

                        <td>{{ $ad->district_name }}</td>
                        <td>{{ $ad->city_name }}</td>

                        <td>
                            @if(!is_null($ad->amount))
                                Rs. {{ number_format($ad->amount, 2) }}
                            @else
                                -
                            @endif
                        </td>

                        {{-- PAYMENT STATUS --}}
                        <td>
                            @include('partials.payment-status-badge', ['status' => $ad->payment_status])
                        </td>

                        {{-- ACTIONS --}}
                        <td class="action-btns">
                            <a href="{{ url('/advertisements/' . $ad->id . '/view') }}" class="btn btn-sm btn-outline-info">
                                <i class="bx bx-show"></i>
                            </a>

                            <a href="{{ url('/advertisements/' . $ad->id . '/edit') }}" class="btn btn-sm btn-outline-warning">
                                <i class="bx bx-edit-alt"></i>
                            </a>

                            <button class="btn btn-sm btn-outline-success" onclick="confirmDownload({{ $ad->id }})">
                                <i class="bx bx-download"></i>
                            </button>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            No unpaid advertisements found.
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

{{-- ✅ DOWNLOAD CONFIRM SCRIPT --}}
<script>
function confirmDownload(adId) {
    if (confirm("Do you want to download the ad details?")) {
        window.location.href = "/advertisements/" + adId + "/download";
    }
}
</script>

@endsection