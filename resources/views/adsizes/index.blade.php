@extends('layouts.app')

@section('content')

<div class="container mt-4">

    ```
    <h2 class="mb-4">Advertisement Sizes</h2>

    {{-- Success Message --}}
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Errors --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Add Form --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Add Advertisement Size</strong>
        </div>

        <div class="card-body">

            <form action="{{ url('/add-adsize') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Size Name (EN)</label>
                        <input type="text" name="advertisement_size_en" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Size Name (SI)</label>
                        <input type="text" name="advertisement_size_si" class="form-control" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Price (LKR)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Ad Type</label>
                        <select name="advertisement_type_id" class="form-control" required>
                            <option value="">Select</option>
                            @foreach ($adTypes as $type)
                            <option value="{{ $type->id }}">
                                {{ $type->advertisement_type_en }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="img_url" class="form-control">
                    </div>

                </div>

                <button type="submit" class="btn btn-primary">
                    Add Ad Size
                </button>

            </form>

        </div>
    </div>


    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <strong>Advertisement Sizes List</strong>
        </div>

        <div class="card-body p-0">

            <table class="table table-bordered table-striped mb-0">

                <thead class="table-dark">
                    <tr>
                        <th width="60">ID</th>
                        <th>Size (EN)</th>
                        <th>Size (SI)</th>
                        <th>Ad Type</th>
                        <th>Price</th>
                        <th>Image</th>
                        <th width="120">Status</th>
                        <th width="180">Updated</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($adSizes as $size)

                    <tr>

                        <td>{{ $size->id }}</td>

                        <td>{{ $size->advertisement_size_en }}</td>

                        <td>{{ $size->advertisement_size_si }}</td>

                        <td>{{ $size->type_name ?? 'N/A' }}</td>

                        <td>Rs. {{ number_format($size->price, 2) }}</td>

                        <td>
                            @if ($size->img_url)
                            <img src="{{ asset('storage/' . $size->img_url) }}" width="80">
                            @else
                            -
                            @endif
                        </td>

                        <td>
                            @if($size->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>{{ \Carbon\Carbon::parse($size->updated_at)->format('Y-m-d H:i') }}</td>

                        <td>
                            <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#editSize{{ $size->id }}">
                                Edit
                            </button>
                        </td>

                    </tr>


                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editSize{{ $size->id }}" tabindex="-1">

                        <div class="modal-dialog">

                            <form action="{{ url('/update-adsize/' . $size->id) }}"
                                method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Size</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label>Size Name (EN)</label>
                                            <input type="text"
                                                name="advertisement_size_en"
                                                class="form-control"
                                                value="{{ $size->advertisement_size_en }}"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label>Size Name (SI)</label>
                                            <input type="text"
                                                name="advertisement_size_si"
                                                class="form-control"
                                                value="{{ $size->advertisement_size_si }}"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label>Price</label>
                                            <input type="number"
                                                step="0.01"
                                                name="price"
                                                class="form-control"
                                                value="{{ $size->price }}"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label>Ad Type</label>
                                            <select name="advertisement_type_id" class="form-control">

                                                @foreach ($adTypes as $type)
                                                <option value="{{ $type->id }}"
                                                    {{ $type->id == $size->advertisement_type_id ? 'selected' : '' }}>
                                                    {{ $type->advertisement_type_en }}
                                                </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Status</label>
                                            <select name="is_active" class="form-control">
                                                <option value="1" {{ $size->is_active ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ !$size->is_active ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Image (optional)</label>
                                            <input type="file" name="img_url" class="form-control">

                                            @if($size->img_url)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $size->img_url) }}" width="100">
                                            </div>
                                            @endif
                                        </div>

                                    </div>

                                    <div class="modal-footer">

                                        <button type="button"
                                            class="btn btn-secondary"
                                            data-bs-dismiss="modal">
                                            Cancel
                                        </button>

                                        <button type="submit"
                                            class="btn btn-primary">
                                            Save Changes
                                        </button>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                    @empty

                    <tr>
                        <td colspan="9" class="text-center">
                            No advertisement sizes found.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>
    ```

</div>
@endsection