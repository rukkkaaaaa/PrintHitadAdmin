@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4">Ad Rates</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <strong>Add Ad Rate</strong>
                </div>
                <div class="card-body">
                    <form action="{{ url('/add-adrate') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Publication</label>
                            <select name="publication" class="form-control" id="publicationSelect" required>
                                <option value="hitad_print" {{ old('publication', 'hitad_print') === 'hitad_print' ? 'selected' : '' }}>Hitad</option>
                                <option value="lahipita" {{ old('publication') === 'lahipita' ? 'selected' : '' }}>Lahipita</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" id="categorySelect" required>
                                <option value="">-- Select category --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Advertisement Type</label>
                            <select name="advertisement_type_id" class="form-control" id="typeSelect" required>
                                <option value="">-- Select type --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Advertisement Size</label>
                            <select name="advertisement_size_id" class="form-control" id="sizeSelect" required>
                                <option value="">-- Select size --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-control" required>
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        @foreach ($columns as $col)
                            @php $field = $col->Field; $type = $col->Type; @endphp

                            @if (in_array($field, ['id', 'created_at', 'updated_at', 'publication', 'category_id', 'advertisement_type_id', 'advertisement_size_id', 'is_active']))
                                @continue
                            @endif

                            <div class="mb-3">
                                <label class="form-label">{{ ucfirst(str_replace('_', ' ', $field)) }}</label>

                                @if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'double') || str_contains($type, 'float'))
                                    <input type="number" name="{{ $field }}" class="form-control" value="{{ old($field, $col->Default) }}">
                                @elseif (str_contains($type, 'text'))
                                    <textarea name="{{ $field }}" class="form-control">{{ old($field, $col->Default) }}</textarea>
                                @elseif (str_contains($field, 'date') || str_contains($type, 'date'))
                                    <input type="date" name="{{ $field }}" class="form-control" value="{{ old($field, $col->Default) }}">
                                @else
                                    <input type="text" name="{{ $field }}" class="form-control" value="{{ old($field, $col->Default) }}">
                                @endif
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary">Add Rate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Existing Ad Rates</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Advertisement Type</th>
                            <th>Advertisement Size</th>
                            <th>Publication</th>
                            <th>Print Price</th>
                            <th>Web Price</th>
                            <th>Is Active</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adRates as $rate)
                            <tr>
                                <td>{{ $rate->id }}</td>
                                <td>{{ $rate->category_name ?? 'N/A' }}</td>
                                <td>{{ $rate->advertisement_type_name ?? 'N/A' }}</td>
                                <td>{{ $rate->advertisement_size_name ?? 'N/A' }}</td>
                                <td>{{ $rate->publication === 'lahipita' ? 'Lahipita' : 'Hitad' }}</td>
                                <td>{{ $rate->print_price ?? '0.00' }}</td>
                                <td>{{ $rate->web_price ?? '0.00' }}</td>
                                <td>{{ (int) ($rate->is_active ?? 0) === 1 ? 'Active' : 'Inactive' }}</td>
                                <td>{{ \Carbon\Carbon::parse($rate->updated_at ?? $rate->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No ad rates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const publicationSel = document.getElementById('publicationSelect');
    const categorySel = document.getElementById('categorySelect');
    const typeSel = document.getElementById('typeSelect');
    const sizeSel = document.getElementById('sizeSelect');

    const categoriesByLang = {
        en: @json($categoriesEn->map(function ($cat) {
            return [
                'id' => $cat->id,
                'label' => $cat->category_name_en,
            ];
        })->values()),
        si: @json($categoriesSi->map(function ($cat) {
            return [
                'id' => $cat->id,
                'label' => $cat->category_name_si,
            ];
        })->values())
    };

    const oldCategoryId = @json(old('category_id'));
    const oldTypeId = @json(old('advertisement_type_id'));
    const oldSizeId = @json(old('advertisement_size_id'));

    function currentLang() {
        return publicationSel && publicationSel.value === 'lahipita' ? 'si' : 'en';
    }

    function resetSelect(selectEl, placeholder) {
        if (!selectEl) return;
        selectEl.innerHTML = '';
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;
        selectEl.appendChild(placeholderOption);
    }

    function populateCategories(selectedId) {
        const lang = currentLang();
        resetSelect(categorySel, '-- Select category --');

        (categoriesByLang[lang] || []).forEach(function (cat) {
            if (!cat || !cat.label) return;
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.label;
            if (String(cat.id) === String(selectedId)) {
                opt.selected = true;
            }
            categorySel.appendChild(opt);
        });
    }

    function loadTypes(categoryId, selectedTypeId, selectedSizeId) {
        resetSelect(typeSel, '-- Select type --');
        resetSelect(sizeSel, '-- Select size --');

        if (!categoryId) {
            return Promise.resolve([]);
        }

        const lang = currentLang();
        return fetch('/adtypes/by-category/' + categoryId + '?lang=' + lang)
            .then(function (r) { return r.json(); })
            .then(function (types) {
                types.forEach(function (t) {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.label;
                    if (String(t.id) === String(selectedTypeId)) {
                        opt.selected = true;
                    }
                    typeSel.appendChild(opt);
                });

                if (selectedTypeId) {
                    return loadSizes(selectedTypeId, selectedSizeId);
                }

                return [];
            })
            .catch(function () {
                return [];
            });
    }

    function loadSizes(typeId, selectedSizeId) {
        resetSelect(sizeSel, '-- Select size --');

        if (!typeId) {
            return Promise.resolve([]);
        }

        const lang = currentLang();
        return fetch('/adsizes/by-type/' + typeId + '?lang=' + lang)
            .then(function (r) { return r.json(); })
            .then(function (sizes) {
                sizes.forEach(function (s) {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.label;
                    if (String(s.id) === String(selectedSizeId)) {
                        opt.selected = true;
                    }
                    sizeSel.appendChild(opt);
                });
                return sizes;
            })
            .catch(function () {
                return [];
            });
    }

    function refreshForPublication(selectedCategoryId, selectedTypeId, selectedSizeId) {
        populateCategories(selectedCategoryId);
        const catId = categorySel.value || selectedCategoryId || '';

        if (!catId) {
            resetSelect(typeSel, '-- Select type --');
            resetSelect(sizeSel, '-- Select size --');
            return;
        }

        loadTypes(catId, selectedTypeId, selectedSizeId);
    }

    publicationSel.addEventListener('change', function () {
        refreshForPublication('', '', '');
    });

    categorySel.addEventListener('change', function () {
        loadTypes(categorySel.value, '', '');
    });

    typeSel.addEventListener('change', function () {
        loadSizes(typeSel.value, '');
    });

    refreshForPublication(oldCategoryId, oldTypeId, oldSizeId);
});
</script>
@endpush
