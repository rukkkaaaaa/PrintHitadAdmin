@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Cities</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Add City Form -->
    <form action="{{ url('/add-city') }}" method="POST" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-5">
                <label>City Name</label>
                <input type="text" name="city_name" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label>District</label>
                <select name="district_id" class="form-control" required>
                    <option value="">Select</option>
                    @foreach ($districts as $dist)
                        <option value="{{ $dist->id }}">{{ $dist->district_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mt-4">
                <button type="submit" class="btn btn-primary mt-2">Add City</button>
            </div>
        </div>
    </form>

    <!-- Cities Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>City</th>
                <th>District</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cities as $city)
                @php
                    $district = $districts->firstWhere('id', $city->district_id);
                @endphp
                <tr>
                    <td>{{ $city->id }}</td>
                    <td>{{ $city->city_name }}</td>
                    <td>{{ $district->district_name ?? 'N/A' }}</td>
                    <td>
                        {!! $city->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}
                    </td>
                    <td>{{ $city->updated_at }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCity{{ $city->id }}">Edit</button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editCity{{ $city->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ url('/update-city/' . $city->id) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5>Edit City</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label>City Name</label>
                                        <input type="text" name="city_name" class="form-control" value="{{ $city->city_name }}" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>District</label>
                                        <select name="district_id" class="form-control" required>
                                            @foreach ($districts as $dist)
                                                <option value="{{ $dist->id }}" {{ $city->district_id == $dist->id ? 'selected' : '' }}>
                                                    {{ $dist->district_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Status</label>
                                        <select name="is_active" class="form-control">
                                            <option value="1" {{ $city->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$city->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            @endforeach
        </tbody>
    </table>
</div>
@endsection
