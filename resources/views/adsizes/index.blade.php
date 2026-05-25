@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2 class="mb-4">Advertisement Sizes</h2>

    {{-- Success Message --}}
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

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Add Size (English)</strong>
                </div>

                <div class="card-body">

                    <form action="{{ url('/add-adsize') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">

                            <div class="col-12 mb-3">
                                <label class="form-label">Size Name (EN)</label>
                                <input type="text" name="advertisement_size_en" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (LKR)</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>

                            <!-- ad_word_count and max_images removed -->

                            <div class="col-12 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-control category-select" data-lang="en" required>
                                    <option value="">Select</option>
                                    @foreach ($categoriesEn as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->category_name_en }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Ad Type</label>
                                <select name="advertisement_type_id" class="form-control adtype-select" required>
                                    <option value="">Select</option>
                                    @foreach ($adTypesEn as $type)
                                        <option value="{{ $type->id }}" data-category="{{ $type->category_id }}">
                                            {{ $type->advertisement_type_en }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" name="img_url" class="form-control">
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary">
                            Add Size (English)
                        </button>

                    </form>

                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Add Size (Sinhala)</strong>
                </div>

                <div class="card-body">

                    <form action="{{ url('/add-adsize') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">

                            <div class="col-12 mb-3">
                                <label class="form-label">Size Name (SI)</label>
                                <input type="text" name="advertisement_size_si" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (LKR)</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>

                            <!-- ad_word_count and max_images removed -->

                            <div class="col-12 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-control category-select" data-lang="si" required>
                                    <option value="">Select</option>
                                    @foreach ($categoriesSi as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->category_name_si }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Ad Type</label>
                                <select name="advertisement_type_id" class="form-control adtype-select" required>
                                    <option value="">Select</option>
                                    @foreach ($adTypesSi as $type)
                                        <option value="{{ $type->id }}" data-category="{{ $type->category_id }}">
                                            {{ $type->advertisement_type_si }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" name="img_url" class="form-control">
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary">
                            Add Size (Sinhala)
                        </button>

                    </form>

                </div>
            </div>
        </div>

    </div>


    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <strong>Advertisement Sizes List</strong>
        </div>

        <div class="card-body p-0">

            <table class="table table-bordered table-striped mb-0">

                <thead class="table-dark">
                    <tr>
                        <th width="60">ID</th>
                        <th>Size (EN)</th>
                        <th>Size (SI)</th>
                        <th>Ad Type</th>
                        <th>Category</th>
                        <th>Price</th>
                        <!-- Ad word count and Max images columns removed -->
                        <th>Image</th>
                        <th width="120">Status</th>
                        <th width="180">Updated</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($adSizes as $size)

                    <tr>

                        <td>{{ $size->id }}</td>

                        <td>{{ $size->advertisement_size_en }}</td>


                        <td>{{ $size->advertisement_size_si }}</td>

                        <td>{{ $size->type_name ?? 'N/A' }}</td>

                        <td>{{ $size->category_name ?? '-' }}</td>

                        <td>Rs. {{ number_format($size->price, 2) }}</td>

                        <!-- ad_word_count and max_images removed -->

                        <td>
                            @if (data_get($size, 'display_img_url'))
                                <img src="{{ data_get($size, 'display_img_url') }}" width="80">
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if($size->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>{{ \Carbon\Carbon::parse($size->updated_at)->format('Y-m-d H:i') }}</td>

                        <td>
                            <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#editSize{{ $size->id }}">
                                Edit
                            </button>
                        </td>

                    </tr>


                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editSize{{ $size->id }}" tabindex="-1">

                        <div class="modal-dialog">

                            <form action="{{ url('/update-adsize/' . $size->id) }}"
                                method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Size</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label>Size Name (EN)</label>
                                            <input type="text"
                                                name="advertisement_size_en"
                                                class="form-control"
                                                value="{{ $size->advertisement_size_en }}">
                                            <small class="text-muted">Either EN or SI required</small>
                                        </div>

                                        <div class="mb-3">
                                            <label>Size Name (SI)</label>
                                            <input type="text"
                                                name="advertisement_size_si"
                                                class="form-control"
                                                value="{{ $size->advertisement_size_si }}">
                                        </div>

                                        <div class="mb-3">
                                            <label>Price</label>
                                            <input type="number"
                                                step="0.01"
                                                name="price"
                                                class="form-control"
                                                value="{{ $size->price }}"
                                                required>
                                        </div>

                                        <!-- ad_word_count and max_images removed from edit modal -->

                                        <div class="mb-3">
                                            <label>Category</label>
                                            @php
                                                $showEnOnly = filled($size->advertisement_size_en) && !filled($size->advertisement_size_si);
                                                $showSiOnly = filled($size->advertisement_size_si) && !filled($size->advertisement_size_en);
                                            @endphp
                                            <select name="category_id" class="form-control category-select-modal" data-lang="{{ $showSiOnly ? 'si' : 'en' }}">
                                                <option value="">Select</option>
                                                @if($showSiOnly)
                                                    @foreach($categoriesSi as $cat)
                                                        <option value="{{ $cat->id }}" {{ (isset($size->type_category_id) && $size->type_category_id == $cat->id) ? 'selected' : '' }}>
                                                            {{ $cat->category_name_si }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach($categoriesEn as $cat)
                                                        <option value="{{ $cat->id }}" {{ (isset($size->type_category_id) && $size->type_category_id == $cat->id) ? 'selected' : '' }}>
                                                            {{ $cat->category_name_en }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Ad Type</label>
                                            <select name="advertisement_type_id" class="form-control adtype-select-modal">
                                                <option value="">Select</option>
                                                @if($showSiOnly)
                                                    @foreach ($adTypesSi as $type)
                                                        <option value="{{ $type->id }}" data-category="{{ $type->category_id }}" {{ $type->id == $size->advertisement_type_id ? 'selected' : '' }}>
                                                            {{ $type->advertisement_type_si }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach ($adTypesEn as $type)
                                                        <option value="{{ $type->id }}" data-category="{{ $type->category_id }}" {{ $type->id == $size->advertisement_type_id ? 'selected' : '' }}>
                                                            {{ $type->advertisement_type_en }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Status</label>
                                            <select name="is_active" class="form-control">
                                                <option value="1" {{ $size->is_active ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ !$size->is_active ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label>Image (optional)</label>
                                            <input type="file" name="img_url" class="form-control">

                                            @if(data_get($size, 'display_img_url'))
                                            <div class="mt-2">
                                                <img src="{{ data_get($size, 'display_img_url') }}" width="100">
                                            </div>
                                            @endif
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
                        <td colspan="10" class="text-center">
                            No advertisement sizes found.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('[id^="editSize"]').forEach(function(modalEl){
            modalEl.addEventListener('shown.bs.modal', function(){
                try {
                    var en = modalEl.querySelector('input[name="advertisement_size_en"]');
                    var si = modalEl.querySelector('input[name="advertisement_size_si"]');

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
                    console.error('error applying readonly rule for ad size modal', e);
                }

                // Diagnostics (optional)
                var inputs = modalEl.querySelectorAll('input, textarea, select');
                console.group('Edit ad size diagnostics for ' + modalEl.id);
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
                    var en = modalEl.querySelector('input[name="advertisement_size_en"]');
                    var si = modalEl.querySelector('input[name="advertisement_size_si"]');
                    if (en) { en.readOnly = false; en.classList.remove('bg-light'); }
                    if (si) { si.readOnly = false; si.classList.remove('bg-light'); }
                } catch (e) { /* ignore */ }
            });
        });

        // Load ad types for a category from server and populate the ad type select
        function loadAdTypesForSelect(adtypeSelect, categoryId, lang, selectedId) {
            if (!adtypeSelect) return;
            // default option
            adtypeSelect.innerHTML = '<option value="">Select</option>';

            if (!categoryId) return;

            fetch('/adtypes/by-category/' + encodeURIComponent(categoryId) + '?lang=' + encodeURIComponent(lang || 'en'))
                .then(function(res){ return res.json(); })
                .then(function(items){
                    items.forEach(function(it){
                        var opt = document.createElement('option');
                        opt.value = it.id;
                        opt.textContent = it.label;
                        adtypeSelect.appendChild(opt);
                    });

                    if (selectedId) {
                        adtypeSelect.value = selectedId;
                    }
                })
                .catch(function(err){
                    console.error('failed to load ad types for category', err);
                });
        }

        document.querySelectorAll('.category-select').forEach(function(catSel){
            var parent = catSel.closest('form') || catSel.parentElement;
            var adSel = parent.querySelector('.adtype-select');
            var lang = catSel.dataset.lang || 'en';
            // initial load
            loadAdTypesForSelect(adSel, catSel.value, lang);
            catSel.addEventListener('change', function(){
                loadAdTypesForSelect(adSel, this.value, lang);
            });
        });

        // Modal category/adtype linkage - fetch types when modal opens and when category changes
        document.querySelectorAll('[id^="editSize"]').forEach(function(modalEl){
            modalEl.addEventListener('shown.bs.modal', function(){
                try {
                    var catSel = modalEl.querySelector('.category-select-modal');
                    var adSel = modalEl.querySelector('.adtype-select-modal');
                    var lang = catSel && catSel.dataset.lang ? catSel.dataset.lang : 'en';
                    var currentAdType = adSel && adSel.value ? adSel.value : null;
                    if (catSel && adSel) {
                        loadAdTypesForSelect(adSel, catSel.value, lang, currentAdType);
                        catSel.addEventListener('change', function(){
                            loadAdTypesForSelect(adSel, this.value, lang);
                        });
                    }
                } catch (e) {
                    console.error('error loading ad types for modal', e);
                }
            });
        });
    });
    </script>
    @endpush
</div>
@endsection