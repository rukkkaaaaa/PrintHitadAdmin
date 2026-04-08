@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Unpaid Advertisements</h2>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Category</th>
                <th>Description</th>
                <th>District</th>
                <th>City</th>
                <th>Publication</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Payment Date</th>
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

                    <td>{{ $ad->publication }}</td>

                    <td>
                        @if(!is_null($ad->amount))
                            Rs. {{ number_format($ad->amount, 2) }}
                        @else
                            -
                        @endif
                    </td>

                    <td>{{ $ad->payment_method ?? 'Not Selected' }}</td>

                    <td>{{ $ad->payment_date ?? '-' }}</td>

                    {{-- PAYMENT STATUS --}}
                    <td>
                        @if(is_null($ad->payment_status))
                            <span class="badge bg-secondary">No Payment Record</span>

                        @elseif($ad->payment_status == 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>

                        @elseif($ad->payment_status == 'completed' && $ad->is_success)
                            <span class="badge bg-success">Paid</span>

                        @else
                            <span class="badge bg-danger">Unpaid</span>
                        @endif
                    </td>

                    {{-- ACTIONS --}}
                    <td>
                        <a href="{{ url('/advertisements/' . $ad->id . '/view') }}"
                           class="btn btn-sm btn-info">
                            View
                        </a>

                        <a href="{{ url('/advertisements/' . $ad->id . '/edit') }}"
                           class="btn btn-sm btn-warning">
                            Edit
                        </a>

                        <button class="btn btn-sm btn-success"
                                onclick="confirmDownload({{ $ad->id }})">
                            Download
                        </button>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted">
                        No unpaid advertisements found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- DOWNLOAD CONFIRM SCRIPT --}}
<script>
function confirmDownload(adId) {
    if (confirm("Do you want to download the ad details?")) {
        window.location.href = "/advertisements/" + adId + "/download";
    }
}
</script>

@endsection