@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Advertisement Criteria Options</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Add Option Form -->
    <form action="{{ url('/add-adcriteria-option') }}" method="POST" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <label>Option Name</label>
                <input type="text" name="advertisement_criteria_option_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Criteria</label>
                <select name="advertisement_criteria_id" class="form-control" required>
                    <option value="">Select</option>
                    @foreach ($criterias as $crit)
                        <option value="{{ $crit->id }}">
                            {{ $crit->advertisement_criteria_name }} ({{ $crit->category_name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mt-4">
                <button type="submit" class="btn btn-primary mt-2">Add Option</button>
            </div>
        </div>
    </form>

    <!-- Table -->
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>ID</th>
                <th>Option Name</th>
                <th>Criteria</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($options as $opt)
                @php
                    $crit = $criterias->firstWhere('id', $opt->advertisement_criteria_id);
                @endphp
                <tr>
                    <td>{{ $opt->id }}</td>
                    <td>{{ $opt->advertisement_criteria_option_name }}</td>
                    <td>
                        {{ $crit->advertisement_criteria_name ?? 'N/A' }}
                        @if($crit?->category_name)
                            ({{ $crit->category_name }})
                        @endif
                    </td>
                    <td>{!! $opt->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</td>
                    <td>{{ $opt->updated_at }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editOption{{ $opt->id }}">Edit</button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editOption{{ $opt->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ url('/update-adcriteria-option/' . $opt->id) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5>Edit Option</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label>Option Name</label>
                                        <input type="text" name="advertisement_criteria_option_name" class="form-control" value="{{ $opt->advertisement_criteria_option_name }}" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Criteria</label>
                                        <select name="advertisement_criteria_id" class="form-control">
                                            @foreach ($criterias as $crit)
                                                <option value="{{ $crit->id }}" {{ $opt->advertisement_criteria_id == $crit->id ? 'selected' : '' }}>
                                                    {{ $crit->advertisement_criteria_name }} ({{ $crit->category_name }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Status</label>
                                        <select name="is_active" class="form-control">
                                            <option value="1" {{ $opt->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$opt->is_active ? 'selected' : '' }}>Inactive</option>
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
