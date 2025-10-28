@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Advertisement Types</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Add Form -->
    <form action="{{ url('/add-adtype') }}" method="POST" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <label>Type Name</label>
                <input type="text" name="advertisement_type" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Price (LKR)</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Category</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mt-4">
                <button type="submit" class="btn btn-primary mt-2">Add Ad Type</button>
            </div>
        </div>
    </form>

    <!-- Ad Types Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($adtypes as $type)
                <tr>
                    <td>{{ $type->id }}</td>
                    <td>{{ $type->advertisement_type }}</td>
                    <td>{{ $categories->where('id', $type->category_id)->first()->category_name ?? 'N/A' }}</td>
                    <td>Rs. {{ number_format($type->price, 2) }}</td>
                    <td>{!! $type->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</td>
                    <td>{{ $type->updated_at }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editAdType{{ $type->id }}">Edit</button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editAdType{{ $type->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $type->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ url('/update-adtype/'.$type->id) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Ad Type</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label>Type Name</label>
                                        <input type="text" name="advertisement_type" class="form-control" value="{{ $type->advertisement_type }}" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Price (LKR)</label>
                                        <input type="number" name="price" step="0.01" value="{{ $type->price }}" class="form-control" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Category</label>
                                        <select name="category_id" class="form-control" required>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $type->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Status</label>
                                        <select name="is_active" class="form-control">
                                            <option value="1" {{ $type->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$type->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
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
