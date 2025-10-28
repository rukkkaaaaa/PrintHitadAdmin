@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Districts</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Add Form -->
    <form action="{{ url('/add-district') }}" method="POST" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-10">
                <label>District Name</label>
                <input type="text" name="district_name" class="form-control" required>
            </div>
            <div class="col-md-2 mt-4">
                <button type="submit" class="btn btn-primary mt-2">Add District</button>
            </div>
        </div>
    </form>

    <!-- Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>District Name</th>
                <th>Status</th>
                <th>Updated At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($districts as $dist)
                <tr>
                    <td>{{ $dist->id }}</td>
                    <td>{{ $dist->district_name }}</td>
                    <td>
                        {!! $dist->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}
                    </td>
                    <td>{{ $dist->updated_at }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editDistrict{{ $dist->id }}">Edit</button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editDistrict{{ $dist->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ url('/update-district/' . $dist->id) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5>Edit District</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label>District Name</label>
                                        <input type="text" name="district_name" class="form-control" value="{{ $dist->district_name }}" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Status</label>
                                        <select name="is_active" class="form-control">
                                            <option value="1" {{ $dist->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$dist->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
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
