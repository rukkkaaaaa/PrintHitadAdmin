@extends('layouts.app')

@push('styles')
<style>
    .tint-category-dropdown .dropdown-menu {
        width: 100%;
        max-height: 260px;
        overflow-y: auto;
    }

    .tint-category-dropdown .dropdown-toggle {
        text-align: left;
    }

    .tint-category-dropdown .dropdown-toggle::after {
        float: right;
        margin-top: 0.6rem;
    }

    .tint-category-dropdown .dropdown-item {
        white-space: normal;
    }

    .tint-category-dropdown .form-check {
        margin: 0;
    }

    .tint-category-dropdown .dropdown-item.active,
    .tint-category-dropdown .dropdown-item:active {
        background-color: transparent;
        color: inherit;
    }
 </style>
@endpush

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
                            <label class="form-label">Categories</label>
                            <div class="dropdown tint-category-dropdown" data-placeholder="Select categories">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-selected-text="Select categories"
                                        aria-expanded="false">
                                    Select categories
                                </button>
                                <div class="dropdown-menu p-2">
                                    @foreach ($categoriesEn as $category)
                                        <label class="dropdown-item">
                                            <div class="form-check">
                                                <input class="form-check-input tint-category-checkbox"
                                                       type="checkbox"
                                                       name="category_ids[]"
                                                       value="{{ $category->id }}"
                                                       data-label="{{ $category->category_name_en }}"
                                                       {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}>
                                                <span class="form-check-label">{{ $category->category_name_en }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <small class="text-muted">Choose one or more categories from the dropdown.</small>
                            <div class="invalid-feedback d-block tint-category-error" style="display:none !important;">Please select at least one category.</div>
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
                            <label class="form-label">Categories</label>
                            <div class="dropdown tint-category-dropdown" data-placeholder="Select categories">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-selected-text="Select categories"
                                        aria-expanded="false">
                                    Select categories
                                </button>
                                <div class="dropdown-menu p-2">
                                    @foreach ($categoriesSi as $category)
                                        <label class="dropdown-item">
                                            <div class="form-check">
                                                <input class="form-check-input tint-category-checkbox"
                                                       type="checkbox"
                                                       name="category_ids[]"
                                                       value="{{ $category->id }}"
                                                       data-label="{{ $category->category_name_si }}"
                                                       {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}>
                                                <span class="form-check-label">{{ $category->category_name_si }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <small class="text-muted">Choose one or more categories from the dropdown.</small>
                            <div class="invalid-feedback d-block tint-category-error" style="display:none !important;">Please select at least one category.</div>
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
                        <th>Categories</th>
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
                                @forelse ($tint->categories as $category)
                                    <span class="badge bg-info text-dark me-1 mb-1">
                                        {{ $category->category_name_en ?: $category->category_name_si }}
                                    </span>
                                @empty
                                    <span class="text-muted">No categories</span>
                                @endforelse
                            </td>

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
                            <td colspan="9" class="text-center">
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

        @php
            $isSinhalaTint = filled($tint->advertisement_tint_si) && !filled($tint->advertisement_tint_en);
            $editCategories = $isSinhalaTint ? $categoriesSi : $categoriesEn;
        @endphp

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
                                <label class="form-label">Categories</label>
                                <div class="dropdown tint-category-dropdown" data-placeholder="Select categories">
                                    <button class="btn btn-outline-secondary dropdown-toggle w-100"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            data-selected-text="Select categories"
                                            aria-expanded="false">
                                        Select categories
                                    </button>
                                    <div class="dropdown-menu p-2">
                                        @foreach ($editCategories as $category)
                                            <label class="dropdown-item">
                                                <div class="form-check">
                                                    <input class="form-check-input tint-category-checkbox"
                                                           type="checkbox"
                                                           name="category_ids[]"
                                                           value="{{ $category->id }}"
                                                           data-label="{{ $isSinhalaTint ? $category->category_name_si : $category->category_name_en }}"
                                                           {{ in_array($category->id, $tint->category_ids ?? []) ? 'checked' : '' }}>
                                                    <span class="form-check-label">{{ $isSinhalaTint ? $category->category_name_si : $category->category_name_en }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <small class="text-muted">Choose one or more categories from the dropdown.</small>
                                <div class="invalid-feedback d-block tint-category-error" style="display:none !important;">Please select at least one category.</div>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropdowns = document.querySelectorAll('.tint-category-dropdown');

        const updateDropdownLabel = (dropdown) => {
            const button = dropdown.querySelector('.dropdown-toggle');
            const checkboxes = dropdown.querySelectorAll('.tint-category-checkbox');
            const checkedBoxes = Array.from(checkboxes).filter((checkbox) => checkbox.checked);
            const labels = checkedBoxes.map((checkbox) => checkbox.dataset.label).filter(Boolean);
            const placeholder = dropdown.dataset.placeholder || 'Select categories';

            if (labels.length === 0) {
                button.textContent = placeholder;
            } else if (labels.length <= 2) {
                button.textContent = labels.join(', ');
            } else {
                button.textContent = labels.length + ' categories selected';
            }
        };

        const updateValidation = (form, showError = false) => {
            const categoryCheckboxes = form.querySelectorAll('.tint-category-checkbox');
            const hasSelection = Array.from(categoryCheckboxes).some((checkbox) => checkbox.checked);
            const error = form.querySelector('.tint-category-error');

            if (error) {
                error.style.display = !hasSelection && showError ? 'block' : 'none';
            }

            return hasSelection;
        };

        dropdowns.forEach((dropdown) => {
            updateDropdownLabel(dropdown);

            dropdown.querySelectorAll('.tint-category-checkbox').forEach((checkbox) => {
                checkbox.addEventListener('change', function () {
                    updateDropdownLabel(dropdown);
                    const form = dropdown.closest('form');

                    if (form) {
                        updateValidation(form, true);
                    }
                });
            });
        });

        document.querySelectorAll('form[action$="/add-tint"], form[action*="/update-tint/"]').forEach((form) => {
            updateValidation(form, false);

            form.addEventListener('submit', function (event) {
                if (!updateValidation(form, true)) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
@endpush
