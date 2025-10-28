@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Advertisement Criterias</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Add Criteria Form -->
    <form action="{{ url('/add-adcriteria') }}" method="POST" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <label>Criteria Name</label>
                <input type="text" name="advertisement_criteria_name" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Field Type</label>
                <select name="field_type" class="form-control" required>
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="dropdown">Dropdown</option>
                </select>
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
                <button type="submit" class="btn btn-primary mt-2">Add Criteria</button>
            </div>
        </div>
    </form>

    <!-- Criteria Table -->
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>ID</th>
                <th>Criteria Name</th>
                <th>Field Type</th>
                <th>Category</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($criterias as $crit)
                <tr>
                    <td>{{ $crit->id }}</td>
                    <td>{{ $crit->advertisement_criteria_name }}</td>
                    <td>{{ ucfirst($crit->field_type) }}</td>
                    <td>{{ $categories->where('id', $crit->category_id)->first()->category_name ?? 'N/A' }}</td>
                    <td>{!! $crit->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</td>
                    <td>{{ $crit->updated_at }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCrit{{ $crit->id }}">Edit</button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editCrit{{ $crit->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ url('/update-adcriteria/' . $crit->id) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5>Edit Criteria</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label>Criteria Name</label>
                                        <input type="text" name="advertisement_criteria_name" class="form-control" value="{{ $crit->advertisement_criteria_name }}" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Field Type</label>
                                        <select name="field_type" class="form-control">
                                            <option value="text" {{ $crit->field_type == 'text' ? 'selected' : '' }}>Text</option>
                                            <option value="number" {{ $crit->field_type == 'number' ? 'selected' : '' }}>Number</option>
                                            <option value="dropdown" {{ $crit->field_type == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Category</label>
                                        <select name="category_id" class="form-control">
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $crit->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Status</label>
                                        <select name="is_active" class="form-control">
                                            <option value="1" {{ $crit->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$crit->is_active ? 'selected' : '' }}>Inactive</option>
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
