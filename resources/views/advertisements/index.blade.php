@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>All Advertisements</h2>

    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Category</th>
                <th>Title</th>
                <th>District</th>
                <th>City</th>
                <th>Publish Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ads as $ad)
                <tr>
                    <td>{{ $ad->id }}</td>
                    <td>{{ $ad->customer_name }}</td>
                    <td>{{ $ad->category_name }}</td>
                    <td>{{ $ad->advertisement_title }}</td>
                    <td>{{ $ad->district_name }}</td>
                    <td>{{ $ad->city_name }}</td>
                    <td>{{ $ad->publish_date }}</td>
                    <td>
                        {!! $ad->status == 1 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-danger">Inactive</span>' !!}
                    </td>
                    <td>
                        <a href="{{ url('/advertisements/' . $ad->id . '/view') }}" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
