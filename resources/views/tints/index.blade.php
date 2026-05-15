@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4">Advertisement Tints</h2>

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
    <div class="row mb-4">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Add Tint (English)</strong>
                </div>

                <div class="card-body">
                    <form action="{{ url('/add-tint') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tint Name (En)</label>
                            <input type="text" name="advertisement_tint_en" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Color (hex)</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#ffffff">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price (LKR)</label>
                            <input type="number" step="0.01" name="price" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Add Tint (English)</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add Sinhala Tint Form --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Add Tint (Sinhala)</strong>
                </div>

                <div class="card-body">
                    <form action="{{ url('/add-tint') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tint Name (Si)</label>
                            <input type="text" name="advertisement_tint_si" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Color (hex)</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#ffffff">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price (LKR)</label>
                            <input type="number" step="0.01" name="price" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Add Tint (Sinhala)</button>
                    </form>
                </div>
            </div>
        </div>

    </div>


    {{-- Tints Table --}}
    <div class="card">

        <div class="card-header">
            <strong>Advertisement Tints List</strong>
        </div>

        <div class="card-body p-0">

            <table class="table table-bordered table-striped mb-0">

                <thead class="table-dark">
                    <tr>
                        <th width="60">ID</th>
                        <th>Tint (EN)</th>
                        <th>Tint (SI)</th>
                        <th>Color</th>
                        <th>Price</th>
                        <th width="120">Status</th>
                        <th width="180">Updated</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($tints as $tint)

                        <tr>

                            <td>{{ $tint->id }}</td>

                            <td>{{ $tint->advertisement_tint_en }}</td>

                            <td>{{ $tint->advertisement_tint_si }}</td>

                            <td>
                                <span style="display:inline-block;width:20px;height:20px;background:{{ $tint->color ?: '#ffffff' }};border:1px solid #ccc;vertical-align:middle;margin-right:8px"></span>
                                {{ $tint->color }}
                            </td>

                            <td>Rs. {{ number_format($tint->price ?? 0, 2) }}</td>

                            <td>
                                @if($tint->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>

                            <td>{{ \Carbon\Carbon::parse($tint->updated_at)->format('Y-m-d H:i') }}</td>

                            <td>
                                <button class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editTint{{ $tint->id }}">
                                    Edit
                                </button>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center">
                                No tints found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>


    {{-- Edit Modals --}}
    @foreach ($tints as $tint)

        <div class="modal fade" id="editTint{{ $tint->id }}" tabindex="-1">

            <div class="modal-dialog">

                <form action="{{ url('/update-tint/'.$tint->id) }}" method="POST">
                    @csrf

                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Advertisement Tint</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>


                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Tint Name (En)</label>
                                <input type="text"
                                       name="advertisement_tint_en"
                                       class="form-control"
                                    value="{{ $tint->advertisement_tint_en }}">
                                <small class="text-muted">Either EN or SI required</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tint Name (Si)</label>
                                <input type="text"
                                       name="advertisement_tint_si"
                                       class="form-control"
                                    value="{{ $tint->advertisement_tint_si }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Color (hex)</label>
                                <input type="color"
                                       name="color"
                                       value="{{ $tint->color ?: '#ffffff' }}"
                                       class="form-control form-control-color">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Price (LKR)</label>
                                <input type="number"
                                       name="price"
                                       step="0.01"
                                       value="{{ $tint->price ?? 0 }}"
                                       class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" {{ $tint->is_active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$tint->is_active ? 'selected' : '' }}>Inactive</option>
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
