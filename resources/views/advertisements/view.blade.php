@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <h2>Advertisement Details</h2>

```
<div class="card">
    <div class="card-body">

        <!-- Title -->
        <h4>Advertisement #{{ $ad->id }}</h4>

        <!-- Ad Info -->
        <p><strong>Description:</strong> {{ $ad->advertisement_description }}</p>
        <hr>

        <p><strong>Publication:</strong> {{ $ad->publication }}</p>
        <p><strong>Publish Date:</strong> {{ $ad->publish_date }}</p>

        <p><strong>Status:</strong>
            {!! $ad->status == 1
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-danger">Inactive</span>' !!}
        </p>

        <hr>

        <!-- Customer Info -->
        <h5>Customer Info</h5>
        <p><strong>Name:</strong> {{ $ad->customer_name }}</p>
        <p><strong>Address:</strong> {{ $ad->address }}</p>
        <p><strong>Telephone:</strong> {{ $ad->telephone }}</p>
        <p><strong>Email:</strong> {{ $ad->email }}</p>
        <p><strong>NIC/Passport:</strong> {{ $ad->nic_passport }}</p>

        <hr>

        <!-- Location -->
        <h5>Location</h5>
        <p><strong>Category:</strong> {{ $ad->category_name }}</p>
        <p><strong>District:</strong> {{ $ad->district_name }}</p>
        <p><strong>City:</strong> {{ $ad->city_name }}</p>

        <hr>

        <!-- Payment Section -->
        <h5>Payment Details</h5>

        @if($ad->payment_method)

            <p><strong>Payment Method:</strong> {{ $ad->payment_method }}</p>

            <p><strong>Amount:</strong> Rs. {{ number_format($ad->amount, 2) }}</p>

            <p><strong>Payment Date:</strong> {{ $ad->payment_date }}</p>

            <p><strong>Status:</strong>
                @if($ad->is_success)
                    <span class="badge bg-success">✔ Paid</span>
                @else
                    <span class="badge bg-warning">Pending</span>
                @endif
            </p>

        @else

            <p class="text-danger"><strong>No Payment Found</strong></p>

        @endif

    </div>
</div>

<a href="{{ url('/advertisements') }}" class="btn btn-secondary mt-3">
    Back to All Ads
</a>
```

</div>
@endsection
