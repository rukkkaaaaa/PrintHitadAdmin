@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">

                <div>
                    <h4 class="mb-1">Monthly Reports</h4>
                    <p class="text-muted mb-0">
                        View advertisements and download reports by selected month.
                    </p>
                </div>

                <form action="{{ url('/reports') }}" method="GET" class="d-flex gap-2 align-items-center">
                    <input
                        type="month"
                        name="month"
                        class="form-control"
                        value="{{ $monthInput }}"
                        required
                    >

                    <button type="submit" class="btn btn-primary">
                        Generate
                    </button>
                </form>

            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Download PDF Reports - {{ $monthLabel }}</h5>
        </div>

        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">

                <a href="{{ url('/reports/web-combined/hitad-paid/pdf') . '?month=' . $monthInput }}"
                   class="btn btn-outline-info">
                    Web Combined Hitad Paid
                </a>

                <a href="{{ url('/reports/web-combined/hitad-unpaid/pdf') . '?month=' . $monthInput }}"
                   class="btn btn-outline-info">
                    Web Combined Hitad Unpaid
                </a>

                <a href="{{ url('/reports/web-combined/lahipita-paid/pdf') . '?month=' . $monthInput }}"
                   class="btn btn-outline-info">
                    Web Combined Lahipita Paid
                </a>

                <a href="{{ url('/reports/web-combined/lahipita-unpaid/pdf') . '?month=' . $monthInput }}"
                   class="btn btn-outline-info">
                    Web Combined Lahipita Unpaid
                </a>

                <a href="{{ url('/reports/hitad-paid/pdf') . '?month=' . $monthInput }}"
                   class="btn btn-outline-success">
                    Hitad Paid
                </a>

                <a href="{{ url('/reports/hitad-unpaid/pdf') . '?month=' . $monthInput }}"
                   class="btn btn-outline-warning">
                    Hitad Unpaid
                </a>

                <a href="{{ url('/reports/lahipita-paid/pdf') . '?month=' . $monthInput }}"
                   class="btn btn-outline-success">
                    Lahipita Paid
                </a>

                <a href="{{ url('/reports/lahipita-unpaid/pdf') . '?month=' . $monthInput }}"
                   class="btn btn-outline-warning">
                    Lahipita Unpaid
                </a>

            </div>
        </div>
    </div>

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Advertisements</h5>
                <small class="text-muted">{{ $monthLabel }}</small>
            </div>

            <span class="badge bg-primary">
                {{ $ads->total() }} Records
            </span>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Publication</th>
                            <th>Web Combined</th>
                            <th>Customer</th>
                            <th>Category</th>
                            <th>District</th>
                            <th>City</th>
                            <th>Publish Date</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Payment Date</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($ads as $ad)

                        <tr>

                            <td>{{ $ad->id }}</td>

                            <td>
                                @if($ad->publication === 'hitad_print')
                                    Hitad
                                @elseif($ad->publication === 'lahipita')
                                    Lahipita
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $ad->publication)) }}
                                @endif
                            </td>

                            <td>
                                @if($ad->web_combined_ad_hitadlk)
                                    <span class="badge bg-info">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>

                            <td>{{ $ad->customer_name }}</td>

                            <td>{{ $ad->category_name }}</td>

                            <td>{{ $ad->district_name ?? '-' }}</td>

                            <td>{{ $ad->city_name ?? '-' }}</td>

                            <td>
                                {{ $ad->publish_date
                                    ? \Illuminate\Support\Carbon::parse($ad->publish_date)->format('Y-m-d')
                                    : '-'
                                }}
                            </td>

                            <td>
                                {{ is_null($ad->amount)
                                    ? '-'
                                    : 'Rs. ' . number_format($ad->amount, 2)
                                }}
                            </td>

                            <td>
                                @include('partials.payment-status-badge', [
                                    'status' => $ad->payment_status
                                ])
                            </td>

                            <td>
                                {{ $ad->payment_date
                                    ? \Illuminate\Support\Carbon::parse($ad->payment_date)->format('Y-m-d')
                                    : '-'
                                }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                No advertisements found for {{ $monthLabel }}.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        @if($ads->hasPages())
            <div class="card-footer">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">

                    <small class="text-muted">
                        Showing {{ $ads->firstItem() }}
                        to {{ $ads->lastItem() }}
                        of {{ $ads->total() }} records
                    </small>

                    {{ $ads->links() }}

                </div>
            </div>
        @endif

    </div>

</div>
@endsection