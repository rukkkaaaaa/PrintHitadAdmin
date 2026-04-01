@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4">Advertisement Types</h2>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Validation Errors --}}
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
            <strong>Add Advertisement Type</strong>
        </div>

        <div class="card-body">

            <form action="{{ url('/add-adtype') }}" method="POST">
                @csrf

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Type Name (English)</label>
                        <input type="text" name="advertisement_type_en" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Type Name (Sinhala)</label>
                        <input type="text" name="advertisement_type_si" class="form-control" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Price (LKR)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->category_name_en }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <button type="submit" class="btn btn-primary">
                    Add Ad Type
                </button>

            </form>

        </div>
    </div>


    {{-- Ad Types Table --}}
    <div class="card">

        <div class="card-header">
            <strong>Advertisement Types List</strong>
        </div>

        <div class="card-body p-0">

            <table class="table table-bordered table-striped mb-0">

                <thead class="table-dark">
                    <tr>
                        <th width="60">ID</th>
                        <th>Type (EN)</th>
                        <th>Type (SI)</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th width="120">Status</th>
                        <th width="180">Updated</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($adtypes as $type)

                        <tr>

                            <td>{{ $type->id }}</td>

                            <td>{{ $type->advertisement_type_en }}</td>

                            <td>{{ $type->advertisement_type_si }}</td>

                            <td>
                                {{
                                    optional($categories->where('id', $type->category_id)->first())->category_name_en
                                    ?? 'N/A'
                                }}
                            </td>

                            <td>Rs. {{ number_format($type->price, 2) }}</td>

                            <td>
                                @if($type->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>

                            <td>{{ \Carbon\Carbon::parse($type->updated_at)->format('Y-m-d H:i') }}</td>

                            <td>
                                <button class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editAdType{{ $type->id }}">
                                    Edit
                                </button>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center">
                                No advertisement types found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>


    {{-- Edit Modals --}}
    @foreach ($adtypes as $type)

        <div class="modal fade" id="editAdType{{ $type->id }}" tabindex="-1">

            <div class="modal-dialog">

                <form action="{{ url('/update-adtype/'.$type->id) }}" method="POST">
                    @csrf

                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Advertisement Type</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>


                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Type Name (English)</label>
                                <input type="text"
                                       name="advertisement_type_en"
                                       class="form-control"
                                       value="{{ $type->advertisement_type_en }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Type Name (Sinhala)</label>
                                <input type="text"
                                       name="advertisement_type_si"
                                       class="form-control"
                                       value="{{ $type->advertisement_type_si }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Price (LKR)</label>
                                <input type="number"
                                       name="price"
                                       step="0.01"
                                       value="{{ $type->price }}"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-control" required>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ $type->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->category_name_en }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" {{ $type->is_active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$type->is_active ? 'selected' : '' }}>Inactive</option>
                                </select>
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

    @endforeach

</div>
@endsection