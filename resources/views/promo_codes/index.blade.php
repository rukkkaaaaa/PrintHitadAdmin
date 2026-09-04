@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Promo Codes</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($schemaMissing ?? false)
        <div class="alert alert-warning">
            Promo codes table is missing. Please run migrations before adding promo codes.
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Add Promo Code</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/promo-codes') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Promo Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="EX: CAR20" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->category_name_en ?: $category->category_name_si }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Discount Percentage</label>
                        <input type="number" name="discount_percentage" class="form-control" min="0" max="100" step="0.01" value="{{ old('discount_percentage') }}" placeholder="10.00" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Valid From</label>
                        <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Valid Until</label>
                        <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label d-block">Status</label>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="promoActive" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="promoActive">Active</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" {{ ($schemaMissing ?? false) ? 'disabled' : '' }}>
                            Add Promo Code
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Promo Code List</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Discount</th>
                        <th>Valid From</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th style="min-width: 320px;">Update</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promoCodes as $promo)
                        <tr>
                            <td><strong>{{ $promo->code }}</strong></td>
                            <td>{{ $promo->category_name ?? '—' }}</td>
                            <td>{{ number_format((float) $promo->discount_percentage, 2) }}%</td>
                            <td>{{ $promo->valid_from }}</td>
                            <td>{{ $promo->valid_until }}</td>
                            <td>
                                @if($promo->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ url('/promo-codes/' . $promo->id . '/update') }}" class="row g-2 align-items-end">
                                    @csrf
                                    <div class="col-6">
                                        <input type="text" name="code" class="form-control form-control-sm" value="{{ $promo->code }}" required>
                                    </div>
                                    <div class="col-6">
                                        <select name="category_id" class="form-select form-select-sm" required>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ (int) $promo->category_id === (int) $category->id ? 'selected' : '' }}>
                                                    {{ $category->category_name_en ?: $category->category_name_si }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <input type="number" name="discount_percentage" class="form-control form-control-sm" min="0" max="100" step="0.01" value="{{ $promo->discount_percentage }}" required>
                                    </div>
                                    <div class="col-4">
                                        <input type="date" name="valid_from" class="form-control form-control-sm" value="{{ $promo->valid_from }}" required>
                                    </div>
                                    <div class="col-4">
                                        <input type="date" name="valid_until" class="form-control form-control-sm" value="{{ $promo->valid_until }}" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="hidden" name="is_active" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="promoActive{{ $promo->id }}" {{ $promo->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="promoActive{{ $promo->id }}">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-warning">Update</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No promo codes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
