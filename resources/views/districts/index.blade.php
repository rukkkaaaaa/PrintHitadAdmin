@extends('layouts.app')

@section('content')

<div class="container mt-4">

```
<h2 class="mb-4">Districts</h2>

{{-- Success --}}
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
        <strong>Add District</strong>
    </div>

    <div class="card-body">
        <form action="{{ url('/add-district') }}" method="POST">
            @csrf

            <div class="row">

                <div class="col-md-5">
                    <label>District Name (EN)</label>
                    <input type="text" name="district_name_en" class="form-control" required>
                </div>

                <div class="col-md-5">
                    <label>District Name (SI)</label>
                    <input type="text" name="district_name_si" class="form-control" required>
                </div>

                <div class="col-md-2 mt-4">
                    <button type="submit" class="btn btn-primary mt-2 w-100">
                        Add
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>


{{-- Table --}}
<div class="card">
    <div class="card-header">
        <strong>District List</strong>
    </div>

    <div class="card-body p-0">

        <table class="table table-bordered table-striped mb-0">

            <thead class="table-dark">
                <tr>
                    <th width="60">ID</th>
                    <th>District (EN)</th>
                    <th>District (SI)</th>
                    <th width="120">Status</th>
                    <th width="180">Updated</th>
                    <th width="120">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($districts as $dist)

                    <tr>

                        <td>{{ $dist->id }}</td>

                        <td>{{ $dist->district_name_en }}</td>

                        <td>{{ $dist->district_name_si }}</td>

                        <td>
                            @if($dist->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>{{ \Carbon\Carbon::parse($dist->updated_at)->format('Y-m-d H:i') }}</td>

                        <td>
                            <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editDistrict{{ $dist->id }}">
                                Edit
                            </button>
                        </td>

                    </tr>


                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editDistrict{{ $dist->id }}" tabindex="-1">

                        <div class="modal-dialog">

                            <form action="{{ url('/update-district/' . $dist->id) }}" method="POST">
                                @csrf

                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit District</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label>District Name (EN)</label>
                                            <input type="text"
                                                   name="district_name_en"
                                                   class="form-control"
                                                   value="{{ $dist->district_name_en }}"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label>District Name (SI)</label>
                                            <input type="text"
                                                   name="district_name_si"
                                                   class="form-control"
                                                   value="{{ $dist->district_name_si }}"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label>Status</label>
                                            <select name="is_active" class="form-control">
                                                <option value="1" {{ $dist->is_active ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ !$dist->is_active ? 'selected' : '' }}>Inactive</option>
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

                @empty

                    <tr>
                        <td colspan="6" class="text-center">
                            No districts found.
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
