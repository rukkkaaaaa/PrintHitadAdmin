@extends('layouts.app')

@section('content')

<div class="container mt-4">

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


    {{-- Add Forms --}}
    <div class="row mb-4 g-4">

        {{-- Add English District Form --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Add District (English)</strong>
                </div>

                <div class="card-body">
                    <form action="{{ url('/add-district') }}" method="POST">
                        @csrf

                        <div class="row">

                            <div class="col-12 mb-3">
                                <label>District Name (EN)</label>
                                <input type="text" name="district_name_en" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    Add District (English)
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add Sinhala District Form --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Add District (Sinhala)</strong>
                </div>

                <div class="card-body">
                    <form action="{{ url('/add-district') }}" method="POST">
                        @csrf

                        <div class="row">

                            <div class="col-12 mb-3">
                                <label>District Name (SI)</label>
                                <input type="text" name="district_name_si" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    Add District (Sinhala)
                                </button>
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
                                                  value="{{ $dist->district_name_en }}">
                                        </div>

                                        <div class="mb-3">
                                            <label>District Name (SI)</label>
                                            <input type="text"
                                                   name="district_name_si"
                                                   class="form-control"
                                                  value="{{ $dist->district_name_si }}">
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


</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('[id^="editDistrict"]').forEach(function(modalEl){
        modalEl.addEventListener('shown.bs.modal', function(){
            try {
                var en = modalEl.querySelector('input[name="district_name_en"]');
                var si = modalEl.querySelector('input[name="district_name_si"]');

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
                console.error('error applying readonly rule for district modal', e);
            }

            // Optional diagnostics
            var inputs = modalEl.querySelectorAll('input, textarea, select');
            console.group('Edit district diagnostics for ' + modalEl.id);
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
                var en = modalEl.querySelector('input[name="district_name_en"]');
                var si = modalEl.querySelector('input[name="district_name_si"]');
                if (en) { en.readOnly = false; en.classList.remove('bg-light'); }
                if (si) { si.readOnly = false; si.classList.remove('bg-light'); }
            } catch (e) { /* ignore */ }
        });
    });
});
</script>
@endpush
