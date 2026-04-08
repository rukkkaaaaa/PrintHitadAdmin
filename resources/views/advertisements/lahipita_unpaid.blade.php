@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <h2>Lahipita Unpaid Advertisements</h2>

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

                    <td>{{ \Illuminate\Support\Str::limit($ad->advertisement_description, 40) }}</td>

                    <td>{{ $ad->district_name }}</td>
                    <td>{{ $ad->city_name }}</td>

                    <td>{{ $ad->publication }}</td>

                    <td>
                        {{ $ad->amount ? 'Rs. ' . number_format($ad->amount, 2) : '-' }}
                    </td>

                    <td>{{ $ad->payment_method ?? '-' }}</td>

                    <td>
                        <span class="badge bg-danger">Unpaid</span>
                    </td>

                    <td>
                        <a href="{{ url('/advertisements/' . $ad->id . '/view') }}"
                           class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center text-muted">
                        No Lahipita unpaid advertisements found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection