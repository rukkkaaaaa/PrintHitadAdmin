@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Edit Advertisement</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $currentRole = strtolower(trim((string) data_get(session('user'), 'role', '')));
        $canEditPaymentFields = $currentRole === 'super admin';
        $topAdSupported = $topAdSupported ?? false;
        $generalSettings = $generalSettings ?? [];
        $retypedDone = (bool) ($ad->retyped_advertisement_description_done ?? false);
        $topAdRate = trim($ad->publication ?? '') === 'lahipita'
            ? (float) ($generalSettings['top_ad_rate_si'] ?? 0)
            : (float) ($generalSettings['top_ad_rate_en'] ?? 0);
    @endphp

    <style>
        .edit-card { border-radius: 14px; box-shadow: 0 8px 24px rgba(18,38,63,0.08); }
        .section-title { font-size: 1rem; font-weight: 700; color: #566a7f; margin-bottom: 1rem; }
        .form-label { font-weight: 600; }
        .description-done-btn.is-done { background-color: #28a745; border-color: #28a745; color: #fff; }
    </style>

    <form action="{{ url('/advertisements/' . $ad->id . '/update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card edit-card">
            <div class="card-body">

                <div class="section-title">Customer Details</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Customer Name</label>
                        <input type="text"
       name="customer_name"
       class="form-control"
       value="{{ mb_strtoupper(old('customer_name', $ad->customer_name), 'UTF-8') }}"
       oninput="this.value = this.value.toUpperCase()"
       required>
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
                        <input type="text"
       name="address"
       class="form-control"
       value="{{ mb_strtoupper(old('address', $ad->address), 'UTF-8') }}"
       oninput="this.value = this.value.toUpperCase()"
       required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NIC Front Photo</label>
                        <input type="file" name="nic_front_image" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                        <small class="text-muted">Accepted formats: JPG, JPEG, PNG (Max 5MB)</small>
                        @if(!empty($ad->nic_front_img_url))
                            <div class="mt-2 p-2" style="background-color: #f0f8ff; border-radius: 6px; border-left: 3px solid #0d6efd;">
                                <small class="text-muted d-block mb-1">
                                    <strong>📄 Current file:</strong> {{ basename($ad->nic_front_img_url) }}
                                </small>
                                <div class="form-control mt-1" style="font-size: 0.875rem; background-color: #fff; word-break: break-all;">
                                    {{ $ad->nic_front_img_url }}
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NIC Back Photo</label>
                        <input type="file" name="nic_back_image" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                        <small class="text-muted">Accepted formats: JPG, JPEG, PNG (Max 5MB)</small>
                        @if(!empty($ad->nic_back_img_url))
                            <div class="mt-2 p-2" style="background-color: #f0f8ff; border-radius: 6px; border-left: 3px solid #0d6efd;">
                                <small class="text-muted d-block mb-1">
                                    <strong>📄 Current file:</strong> {{ basename($ad->nic_back_img_url) }}
                                </small>
                                <div class="form-control mt-1" style="font-size: 0.875rem; background-color: #fff; word-break: break-all;">
                                    {{ $ad->nic_back_img_url }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="section-title">Advertisement Details</div>
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                        <label class="form-label mb-0">Current Description</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="copyCurrentDescriptionBtn">
                            Copy description
                        </button>
                    </div>
                    <textarea class="form-control mb-2" id="currentDescriptionBox" rows="4" readonly>{{ $ad->advertisement_description }}</textarea>
                    <small class="text-muted d-block mb-2">The saved description is shown above for reference only. Please retype the updated description below.</small>

                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                        <label class="form-label mb-0">Retype Description</label>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="copyRetypedDescriptionBtn">
                                Copy retyped
                            </button>
                            <button type="button"
                                    id="retypedDescriptionDoneBtn"
                                    class="btn btn-sm description-done-btn {{ $retypedDone ? 'is-done' : 'btn-outline-secondary' }}"
                                    {{ $retypedDone ? 'disabled' : '' }}>
                                Update
                            </button>
                        </div>
                    </div>
                    <textarea name="retyped_advertisement_description"
                              id="retypedDescriptionBox"
                              class="form-control"
                              rows="4"
                              required
                              {{ $retypedDone ? 'readonly' : '' }}>{{ old('retyped_advertisement_description', $ad->retyped_advertisement_description ?? '') }}</textarea>

                    <div class="mt-3">
    <label class="form-label" for="referenceNumberInput">
        Reference Number
    </label>

    <input type="text"
           name="reference_number"
           id="referenceNumberInput"
           class="form-control @error('reference_number') is-invalid @enderror"
           value="{{ old('reference_number', $ad->reference_number ?? $ad->order_ref ?? '') }}"
           placeholder="Enter reference number">

    @error('reference_number')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

                    <input type="hidden" name="retyped_advertisement_description_done" id="retypedDescriptionDoneInput" value="{{ $retypedDone ? 1 : 0 }}">
                    @error('retyped_advertisement_description')
                        <div class="text-danger mt-1" style="font-size: 0.875rem;">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-2">Click Updtae after retyping. Then Click on Update in below. Once confirmed, this field becomes locked and stays confirmed when you return.</small>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        {{-- Category should show but not be editable --}}
                            @php
                                $cat = $categories->firstWhere('id', $ad->category_id);
                                // If this is a Lahipita ad prefer Sinhala labels, otherwise English
                                if (trim($ad->publication ?? '') === 'lahipita') {
                                    $catLabel = trim($cat->category_name_si ?? '');
                                    $catFallback = '(no Sinhala name)';
                                } else {
                                    $catLabel = trim($cat->category_name_en ?? '');
                                    $catFallback = '(no English name)';
                                }
                            @endphp
                            <input type="text" class="form-control" value="{{ $catLabel ?: $catFallback }}" disabled>
                        <input type="hidden" name="category_id" value="{{ $ad->category_id }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">District</label>
                        <select name="district_id" id="district_id" class="form-select">
                            @foreach($districts as $d)
                                @php
                                    // Use Sinhala labels for Lahipita, otherwise English
                                    if (trim($ad->publication ?? '') === 'lahipita') {
                                        $distLabel = trim($d->district_name ?? '');
                                    } else {
                                        $distLabel = trim($d->district_name ?? '');
                                    }
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
                        <select name="city_id" id="city_id" class="form-select">
                            @foreach($cities as $c)
                                    @php
                                        // Use Sinhala labels for Lahipita, otherwise English
                                        if (trim($ad->publication ?? '') === 'lahipita') {
                                            $cityLabel = trim($c->city_name ?? '');
                                        } else {
                                            $cityLabel = trim($c->city_name ?? '');
                                        }
                                    @endphp
                                    @if(trim($cityLabel) !== '')
                                        <option value="{{ $c->id }}" data-district="{{ $c->district_id }}" {{ old('city_id', $ad->city_id) == $c->id ? 'selected' : '' }}>
                                            {{ $cityLabel }}
                                        </option>
                                    @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Publish Date</label>
                        @php
                            $isSundayPub = in_array($ad->publication ?? '', ['lahipita', 'hitad_print', 'hitad']);
                            $today = \Illuminate\Support\Carbon::today();
                            $minSunday = $today->isSunday() ? $today : $today->copy()->next(\Illuminate\Support\Carbon::SUNDAY);
                        @endphp
                        <input type="date" name="publish_date" class="form-control"
                               value="{{ old('publish_date', \Illuminate\Support\Carbon::parse($ad->publish_date)->format('Y-m-d')) }}"
                               @if($isSundayPub)
                               min="{{ $minSunday->format('Y-m-d') }}" step="7"
                               @endif>
                        @error('publish_date')
                            <div class="text-danger mt-1" style="font-size: 0.875rem;">{{ $message }}</div>
                        @enderror
                    </div>



                    <div class="col-md-6">
                        <label class="form-label">Tint</label>
                        <select name="advertisement_tint_id" class="form-select">
                            <option value="">No Tint</option>
                            @foreach(($tints ?? collect()) as $tint)
                                @php
                                    $tintLabel = trim((trim($ad->publication ?? '') === 'lahipita')
                                        ? ($tint->advertisement_tint_si ?? '')
                                        : ($tint->advertisement_tint_en ?? ''));

                                    if ($tintLabel === '') {
                                        $tintLabel = trim((trim($ad->publication ?? '') === 'lahipita')
                                            ? ($tint->advertisement_tint_en ?? '')
                                            : ($tint->advertisement_tint_si ?? ''));
                                    }
                                @endphp
                                @if($tintLabel !== '')
                                    <option value="{{ $tint->id }}" {{ old('advertisement_tint_id', $ad->advertisement_tint_id) == $tint->id ? 'selected' : '' }}>
                                        {{ $tintLabel }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('advertisement_tint_id')
                            <div class="text-danger mt-1" style="font-size: 0.875rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Web Combined Ad</label>
                        <select name="web_combined_ad_hitadlk" class="form-select">
                            <option value="0" {{ old('web_combined_ad_hitadlk', $ad->web_combined_ad_hitadlk) == 0 ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('web_combined_ad_hitadlk', $ad->web_combined_ad_hitadlk) == 1 ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    @if($topAdSupported)
                    <div class="col-md-6">
                        <label class="form-label d-block" for="topAdToggle">Top Ad</label>
                        <input type="hidden" name="top_ad" value="0">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="topAdToggle" name="top_ad" value="1" {{ old('top_ad', (int) ($ad->top_ad ?? 0)) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="topAdToggle">Pin this advertisement in the top ad slot</label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            @if($topAdRate > 0)
                                Top ad placement adds LKR {{ number_format($topAdRate, 2) }} to the calculated amount.
                            @else
                                Enable this if the ad should run in the top placement.
                            @endif
                        </small>
                    </div>
                    @endif

                    <div class="col-md-6">
                        {{-- Status removed: advertisement-level status removed from model/table --}}
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select" {{ empty($ad->payment_id) || !$canEditPaymentFields ? 'disabled' : '' }}>
                            <option value="">-- Select --</option>
                            <option value="pending" {{ old('payment_status', $ad->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ old('payment_status', $ad->payment_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ old('payment_status', $ad->payment_status) == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                        @if(empty($ad->payment_id))
                            <small class="text-muted">No payment record found for this advertisement.</small>
                        @elseif(!$canEditPaymentFields)
                            <small class="text-muted">Only super admin can edit payment status.</small>
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
                               {{ empty($ad->payment_id) || !$canEditPaymentFields ? 'disabled' : '' }}>
                        @if(empty($ad->payment_id))
                            <small class="text-muted">No payment record found for this advertisement.</small>
                        @elseif(!$canEditPaymentFields)
                            <small class="text-muted">Only super admin can edit payment date.</small>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Receipt No.</label>
                        <input type="text" name="receipt_number" class="form-control"
                               value="{{ old('receipt_number', $ad->receipt_number ?? '') }}"
                               {{ empty($ad->payment_id) || !$canEditPaymentFields ? 'disabled' : '' }}
                               placeholder="Enter receipt number">
                        @if(empty($ad->payment_id))
                            <small class="text-muted">No payment record found for this advertisement.</small>
                        @elseif(!$canEditPaymentFields)
                            <small class="text-muted">Only super admin can edit receipt number.</small>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Payment Slip</label>
                        <input type="file" name="payment_slip" class="form-control" accept=".pdf,.jpg,.jpeg,.png"
                               {{ empty($ad->payment_id) || !$canEditPaymentFields ? 'disabled' : '' }}>
                        <small class="text-muted">Accepted formats: PDF, JPG, JPEG, PNG (Max 5MB)</small>
                        @if(!empty($ad->payment_slip_file_path))
                            <div class="mt-2 p-2" style="background-color: #f0f8ff; border-radius: 6px; border-left: 3px solid #0d6efd;">
                                <small class="text-muted d-block mb-1">
                                    <strong>📄 File uploaded:</strong> {{ basename($ad->payment_slip_file_path) }}
                                </small>
                                <small class="text-muted d-block">Stored path:</small>
                                <div class="form-control mt-1" style="font-size: 0.875rem; background-color: #fff; word-break: break-all;">
                                    {{ $ad->payment_slip_file_path }}
                                </div>
                            </div>
                        @else
                            @if(!empty($ad->payment_id))
                                <small class="text-muted d-block mt-2">No payment slip uploaded yet.</small>
                            @endif
                        @endif
                    </div>
                </div>

                
                
                {{-- Advertisement criterias (category specific) --}}
                @if(isset($criterias) && $criterias->count() > 0)
                    <hr class="my-4">
                    <div class="section-title">Criteria</div>
                    <div class="row g-3">
                        @foreach($criterias as $crit)
                            @php
                                $critLabel = trim((trim($ad->publication ?? '') === 'lahipita'
                                    ? ($crit->advertisement_criteria_name_si ?? '')
                                    : ($crit->advertisement_criteria_name_en ?? '')));
                                $existing = $criteriaValues[$crit->id] ?? null;
                                $options = $criteriaOptions[$crit->id] ?? collect();
                            @endphp

                            <div class="col-12">
                                <label class="form-label">{{ $critLabel }}</label>

                                @if($crit->field_type === 'textarea')
                                    <textarea name="criteria[{{ $crit->id }}]" class="form-control" rows="3">{{ old('criteria.' . $crit->id, $existing) }}</textarea>
                                @elseif($crit->field_type === 'image')
                                    <input type="file" name="criteria_image[{{ $crit->id }}]" class="form-control" accept="image/*">
                                    <small class="text-muted d-block mt-1">Upload a new image to replace the current one.</small>

                                    @if(!empty($existing))
                                        <div class="mt-2 p-2" style="background-color: #f0f8ff; border-radius: 6px; border-left: 3px solid #0d6efd;">
                                            <small class="text-muted d-block mb-1">
                                                <strong>📄 Current file:</strong> {{ basename((string) $existing) }}
                                            </small>
                                            <div class="form-control mt-1" style="font-size: 0.875rem; background-color: #fff; word-break: break-all;">
                                                {{ $existing }}
                                            </div>
                                        </div>
                                    @endif
                                @elseif($crit->field_type === 'dropdown')
                                    <select name="criteria[{{ $crit->id }}]" class="form-select">
                                        <option value="">-- Select --</option>
                                        @foreach($options as $opt)
                                            @php
                                                $optLabel = trim((trim($ad->publication ?? '') === 'lahipita'
                                                    ? ($opt->advertisement_criteria_option_name_si ?? '')
                                                    : ($opt->advertisement_criteria_option_name_en ?? '')));
                                            @endphp
                                            @if($optLabel !== '')
                                                <option value="{{ $optLabel }}" {{ old('criteria.' . $crit->id, $existing) == $optLabel ? 'selected' : '' }}>
                                                    {{ $optLabel }}
                                                </option>
                                            @endif
                                        @endforeach
                                        @if(!empty($existing) && !collect($options)->contains(fn($opt) => trim((trim($ad->publication ?? '') === 'lahipita' ? ($opt->advertisement_criteria_option_name_si ?? '') : ($opt->advertisement_criteria_option_name_en ?? ''))) === trim((string) $existing)))
                                            <option value="{{ $existing }}" selected>{{ $existing }}</option>
                                        @endif
                                    </select>
                                @elseif($crit->field_type === 'radio')
                                    <div>
                                        @foreach($options as $opt)
                                            @php
                                                $optLabel = trim((trim($ad->publication ?? '') === 'lahipita'
                                                    ? ($opt->advertisement_criteria_option_name_si ?? '')
                                                    : ($opt->advertisement_criteria_option_name_en ?? '')));
                                            @endphp
                                            @if($optLabel !== '')
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="criteria[{{ $crit->id }}]" id="crit_{{ $crit->id }}_{{ $opt->id }}" value="{{ $optLabel }}" {{ old('criteria.' . $crit->id, $existing) == $optLabel ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="crit_{{ $crit->id }}_{{ $opt->id }}">{{ $optLabel }}</label>
                                                </div>
                                            @endif
                                        @endforeach
                                        @if(!empty($existing) && !collect($options)->contains(fn($opt) => trim((trim($ad->publication ?? '') === 'lahipita' ? ($opt->advertisement_criteria_option_name_si ?? '') : ($opt->advertisement_criteria_option_name_en ?? ''))) === trim((string) $existing)))
                                            <div class="form-check form-check-inline mt-2">
                                                <input class="form-check-input" type="radio" name="criteria[{{ $crit->id }}]" id="crit_{{ $crit->id }}_legacy" value="{{ $existing }}" checked>
                                                <label class="form-check-label text-muted" for="crit_{{ $crit->id }}_legacy">{{ $existing }} (legacy value)</label>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button
    type="submit"
    name="action"
    value="approve"
    class="btn btn-success">
    <i class="bx bx-check-circle"></i> Approved
</button>
                    <a href="{{ url('/advertisements') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var copyBtn = document.getElementById('copyCurrentDescriptionBtn');
                    var currentDescriptionBox = document.getElementById('currentDescriptionBox');
                    var copyRetypedBtn = document.getElementById('copyRetypedDescriptionBtn');
                    var retypedDoneBtn = document.getElementById('retypedDescriptionDoneBtn');
                    var retypedDescriptionBox = document.getElementById('retypedDescriptionBox');
                    var retypedDoneInput = document.getElementById('retypedDescriptionDoneInput');

                    if (copyBtn && currentDescriptionBox) {
                        copyBtn.addEventListener('click', function () {
                            var text = currentDescriptionBox.value || '';

                            if (!text.trim()) {
                                return;
                            }

                            if (navigator.clipboard && window.isSecureContext) {
                                navigator.clipboard.writeText(text).then(function () {
                                    copyBtn.textContent = 'Copied!';
                                    setTimeout(function () {
                                        copyBtn.textContent = 'Copy description';
                                    }, 1500);
                                });
                                return;
                            }

                            currentDescriptionBox.removeAttribute('readonly');
                            currentDescriptionBox.select();
                            document.execCommand('copy');
                            currentDescriptionBox.setAttribute('readonly', 'readonly');
                            copyBtn.textContent = 'Copied!';
                            setTimeout(function () {
                                copyBtn.textContent = 'Copy description';
                            }, 1500);
                        });
                    }

                    if (copyRetypedBtn && retypedDescriptionBox) {
                        copyRetypedBtn.addEventListener('click', function () {
                            var text = retypedDescriptionBox.value || '';

                            if (!text.trim()) {
                                return;
                            }

                            if (navigator.clipboard && window.isSecureContext) {
                                navigator.clipboard.writeText(text).then(function () {
                                    copyRetypedBtn.textContent = 'Copied!';
                                    setTimeout(function () {
                                        copyRetypedBtn.textContent = 'Copy retyped';
                                    }, 1500);
                                });
                                return;
                            }

                            var wasReadonly = retypedDescriptionBox.hasAttribute('readonly');
                            if (wasReadonly) {
                                retypedDescriptionBox.removeAttribute('readonly');
                            }
                            retypedDescriptionBox.select();
                            document.execCommand('copy');
                            if (wasReadonly) {
                                retypedDescriptionBox.setAttribute('readonly', 'readonly');
                            }
                            copyRetypedBtn.textContent = 'Copied!';
                            setTimeout(function () {
                                copyRetypedBtn.textContent = 'Copy retyped';
                            }, 1500);
                        });
                    }

                    if (retypedDoneBtn && retypedDescriptionBox && retypedDoneInput) {
                        var lockRetypedDescription = function () {
                            retypedDescriptionBox.setAttribute('readonly', 'readonly');
                            retypedDoneInput.value = '1';
                            retypedDoneBtn.classList.remove('btn-outline-secondary');
                            retypedDoneBtn.classList.add('is-done');
                            retypedDoneBtn.disabled = true;
                        };

                        if (retypedDoneInput.value === '1') {
                            lockRetypedDescription();
                        }

                        retypedDoneBtn.addEventListener('click', function () {
                            lockRetypedDescription();
                        });
                    }

                    var districtSelect = document.getElementById('district_id');
                    var citySelect = document.getElementById('city_id');
                    if (!districtSelect || !citySelect) return;

                    var adCityId = '{{ old('city_id', $ad->city_id) }}';
                    var adDistrictId = '{{ old('district_id', $ad->district_id) }}';

                    function filterCities() {
                        var districtId = districtSelect.value;
                        var options = citySelect.querySelectorAll('option');
                        var found = false;
                        options.forEach(function(opt){
                            // always keep the advertisement's current city option visible so it stays selected
                            if (opt.value == adCityId) {
                                opt.style.display = '';
                                opt.disabled = false;
                                opt.selected = true;
                                found = true;
                                return;
                            }

                            if (opt.dataset.district == districtId) {
                                opt.style.display = '';
                                opt.disabled = false;
                            } else {
                                opt.style.Display = opt.style.display; // noop to keep formatting
                                opt.style.display = 'none';
                                opt.disabled = true;
                                opt.selected = false;
                            }
                        });

                        if (!found) {
                            // if ad city wasn't present (or wasn't in the district), select the first visible option
                            for (var i=0;i<options.length;i++){
                                if (options[i].style.display !== 'none') { options[i].selected = true; break; }
                            }
                        }
                    }

                    // ensure district is set to the ad's district on load (preserve selection)
                    if (adDistrictId && districtSelect.value !== adDistrictId) {
                        districtSelect.value = adDistrictId;
                    }

                    // initial filter on page load
                    filterCities();

                    districtSelect.addEventListener('change', function(){
                        filterCities();
                    });
                });
                </script>

@endsection