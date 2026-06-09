@extends('layouts.app')

@section('content')

<div class="container mt-4">


<h2 class="mb-4">Advertisement Criterias</h2>

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


{{-- Add Forms --}}
<div class="row mb-4 g-4">

    {{-- Add English Criteria Form --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <strong>Add Criteria (English)</strong>
            </div>

            <div class="card-body">

                <form action="{{ url('/add-adcriteria') }}" method="POST">
                    @csrf

                    <div class="row">

                        <div class="col-12 mb-3">
                            <label class="form-label">Criteria Name (EN)</label>
                            <input type="text" name="advertisement_criteria_name_en" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Type</label>
                            <select name="field_type" class="form-control" required>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="dropdown">Dropdown</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select</option>
                                @foreach ($categoriesEn as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_name_en }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Add Criteria (English)</button>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- Add Sinhala Criteria Form --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <strong>Add Criteria (Sinhala)</strong>
            </div>

            <div class="card-body">

                <form action="{{ url('/add-adcriteria') }}" method="POST">
                    @csrf

                    <div class="row">

                        <div class="col-12 mb-3">
                            <label class="form-label">Criteria Name (SI)</label>
                            <input type="text" name="advertisement_criteria_name_si" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Type</label>
                            <select name="field_type" class="form-control" required>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="dropdown">Dropdown</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select</option>
                                @foreach ($categoriesSi as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_name_si }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Add Criteria (Sinhala)</button>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>

</div>


{{-- Table --}}
<div class="card">
    <div class="card-header">
        <strong>Criteria List</strong>
    </div>

    <div class="card-body p-0">

        <table class="table table-bordered table-striped mb-0">

            <thead class="table-dark">
                <tr>
                    <th width="60">ID</th>
                    <th>Name (EN)</th>
                    <th>Name (SI)</th>
                    <th>Field Type</th>
                    <th>Category</th>
                    <th width="120">Status</th>
                    <th width="180">Updated</th>
                    <th width="120">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($criterias as $crit)

                    <tr>

                        <td>{{ $crit->id }}</td>

                        <td>{{ $crit->advertisement_criteria_name_en }}</td>

                        <td>{{ $crit->advertisement_criteria_name_si }}</td>

                        <td>{{ ucfirst($crit->field_type) }}</td>

                        <td>{{ $crit->category_name ?? 'N/A' }}</td>

                        <td>
                            @if($crit->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>{{ \Carbon\Carbon::parse($crit->updated_at)->format('Y-m-d H:i') }}</td>

                        <td>
                            <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCrit{{ $crit->id }}">
                                Edit
                            </button>
                        </td>

                    </tr>


                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editCrit{{ $crit->id }}" tabindex="-1">

                        <div class="modal-dialog">

                            <form action="{{ url('/update-adcriteria/' . $crit->id) }}" method="POST">
                                @csrf

                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Criteria</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label>Criteria Name (EN)</label>
                                            <input type="text"
                                                   name="advertisement_criteria_name_en"
                                                   class="form-control"
                                                   value="{{ $crit->advertisement_criteria_name_en }}">
                                            <small class="text-muted">Either EN or SI required</small>
                                        </div>

                                        <div class="mb-3">
                                            <label>Criteria Name (SI)</label>
                                            <input type="text"
                                                   name="advertisement_criteria_name_si"
                                                   class="form-control"
                                                   value="{{ $crit->advertisement_criteria_name_si }}">
                                        </div>

                                        <div class="mb-3">
                                            <label>Field Type</label>
                                            <select name="field_type" class="form-control">
                                                <option value="text" {{ $crit->field_type == 'text' ? 'selected' : '' }}>Text</option>
                                                <option value="number" {{ $crit->field_type == 'number' ? 'selected' : '' }}>Number</option>
                                                <option value="dropdown" {{ $crit->field_type == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Category</label>
                                            <select name="category_id" class="form-control">
                                                @php
                                                    $showEnOnly = filled($crit->advertisement_criteria_name_en) && !filled($crit->advertisement_criteria_name_si);
                                                    $showSiOnly = filled($crit->advertisement_criteria_name_si) && !filled($crit->advertisement_criteria_name_en);
                                                @endphp

                                                @if($showSiOnly)
                                                    @foreach($categoriesSi as $cat)
                                                        <option value="{{ $cat->id }}" {{ $crit->category_id == $cat->id ? 'selected' : '' }}>
                                                            {{ $cat->category_name_si }}
                                                        </option>
                                                    @endforeach
                                                @elseif($showEnOnly)
                                                    @foreach($categoriesEn as $cat)
                                                        <option value="{{ $cat->id }}" {{ $crit->category_id == $cat->id ? 'selected' : '' }}>
                                                            {{ $cat->category_name_en }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach ($categories as $cat)
                                                        @php
                                                            $catLabel = $cat->category_name_en ?: $cat->category_name_si;
                                                        @endphp
                                                        @if($catLabel)
                                                            <option value="{{ $cat->id }}" {{ $crit->category_id == $cat->id ? 'selected' : '' }}>
                                                                {{ $catLabel }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Status</label>
                                            <select name="is_active" class="form-control">
                                                <option value="1" {{ $crit->is_active ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ !$crit->is_active ? 'selected' : '' }}>Inactive</option>
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
                        <td colspan="8" class="text-center">
                            No criterias found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>
</div>


</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('[id^="editCrit"]').forEach(function(modalEl){
        modalEl.addEventListener('shown.bs.modal', function(){
            try {
                var en = modalEl.querySelector('input[name="advertisement_criteria_name_en"]');
                var si = modalEl.querySelector('input[name="advertisement_criteria_name_si"]');

                if (en && si) {
                    var enVal = (en.value || '').toString().trim();
                    var siVal = (si.value || '').toString().trim();

                    if (enVal && !siVal) {
                        si.readOnly = true;
                        si.classList.add('bg-light');
                    } else if (siVal && !enVal) {
                        en.readOnly = true;
                        en.classList.add('bg-light');
                    } else {
                        if (en) en.readOnly = false;
                        if (si) si.readOnly = false;
                        en && en.classList.remove('bg-light');
                        si && si.classList.remove('bg-light');
                    }
                }
            } catch (e) {
                console.error('error applying readonly rule for criteria modal', e);
            }

            // Optional diagnostics
            var inputs = modalEl.querySelectorAll('input, textarea, select');
            console.group('Edit criteria diagnostics for ' + modalEl.id);
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
            try {
                var en = modalEl.querySelector('input[name="advertisement_criteria_name_en"]');
                var si = modalEl.querySelector('input[name="advertisement_criteria_name_si"]');
                if (en) { en.readOnly = false; en.classList.remove('bg-light'); }
                if (si) { si.readOnly = false; si.classList.remove('bg-light'); }
            } catch (e) { /* ignore */ }
        });
    });
});
</script>
@endpush
