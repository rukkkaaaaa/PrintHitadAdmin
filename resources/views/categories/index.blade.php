@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2 class="mb-4">Categories</h2>

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


    {{-- Add Category Forms --}}
    <div class="row mb-4 g-4">

        {{-- Add English Category Form --}}
        <div class="col-md-6">
            <div class="card h-100">

                <div class="card-header">
                    <strong>Add Category (English)</strong>
                </div>

                <div class="card-body">

                    <form action="{{ url('/add-category') }}" method="POST">
                        @csrf

                        <div class="row">

                            <div class="col-12 mb-3">
                                <label class="form-label">Category Name (En)</label>
                                <input type="text"
                                    name="category_name_en"
                                    class="form-control"
                                    placeholder="Enter English name"
                                    required>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    Add Category (English)
                                </button>
                            </div>

                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- Add Sinhala Category Form --}}
        <div class="col-md-6">
            <div class="card h-100">

                <div class="card-header">
                    <strong>Add Category (Sinhala)</strong>
                </div>

                <div class="card-body">

                    <form action="{{ url('/add-category') }}" method="POST">
                        @csrf

                        <div class="row">

                            <div class="col-12 mb-3">
                                <label class="form-label">Category Name (Si)</label>
                                <input type="text"
                                    name="category_name_si"
                                    class="form-control"
                                    placeholder="Enter Sinhala name"
                                    required>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    Add Category (Sinhala)
                                </button>
                            </div>

                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>


    {{-- Categories Table --}}
    <div class="card">

        <div class="card-header">
            <strong>Category List</strong>
        </div>

        <div class="card-body p-0">

            <table class="table table-bordered table-striped mb-0">

                <thead class="table-dark">
                    <tr>
                        <th width="60">ID</th>
                        <th>English Name</th>
                        <th>Sinhala Name</th>
                        <th width="120">Status</th>
                        <th width="180">Created</th>
                        <th width="180">Updated</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($categories as $cat)

                    <tr>

                        <td>{{ $cat->id }}</td>

                        <td>{{ $cat->category_name_en }}</td>

                        <td>{{ $cat->category_name_si }}</td>

                        <td>
                            @if($cat->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>{{ \Carbon\Carbon::parse($cat->created_at)->format('Y-m-d H:i') }}</td>

                        <td>{{ \Carbon\Carbon::parse($cat->updated_at)->format('Y-m-d H:i') }}</td>

                        <td>

                            <button
                                class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal{{ $cat->id }}">
                                Edit
                            </button>

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            No categories found.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>


    {{-- Edit Modals --}}
    @foreach ($categories as $cat)

    <div class="modal fade"
        id="editModal{{ $cat->id }}"
        tabindex="-1">

        <div class="modal-dialog">

            <form action="{{ url('/update-category/'.$cat->id) }}" method="POST">
                @csrf

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Edit Category
                        </h5>

                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>

                    </div>


                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Category Name (English)
                            </label>

                            <input
                                type="text"
                                name="category_name_en"
                                class="form-control"
                                value="{{ $cat->category_name_en }}"
                                >
                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Category Name (Sinhala)
                            </label>

                            <input
                                type="text"
                                name="category_name_si"
                                class="form-control"
                                value="{{ $cat->category_name_si }}"
                                >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="is_active"
                                class="form-control">

                                <option value="1"
                                    {{ $cat->is_active ? 'selected' : '' }}>
                                    Active
                                </option>

                                <option value="0"
                                    {{ !$cat->is_active ? 'selected' : '' }}>
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button
                            type="submit"
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Diagnostic + behavior: when an edit modal is shown, log each input's state and make the empty language field readonly
    document.querySelectorAll('[id^="editModal"]').forEach(function(modalEl){
        modalEl.addEventListener('shown.bs.modal', function(){
            var inputs = modalEl.querySelectorAll('input, textarea, select');
            // Make language input readonly when it's empty and the other language has a value.
            try {
                var en = modalEl.querySelector('input[name="category_name_en"]');
                var si = modalEl.querySelector('input[name="category_name_si"]');

                if (en && si) {
                    var enVal = (en.value || '').toString().trim();
                    var siVal = (si.value || '').toString().trim();

                    if (enVal && !siVal) {
                        // English exists, Sinhala empty -> make Sinhala readonly
                        si.readOnly = true;
                        si.classList.add('bg-light');
                    } else if (siVal && !enVal) {
                        // Sinhala exists, English empty -> make English readonly
                        en.readOnly = true;
                        en.classList.add('bg-light');
                    } else {
                        // both present or both empty -> allow editing both
                        if (en) en.readOnly = false;
                        if (si) si.readOnly = false;
                        en && en.classList.remove('bg-light');
                        si && si.classList.remove('bg-light');
                    }
                }
            } catch (e) {
                console.error('error applying readonly rule', e);
            }
            console.group('Edit modal diagnostics for ' + modalEl.id);
            inputs.forEach(function(inp, idx){
                try {
                    var cs = window.getComputedStyle(inp);
                    console.log(idx, inp.name || inp.id || inp.tagName, {
                        disabled: inp.disabled,
                        readOnly: inp.readOnly,
                        value: inp.value,
                        tabIndex: inp.tabIndex,
                        display: cs.display,
                        visibility: cs.visibility,
                        pointerEvents: cs.pointerEvents,
                        opacity: cs.opacity
                    });

                    var rect = inp.getBoundingClientRect();
                    var x = Math.round(rect.left + rect.width/2);
                    var y = Math.round(rect.top + rect.height/2);
                    var topEl = document.elementFromPoint(x, y);
                    console.log(' elementFromPoint center ->', topEl, topEl && topEl.className, topEl && topEl.id);
                } catch (e) {
                    console.error('diagnostic error for input', inp, e);
                }
            });
            console.groupEnd();
        });
        modalEl.addEventListener('hidden.bs.modal', function(){
            // cleanup: remove readonly flags when modal closes
            try {
                var en = modalEl.querySelector('input[name="category_name_en"]');
                var si = modalEl.querySelector('input[name="category_name_si"]');
                if (en) { en.readOnly = false; en.classList.remove('bg-light'); }
                if (si) { si.readOnly = false; si.classList.remove('bg-light'); }
            } catch (e) { /* ignore */ }
        });
    });
});
</script>
@endpush