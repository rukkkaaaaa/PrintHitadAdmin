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
    <div class="row mb-4">
        
        {{-- Add English Type Form --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Add Type (English)</strong>
                </div>

                <div class="card-body">
                    <form action="{{ url('/add-adtype') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Type Name (En)</label>
                            <input type="text" name="advertisement_type_en" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price (LKR)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select</option>
                                @foreach ($categories as $cat)
                                    @if($cat->category_name_en)
                                        <option value="{{ $cat->id }}">{{ $cat->category_name_en }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Type (English)</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add Sinhala Type Form --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Add Type (Sinhala)</strong>
                </div>

                <div class="card-body">
                    <form action="{{ url('/add-adtype') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Type Name (Si)</label>
                            <input type="text" name="advertisement_type_si" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price (LKR)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select</option>
                                @foreach ($categories as $cat)
                                    @if($cat->category_name_si)
                                        <option value="{{ $cat->id }}">{{ $cat->category_name_si }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Type (Sinhala)</button>
                    </form>
                </div>
            </div>
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

                            <td>{{ $type->category_name ?? 'N/A' }}</td>

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
                                <label class="form-label">Type Name (En)</label>
                                <input type="text"
                                       name="advertisement_type_en"
                                       class="form-control"
                                    value="{{ $type->advertisement_type_en }}">
                                <small class="text-muted">Either EN or SI required</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Type Name (Si)</label>
                                <input type="text"
                                       name="advertisement_type_si"
                                       class="form-control"
                                    value="{{ $type->advertisement_type_si }}">
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
                                    @php
                                        // If this ad type has only an English name, show only English categories.
                                        // If it has only a Sinhala name, show only Sinhala categories.
                                        // Otherwise show all categories (fall back).
                                        $showEnOnly = filled($type->advertisement_type_en) && !filled($type->advertisement_type_si);
                                        $showSiOnly = filled($type->advertisement_type_si) && !filled($type->advertisement_type_en);
                                    @endphp

                                    @foreach ($categories as $cat)
                                        @php
                                            $labelEn = $cat->category_name_en;
                                            $labelSi = $cat->category_name_si;
                                            // Decide whether this category should be listed for this edit modal
                                            $include = true;
                                            if ($showEnOnly) {
                                                $include = filled($labelEn);
                                            } elseif ($showSiOnly) {
                                                $include = filled($labelSi);
                                            }
                                            // Prefer the language label that makes sense for this modal
                                            $catLabel = $showSiOnly ? $labelSi : ($showEnOnly ? $labelEn : ($labelEn ?: $labelSi));
                                        @endphp

                                        @if($include && $catLabel)
                                            <option value="{{ $cat->id }}" {{ $type->category_id == $cat->id ? 'selected' : '' }}>
                                                {{ $catLabel }}
                                            </option>
                                        @endif
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('[id^="editAdType"]').forEach(function(modalEl){
        modalEl.addEventListener('shown.bs.modal', function(){
            var inputs = modalEl.querySelectorAll('input, textarea, select');
            try {
                var en = modalEl.querySelector('input[name="advertisement_type_en"]');
                var si = modalEl.querySelector('input[name="advertisement_type_si"]');

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
                console.error('error applying readonly rule', e);
            }

            console.group('Edit adtype diagnostics for ' + modalEl.id);
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
                var en = modalEl.querySelector('input[name="advertisement_type_en"]');
                var si = modalEl.querySelector('input[name="advertisement_type_si"]');
                if (en) { en.readOnly = false; en.classList.remove('bg-light'); }
                if (si) { si.readOnly = false; si.classList.remove('bg-light'); }
            } catch (e) { /* ignore */ }
        });
    });
});
</script>
@endpush