@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <h2>Paid Advertisements</h2>

```
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
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($ads as $ad)
            <tr>
                <td>{{ $ad->id }}</td>
                <td>{{ $ad->customer_name }}</td>
                <td>{{ $ad->category_name }}</td>

                <td>{{ Str::limit($ad->advertisement_description, 40) }}</td>

                <td>{{ $ad->district_name }}</td>
                <td>{{ $ad->city_name }}</td>

                <td>{{ $ad->publication }}</td>

                <td>Rs. {{ number_format($ad->amount, 2) }}</td>

                <td>{{ $ad->payment_method }}</td>

                <td>{{ $ad->payment_date }}</td>

                <td>
                    <a href="{{ url('/advertisements/' . $ad->id . '/view') }}"
                       class="btn btn-sm btn-info">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center text-muted">
                    No paid advertisements found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
```

</div>
@endsection
