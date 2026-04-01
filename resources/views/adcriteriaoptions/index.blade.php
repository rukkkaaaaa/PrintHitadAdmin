@extends('layouts.app')

@section('content')

<div class="container mt-4">

    ```
    <h2 class="mb-4">Advertisement Criteria Options</h2>

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
            <strong>Add Option</strong>
        </div>

        <div class="card-body">

            <form action="{{ url('/add-adcriteria-option') }}" method="POST">
                @csrf

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Option Name (EN)</label>
                        <input type="text" name="advertisement_criteria_option_name_en" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Option Name (SI)</label>
                        <input type="text" name="advertisement_criteria_option_name_si" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Criteria</label>
                        <select name="advertisement_criteria_id" class="form-control" required>
                            <option value="">Select</option>
                            @foreach ($criterias as $crit)
                            <option value="{{ $crit->id }}">
                                {{ $crit->advertisement_criteria_name_en }} ({{ $crit->category_name }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1 mt-4">
                        <button type="submit" class="btn btn-primary mt-2">
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
            <strong>Options List</strong>
        </div>

        <div class="card-body p-0">

            <table class="table table-bordered table-striped mb-0">

                <thead class="table-dark">
                    <tr>
                        <th width="60">ID</th>
                        <th>Name (EN)</th>
                        <th>Name (SI)</th>
                        <th>Criteria</th>
                        <th width="120">Status</th>
                        <th width="180">Updated</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($options as $opt)

                    @php
                    $crit = $criterias->firstWhere('id', $opt->advertisement_criteria_id);
                    @endphp

                    <tr>

                        <td>{{ $opt->id }}</td>

                        <td>{{ $opt->advertisement_criteria_option_name_en }}</td>

                        <td>{{ $opt->advertisement_criteria_option_name_si }}</td>

                        <td>
                            {{ $crit->advertisement_criteria_name_en ?? 'N/A' }}
                            @if($crit?->category_name)
                            ({{ $crit->category_name }})
                            @endif
                        </td>

                        <td>
                            @if($opt->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>{{ \Carbon\Carbon::parse($opt->updated_at)->format('Y-m-d H:i') }}</td>

                        <td>
                            <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#editOption{{ $opt->id }}">
                                Edit
                            </button>
                        </td>

                    </tr>


                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editOption{{ $opt->id }}" tabindex="-1">

                        <div class="modal-dialog">

                            <form action="{{ url('/update-adcriteria-option/' . $opt->id) }}" method="POST">
                                @csrf

                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Option</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label>Option Name (EN)</label>
                                            <input type="text"
                                                name="advertisement_criteria_option_name_en"
                                                class="form-control"
                                                value="{{ $opt->advertisement_criteria_option_name_en }}"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label>Option Name (SI)</label>
                                            <input type="text"
                                                name="advertisement_criteria_option_name_si"
                                                class="form-control"
                                                value="{{ $opt->advertisement_criteria_option_name_si }}"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label>Criteria</label>
                                            <select name="advertisement_criteria_id" class="form-control">
                                                @foreach ($criterias as $crit)
                                                <option value="{{ $crit->id }}"
                                                    {{ $opt->advertisement_criteria_id == $crit->id ? 'selected' : '' }}>
                                                    {{ $crit->advertisement_criteria_name_en }} ({{ $crit->category_name }})
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Status</label>
                                            <select name="is_active" class="form-control">
                                                <option value="1" {{ $opt->is_active ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ !$opt->is_active ? 'selected' : '' }}>Inactive</option>
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
                        <td colspan="7" class="text-center">
                            No options found.
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