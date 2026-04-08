@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>All Advertisements</h2>

    <!-- 🔍 Search Form -->
    <form action="{{ url('/advertisements') }}" method="GET" class="row g-3 mb-4">
        <div class="col-md-10">
            <input type="text" name="search" class="form-control"
                   placeholder="Search by ad title or customer name..."
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <table class="table table-bordered mt-2">
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
                <th>Payment</th> {{-- ✅ NEW --}}
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

                    <td>{{ $ad->publish_date }}</td>

                    {{-- Ad Status --}}
                    <td>
                        {!! $ad->status == 1 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-danger">Inactive</span>' !!}
                    </td>

                    {{-- ✅ PAYMENT STATUS --}}
                    <td>
                        @if(is_null($ad->payment_status))
                            <span class="badge bg-secondary">No Payment</span>

                        @elseif($ad->payment_status == 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>

                        @elseif($ad->payment_status == 'completed' && $ad->is_success)
                            <span class="badge bg-success">Paid</span>

                        @else
                            <span class="badge bg-danger">Failed</span>
                        @endif
                    </td>

                    <td>
                        <a href="{{ url('/advertisements/' . $ad->id . '/view') }}"
                           class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">
                        No advertisements found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $ads->links() }}
    </div>

</div>
@endsection