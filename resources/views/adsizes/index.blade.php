@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Advertisement Sizes</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Add Form -->
    <form action="{{ url('/add-adsize') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <label>Size Name</label>
                <input type="text" name="advertisement_size" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Price (LKR)</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Ad Type</label>
                <select name="advertisement_type_id" class="form-control" required>
                    <option value="">Select</option>
                    @foreach ($adTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->advertisement_type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Image (optional)</label>
                <input type="file" name="img_url" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add Ad Size</button>
    </form>

    <!-- Table -->
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>ID</th>
                <th>Size</th>
                <th>Ad Type</th>
                <th>Price</th>
                <th>Image</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($adSizes as $size)
                <tr>
                    <td>{{ $size->id }}</td>
                    <td>{{ $size->advertisement_size }}</td>
                    <td>{{ $adTypes->where('id', $size->advertisement_type_id)->first()->advertisement_type ?? 'N/A' }}</td>
                    <td>Rs. {{ number_format($size->price, 2) }}</td>
                    <td>
                        @if ($size->img_url)
                            <img src="{{ asset('storage/' . $size->img_url) }}" width="80" />
                        @else
                            -
                        @endif
                    </td>
                    <td>{!! $size->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</td>
                    <td>{{ $size->updated_at }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editSize{{ $size->id }}">Edit</button>
                    </td>
                </tr>

                <!-- Modal -->
                <div class="modal fade" id="editSize{{ $size->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ url('/update-adsize/' . $size->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5>Edit Size</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label>Size Name</label>
                                        <input type="text" name="advertisement_size" class="form-control" value="{{ $size->advertisement_size }}" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Price</label>
                                        <input type="number" step="0.01" name="price" class="form-control" value="{{ $size->price }}" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Ad Type</label>
                                        <select name="advertisement_type_id" class="form-control">
                                            @foreach ($adTypes as $type)
                                                <option value="{{ $type->id }}" {{ $type->id == $size->advertisement_type_id ? 'selected' : '' }}>
                                                    {{ $type->advertisement_type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Status</label>
                                        <select name="is_active" class="form-control">
                                            <option value="1" {{ $size->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$size->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Image (optional)</label>
                                        <input type="file" name="img_url" class="form-control">
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
