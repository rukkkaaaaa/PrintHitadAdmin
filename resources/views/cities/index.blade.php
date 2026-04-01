@extends('layouts.app')

@section('content')

<div class="container mt-4">

```
<h2 class="mb-4">Cities</h2>

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
        <strong>Add City</strong>
    </div>

    <div class="card-body">
        <form action="{{ url('/add-city') }}" method="POST">
            @csrf

            <div class="row">

                <div class="col-md-4">
                    <label>City Name (EN)</label>
                    <input type="text" name="city_name_en" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label>City Name (SI)</label>
                    <input type="text" name="city_name_si" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label>District</label>
                    <select name="district_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach ($districts as $dist)
                            <option value="{{ $dist->id }}">
                                {{ $dist->district_name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1 mt-4">
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
        <strong>City List</strong>
    </div>

    <div class="card-body p-0">

        <table class="table table-bordered table-striped mb-0">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>City (EN)</th>
                    <th>City (SI)</th>
                    <th>District</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($cities as $city)

                    @php
                        $district = $districts->firstWhere('id', $city->district_id);
                    @endphp

                    <tr>

                        <td>{{ $city->id }}</td>

                        <td>{{ $city->city_name_en }}</td>

                        <td>{{ $city->city_name_si }}</td>

                        <td>{{ $district->district_name_en ?? 'N/A' }}</td>

                        <td>
                            @if($city->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>{{ \Carbon\Carbon::parse($city->updated_at)->format('Y-m-d H:i') }}</td>

                        <td>
                            <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCity{{ $city->id }}">
                                Edit
                            </button>
                        </td>

                    </tr>


                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editCity{{ $city->id }}" tabindex="-1">

                        <div class="modal-dialog">

                            <form action="{{ url('/update-city/' . $city->id) }}" method="POST">
                                @csrf

                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit City</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label>City Name (EN)</label>
                                            <input type="text"
                                                   name="city_name_en"
                                                   class="form-control"
                                                   value="{{ $city->city_name_en }}"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label>City Name (SI)</label>
                                            <input type="text"
                                                   name="city_name_si"
                                                   class="form-control"
                                                   value="{{ $city->city_name_si }}"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label>District</label>
                                            <select name="district_id" class="form-control">
                                                @foreach ($districts as $dist)
                                                    <option value="{{ $dist->id }}"
                                                        {{ $city->district_id == $dist->id ? 'selected' : '' }}>
                                                        {{ $dist->district_name_en }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Status</label>
                                            <select name="is_active" class="form-control">
                                                <option value="1" {{ $city->is_active ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ !$city->is_active ? 'selected' : '' }}>Inactive</option>
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
                                            Save
                                        </button>
                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            No cities found.
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
