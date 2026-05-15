@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Edit Advertisement</h2>

    <style>
        .edit-card { border-radius: 14px; box-shadow: 0 8px 24px rgba(18,38,63,0.08); }
        .section-title { font-size: 1rem; font-weight: 700; color: #566a7f; margin-bottom: 1rem; }
        .form-label { font-weight: 600; }
    </style>

    <form action="{{ url('/advertisements/' . $ad->id . '/update') }}" method="POST">
        @csrf

        <div class="card edit-card">
            <div class="card-body">

                <div class="section-title">Customer Details</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $ad->customer_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NIC / Passport</label>
                        <input type="text" name="nic_passport" class="form-control" value="{{ old('nic_passport', $ad->nic_passport) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telephone</label>
                        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $ad->telephone) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $ad->email) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $ad->address) }}" required>
                    </div>
                </div>

                <div class="section-title">Advertisement Details</div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="advertisement_description" class="form-control" rows="4" required>{{ old('advertisement_description', $ad->advertisement_description) }}</textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        {{-- Category should show but not be editable --}}
                            @php
                                $cat = $categories->firstWhere('id', $ad->category_id);
                                // Show only English name
                                $catLabel = trim($cat->category_name_en ?? '');
                            @endphp
                            <input type="text" class="form-control" value="{{ $catLabel ?: '(no English name)' }}" disabled>
                        <input type="hidden" name="category_id" value="{{ $ad->category_id }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">District</label>
                        <select name="district_id" class="form-select">
                            @foreach($districts as $d)
                                @php
                                    // Show only English district names
                                    $distLabel = trim($d->district_name_en ?? '');
                                @endphp
                                @if(trim($distLabel) !== '')
                                    <option value="{{ $d->id }}" {{ old('district_id', $ad->district_id) == $d->id ? 'selected' : '' }}>
                                        {{ $distLabel }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <select name="city_id" class="form-select">
                            @foreach($cities as $c)
                                    @php
                                        // Show only English city names
                                        $cityLabel = trim($c->city_name_en ?? '');
                                @endphp
                                @if(trim($cityLabel) !== '')
                                    <option value="{{ $c->id }}" {{ old('city_id', $ad->city_id) == $c->id ? 'selected' : '' }}>
                                        {{ $cityLabel }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Publish Date</label>
                        <input type="date" name="publish_date" class="form-control"
                               value="{{ old('publish_date', \Illuminate\Support\Carbon::parse($ad->publish_date)->format('Y-m-d')) }}">
                    </div>



                    <div class="col-md-6">
                        <label class="form-label">Web Combined Ad</label>
                        <select name="web_combined_ad" class="form-select">
                            <option value="0" {{ old('web_combined_ad', $ad->web_combined_ad) == 0 ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('web_combined_ad', $ad->web_combined_ad) == 1 ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1" {{ old('status', $ad->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $ad->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select" {{ empty($ad->payment_id) ? 'disabled' : '' }}>
                            <option value="">-- Select --</option>
                            <option value="pending" {{ old('payment_status', $ad->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ old('payment_status', $ad->payment_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ old('payment_status', $ad->payment_status) == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                        @if(empty($ad->payment_id))
                            <small class="text-muted">No payment record found for this advertisement.</small>
                        @endif
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">Amount</label>
                        <input type="text" class="form-control" value="{{ isset($ad->amount) ? 'Rs. ' . number_format($ad->amount, 2) : '—' }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Date</label>
                        <input type="datetime-local" name="payment_date" class="form-control"
                               value="{{ old('payment_date', !empty($ad->payment_date) ? \Illuminate\Support\Carbon::parse($ad->payment_date)->format('Y-m-d\TH:i') : '') }}"
                               {{ empty($ad->payment_id) ? 'disabled' : '' }}>
                        @if(empty($ad->payment_id))
                            <small class="text-muted">No payment record found for this advertisement.</small>
                        @endif
                    </div>
                </div>

                
                
                {{-- Advertisement criterias (category specific) --}}
                @if(isset($criterias) && $criterias->count() > 0)
                    <hr class="my-4">
                    <div class="section-title">Criteria</div>
                    <div class="row g-3">
                        @foreach($criterias as $crit)
                            <div class="col-12">
                                        @php
                                            // Show only English criteria labels
                                            $critLabel = trim($crit->advertisement_criteria_name_en ?? '');
                                        @endphp
                                <label class="form-label">{{ $critLabel }}</label>

                                @php
                                    $existing = $criteriaValues[$crit->id] ?? null;
                                    $options = $criteriaOptions[$crit->id] ?? collect();
                                @endphp

                                @if($crit->field_type === 'textarea')
                                    <textarea name="criteria[{{ $crit->id }}]" class="form-control" rows="3">{{ old('criteria.' . $crit->id, $existing) }}</textarea>

                                @elseif($crit->field_type === 'dropdown')
                                    <select name="criteria[{{ $crit->id }}]" class="form-select">
                                        <option value="">-- Select --</option>
                                        @foreach($options as $opt)
                                                @php
                                                    // Show only English option labels/values
                                                    $optLabel = trim($opt->advertisement_criteria_option_name_en ?? '');
                                                    $optValue = $optLabel;
                                                @endphp
                                            @if(trim($optLabel) !== '')
                                                <option value="{{ $optValue }}" {{ (old('criteria.' . $crit->id, $existing) == $optValue) ? 'selected' : '' }}>
                                                    {{ $optLabel }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>

                                @elseif($crit->field_type === 'radio')
                                    <div>
                                        @foreach($options as $opt)
                                                @php
                                                    // Show only English option labels/values
                                                    $optLabel = trim($opt->advertisement_criteria_option_name_en ?? '');
                                                    $optValue = $optLabel;
                                                @endphp
                                            @if(trim($optLabel) !== '')
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="criteria[{{ $crit->id }}]" id="crit_{{ $crit->id }}_{{ $opt->id }}" value="{{ $optValue }}" {{ (old('criteria.' . $crit->id, $existing) == $optValue) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="crit_{{ $crit->id }}_{{ $opt->id }}">{{ $optLabel }}</label>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ url('/advertisements') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection