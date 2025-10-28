@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Advertisement Details</h2>

    <div class="card">
        <div class="card-body">
            <h4>{{ $ad->advertisement_title }}</h4>
            <p><strong>Description:</strong> {{ $ad->advertisement_description }}</p>
            <hr>
            <p><strong>Publish Date:</strong> {{ $ad->publish_date }}</p>
            <p><strong>Status:</strong>
                {!! $ad->status == 1 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Inactive</span>' !!}
            </p>
            <hr>
            <h5>Customer Info</h5>
            <p><strong>Name:</strong> {{ $ad->customer_name }}</p>
            <p><strong>Address:</strong> {{ $ad->address }}</p>
            <p><strong>Telephone:</strong> {{ $ad->telephone }}</p>
            <p><strong>Email:</strong> {{ $ad->email }}</p>
            <p><strong>NIC/Passport:</strong> {{ $ad->nic_passport }}</p>
            <hr>
            <h5>Location</h5>
            <p><strong>Category:</strong> {{ $ad->category_name }}</p>
            <p><strong>District:</strong> {{ $ad->district_name }}</p>
            <p><strong>City:</strong> {{ $ad->city_name }}</p>
        </div>
    </div>

    <a href="{{ url('/advertisements') }}" class="btn btn-secondary mt-3">Back to All Ads</a>
</div>
@endsection
