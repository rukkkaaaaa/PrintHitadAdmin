@php
    $formId = $formId ?? 'advertisementCreateForm';
    $action = $action ?? url('/advertisements/store');
    $submitLabel = $submitLabel ?? 'Save Advertisement';
    $publicationValue = old('publication', $publication ?? 'hitad_print');
    $isLahipita = $publicationValue === 'lahipita';
    $autoOpen = $autoOpen ?? false;
    $categories = $categories ?? collect();
    $districts = $districts ?? collect();
    $cities = $cities ?? collect();
    $paymentMethods = $paymentMethods ?? collect();
    $publicationDeadlines = $publicationDeadlines ?? [];
    $topAdSupported = $topAdSupported ?? false;
    $generalSettings = $generalSettings ?? [
        'max_words_en' => 65,
        'max_words_si' => 65,
        'additional_word_rate_en' => 20,
        'additional_word_rate_si' => 20,
        'free_word_limit_en' => 15,
        'free_word_limit_si' => 15,
        'top_ad_rate_en' => 100,
        'top_ad_rate_si' => 100,
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-day.flatpickr-disabled,
    .flatpickr-day.flatpickr-disabled:hover {
        color: #c8c8c8 !important;
        background: transparent !important;
        cursor: not-allowed !important;
        text-decoration: line-through;
    }
</style>
@endpush

<style>
    .ad-form-shell {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }
    .ad-form-shell .section-title {
        font-size: .92rem;
        font-weight: 700;
        letter-spacing: .02em;
        color: #5b6b7a;
        text-transform: uppercase;
        margin-bottom: .85rem;
    }
    .ad-form-shell .form-label {
        font-weight: 600;
        color: #374151;
    }
    .ad-form-shell .help-note {
        font-size: .82rem;
        color: #6b7280;
    }
    .ad-form-shell .soft-card {
        border: 1px solid rgba(231, 236, 245, .95);
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(18, 38, 63, 0.05);
    }
    .ad-form-shell .criteria-block {
        border: 1px solid #edf2f7;
        border-radius: 14px;
        background: #fff;
        padding: 1rem;
    }
    .wc-badge {
        font-size: .76rem;
        font-weight: 600;
        padding: .18rem .55rem;
        border-radius: 50px;
        background: #e0f2fe;
        color: #0369a1;
        white-space: nowrap;
    }
    .wc-badge.over {
        background: #fee2e2;
        color: #dc2626;
    }
    .img-badge {
        font-size: .76rem;
        font-weight: 600;
        padding: .18rem .55rem;
        border-radius: 50px;
        background: #d1fae5;
        color: #065f46;
        white-space: nowrap;
    }
</style>

<form id="{{ $formId }}"
      action="{{ $action }}"
      method="POST"
      enctype="multipart/form-data"
    novalidate
      class="ad-form-shell">
    @csrf

    <div class="p-4">

        {{-- ── Card 1: Publication & Category ─────────────────────────── --}}
        <div class="soft-card p-3 mb-4 bg-white">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Publication</label>
                    <select name="publication" id="pubSel" class="form-select" required>
                        <option value="hitad_print" {{ $publicationValue === 'hitad_print' ? 'selected' : '' }}>HitAd</option>
                        <option value="lahipita"    {{ $publicationValue === 'lahipita'    ? 'selected' : '' }}>Lahipita</option>
                    </select>
                    <div class="help-note mt-1">Pick the brand first. Labels switch to English or Sinhala automatically.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="catSel" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            @php
                                $catEn = trim($cat->category_name_en ?? '');
                                $catSi = trim($cat->category_name_si ?? '');
                                $catLabel = $isLahipita ? ($catSi ?: $catEn) : ($catEn ?: $catSi);
                            @endphp
                            <option value="{{ $cat->id }}"
                                    data-en="{{ $catEn }}"
                                    data-si="{{ $catSi }}"
                                    {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $catLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Card 2: Type (shown after category selected) ────────────── --}}
        <div class="soft-card p-3 mb-4 bg-white" id="typeCard" style="display:none">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Type</label>
                    <select name="advertisement_type_id" id="typeSel" class="form-select" required>
                        <option value="">Select Type</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Card 3: Size (shown after type selected) ────────────────── --}}
        <div class="soft-card p-3 mb-4 bg-white" id="sizeCard" style="display:none">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Size</label>
                    <select name="advertisement_size_id" id="sizeSel" class="form-select" required>
                        <option value="">Select Size</option>
                    </select>
                    <div id="sizeHints" class="mt-2 d-flex gap-2" style="display:none">
                        <span class="wc-badge"  id="wcHint"></span>
                        <span class="img-badge" id="imgHint"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 4: Advertisement Details (shown after size selected) ── --}}
        <div class="soft-card p-3 mb-4 bg-white" id="adDetailsCard" style="display:none">
            <div class="section-title">Advertisement details</div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Description</span>
                        <span id="wcDisplay"></span>
                    </label>
                    <textarea name="advertisement_description"
                              id="descTA"
                              class="form-control"
                              rows="4"
                              required>{{ old('advertisement_description') }}</textarea>
                    <div id="descHint" class="help-note mt-1" style="display:none"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Upload Images</label>
                    <input type="file" name="images[]" id="imagesInput" class="form-control" multiple accept="image/*">
                    <small class="help-note" id="imagesHint">JPG, PNG, GIF supported.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Publish Date</label>
                    <input type="text" name="publish_date" id="publishDateInput" class="form-control" value="{{ old('publish_date') }}" placeholder="Select a Sunday" autocomplete="off" required>
                    <small class="help-note" id="publishDateHelp">Only Sundays are selectable.</small>
                    <div id="publishDateCutoffWarning" class="text-danger mt-1" style="font-size:.82rem; display:none;"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label d-block">Web Combined Ad</label>
                    <select name="web_combined_ad" class="form-select">
                        <option value="0" {{ old('web_combined_ad', 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('web_combined_ad') == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                @if($topAdSupported)
                <div class="col-md-6">
                    <label class="form-label d-block" for="topAdToggle">Top Ad</label>
                    <input type="hidden" name="top_ad" value="0">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="topAdToggle" name="top_ad" value="1" {{ old('top_ad') == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="topAdToggle">Pin this advertisement in the top ad slot</label>
                    </div>
                    <small class="help-note d-block mt-1" id="topAdHint"></small>
                </div>
                @endif
                <div class="col-md-6" id="tintFieldWrap">
                    <label class="form-label">Tint</label>
                    <select name="advertisement_tint_id" id="tintSel" class="form-select" data-old="{{ old('advertisement_tint_id') }}">
                        <option value="">No Tint</option>
                    </select>
                </div>
                {{-- Status removed: advertisement status is no longer stored on advertisements table --}}
            </div>
        </div>

        {{-- ── Card 5: Location (shown after size selected) ────────────── --}}
        <div class="soft-card p-3 mb-4 bg-white" id="locationCard" style="display:none">
            <div class="section-title">Location</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">District</label>
                    <select name="district_id" id="districtSelect" class="form-select" required>
                        <option value="">Select District</option>
                        @foreach($districts as $district)
                            @php
                                $distEn = trim($district->district_name_en ?? '');
                                $distSi = trim($district->district_name_si ?? '');
                                $distLabel = $isLahipita ? ($distSi ?: $distEn) : ($distEn ?: $distSi);
                            @endphp
                            <option value="{{ $district->id }}"
                                    data-en="{{ $distEn }}"
                                    data-si="{{ $distSi }}"
                                    {{ old('district_id') == $district->id ? 'selected' : '' }}>
                                {{ $distLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">City</label>
                    <select name="city_id" id="citySelect" class="form-select" required disabled>
                        <option value="">Select District first</option>
                        @foreach($cities as $city)
                            @php
                                $cityEn = trim($city->city_name_en ?? '');
                                $citySi = trim($city->city_name_si ?? '');
                                $cityLabel = $isLahipita ? ($citySi ?: $cityEn) : ($cityEn ?: $citySi);
                            @endphp
                            <option value="{{ $city->id }}"
                                    data-district="{{ $city->district_id }}"
                                    data-en="{{ $cityEn }}"
                                    data-si="{{ $citySi }}"
                                    {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $cityLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Card 6: Criteria (AJAX, shown after size selected if any) ── --}}
        <div class="soft-card p-3 mb-4 bg-white" id="criteriaCard" style="display:none">
            <div class="section-title">Advertising criteria</div>
            <div class="row g-3" id="criteriaBlocks"></div>
        </div>

        {{-- ── Card 7: Advertiser Details (shown after size selected) ───── --}}
        <div class="soft-card p-3 mb-4 bg-white" id="customerCard" style="display:none">
            <div class="section-title">Advertiser details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIC / Passport</label>
                    <input type="text" name="nic_passport" class="form-control" value="{{ old('nic_passport') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Email</label>
                    <input type="email" name="confirm_email" class="form-control" value="{{ old('confirm_email') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address') }}" required>
                </div>
                <div class="col-md-6" id="nicFrontFieldWrap">
                    <label class="form-label">NIC Front Photo</label>
                    <input type="file" name="nic_front_image" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                    <small class="help-note">Allowed: JPG, JPEG, PNG (max 5MB)</small>
                </div>
                <div class="col-md-6" id="nicBackFieldWrap">
                    <label class="form-label">NIC Back Photo</label>
                    <input type="file" name="nic_back_image" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                    <small class="help-note">Allowed: JPG, JPEG, PNG (max 5MB)</small>
                </div>
            </div>
        </div>

        {{-- ── Card 8: Payment Details (shown after size selected) ────────── --}}
        <div class="soft-card p-3 mb-4 bg-white" id="paymentCard" style="display:none">
            <div class="section-title">Payment details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method_id" class="form-select">
                        <option value="">-- Not paid yet --</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->id }}" {{ old('payment_method_id') == $pm->id ? 'selected' : '' }}>
                                {{ $pm->payment_method_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select" required>
                        <option value="pending"   {{ old('payment_status', 'pending') === 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ old('payment_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed"    {{ old('payment_status') === 'failed'    ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Amount (LKR)</label>
                    <input type="number" name="payment_amount" class="form-control" min="0" step="0.01"
                           value="{{ old('payment_amount') }}" placeholder="0.00" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Date</label>
                    <input type="text" name="payment_date" id="paymentDateInput" class="form-control"
                           value="{{ old('payment_date') }}" placeholder="Select date" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Receipt Number</label>
                    <input type="text" name="receipt_number" class="form-control"
                           value="{{ old('receipt_number') }}" placeholder="Enter receipt number">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Slip</label>
                    <input type="file" name="payment_slip" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="help-note">Allowed: PDF, JPG, JPEG, PNG (max 5MB)</small>
                </div>
            </div>
        </div>

        {{-- ── Form actions (shown after size selected) ─────────────────── --}}
        <div id="formActions" style="display:none">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                <div class="help-note">The brand toggle updates labels and criteria text without affecting the rest of the admin panel.</div>
                <div class="d-flex gap-2">
                    <a href="{{ url('/advertisements') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">{{ $submitLabel }}</button>
                </div>
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {
    'use strict';

    const form = document.getElementById(@json($formId));
    if (!form) return;

    const publicationDeadlines = @json($publicationDeadlines);
    const generalSettings = @json($generalSettings);

    /* ── Flatpickr: Sundays only + publication cutoffs ───────────────── */
    var publishInput = form.querySelector('#publishDateInput');
    var publishHelp = form.querySelector('#publishDateHelp');
    var publishPicker = null;

    function twoDigit(num) {
        return String(num).padStart(2, '0');
    }

    function getPublicationRule(publication) {
        if (publicationDeadlines && publicationDeadlines[publication]) {
            return publicationDeadlines[publication];
        }

        return publication === 'lahipita'
            ? { label: 'Lahipita', cutoff_day_of_week: 2, cutoff_time: '18:00:00' }
            : { label: 'HitAd', cutoff_day_of_week: 5, cutoff_time: '18:00:00' };
    }

    function parseTimeParts(timeString) {
        var parts = String(timeString || '18:00:00').split(':');
        return {
            hour: parseInt(parts[0] || '18', 10),
            minute: parseInt(parts[1] || '00', 10)
        };
    }

    function getNextOrSameSunday(dateObj) {
        var start = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
        var day = start.getDay();
        var diff = (7 - day) % 7;
        start.setDate(start.getDate() + diff);
        return start;
    }

    function getCutoffDateTimeForSunday(sundayDate, rule) {
        var cutoffDay = Number(rule.cutoff_day_of_week);
        var daysBack = (7 - cutoffDay) % 7;
        var cutoff = new Date(sundayDate.getFullYear(), sundayDate.getMonth(), sundayDate.getDate());
        cutoff.setDate(cutoff.getDate() - daysBack);

        var time = parseTimeParts(rule.cutoff_time);
        cutoff.setHours(time.hour, time.minute, 0, 0);

        return cutoff;
    }

    function getFirstAllowedSunday(publication) {
        var now = new Date();
        var rule = getPublicationRule(publication);
        var candidate = getNextOrSameSunday(now);
        var cutoffDate = getCutoffDateTimeForSunday(candidate, rule);

        if (now > cutoffDate) {
            candidate.setDate(candidate.getDate() + 7);
        }

        return candidate;
    }

    function parseYmdAsLocalDate(value) {
        var str = String(value || '').trim();
        var parts = str.split('-');
        if (parts.length !== 3) return null;

        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        var day = parseInt(parts[2], 10);

        if (!year || !month || !day) return null;

        return new Date(year, month - 1, day);
    }

    function formatCutoffHelp(rule) {
        var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        var time = parseTimeParts(rule.cutoff_time);
        return 'Only Sundays are selectable. ' + (rule.label || 'Publication')
            + ' cutoff: ' + dayNames[Number(rule.cutoff_day_of_week) || 0]
            + ' ' + twoDigit(time.hour) + ':' + twoDigit(time.minute) + '.';
    }

    function formatCutoffBlockedMessage(publication, sundayDate) {
        var rule = getPublicationRule(publication);
        var time = parseTimeParts(rule.cutoff_time);
        var cutoff = getCutoffDateTimeForSunday(sundayDate, rule);
        var cutoffDate = cutoff.getFullYear() + '-' + twoDigit(cutoff.getMonth() + 1) + '-' + twoDigit(cutoff.getDate());

        return 'Cutoff passed. You cannot add this ad now. ('
            + (rule.label || 'Publication') + ' cutoff: ' + cutoffDate + ' ' + twoDigit(time.hour) + ':' + twoDigit(time.minute) + ')';
    }

    function getSelectedPublishDate() {
        if (publishPicker && publishPicker.selectedDates && publishPicker.selectedDates.length > 0) {
            var selected = publishPicker.selectedDates[0];
            return new Date(selected.getFullYear(), selected.getMonth(), selected.getDate());
        }

        return parseYmdAsLocalDate(publishInput ? publishInput.value : '');
    }

    function validatePublishDateCutoff(showNativeValidity) {
        var warningEl = form.querySelector('#publishDateCutoffWarning');
        var publicationSelect = form.querySelector('#pubSel');
        if (!publishInput || !publicationSelect) return true;

        var selectedSunday = getSelectedPublishDate();
        if (!selectedSunday) {
            if (warningEl) {
                warningEl.style.display = 'none';
                warningEl.textContent = '';
            }
            publishInput.setCustomValidity('');
            return true;
        }

        var rule = getPublicationRule(publicationSelect.value);
        var cutoffDateTime = getCutoffDateTimeForSunday(selectedSunday, rule);
        var now = new Date();
        var blocked = now >= cutoffDateTime;

        if (blocked) {
            var msg = formatCutoffBlockedMessage(publicationSelect.value, selectedSunday);
            if (warningEl) {
                warningEl.style.display = '';
                warningEl.textContent = msg;
            }
            publishInput.setCustomValidity(msg);
            if (showNativeValidity) {
                publishInput.reportValidity();
            }
            return false;
        }

        if (warningEl) {
            warningEl.style.display = 'none';
            warningEl.textContent = '';
        }
        publishInput.setCustomValidity('');
        return true;
    }

    function refreshPublishDateConstraints() {
        var publicationSelect = form.querySelector('#pubSel');
        if (!publishInput || !publicationSelect) return;

        var rule = getPublicationRule(publicationSelect.value);
        var minSunday = getFirstAllowedSunday(publicationSelect.value);

        if (publishPicker) {
            publishPicker.set('minDate', minSunday);

            if (publishPicker.selectedDates.length > 0 && publishPicker.selectedDates[0] < minSunday) {
                publishPicker.clear();
            }
        }

        if (publishHelp) {
            publishHelp.textContent = formatCutoffHelp(rule);
        }

        validatePublishDateCutoff(false);
    }

    if (publishInput) {
        publishPicker = flatpickr(publishInput, {
            dateFormat: 'Y-m-d',
            disableMobile: true,
            minDate: getFirstAllowedSunday((form.querySelector('#pubSel') || {}).value || 'hitad_print'),
            disable: [
                function (date) { return date.getDay() !== 0; }
            ],
            onChange: function () {
                validatePublishDateCutoff(false);
            }
        });

        refreshPublishDateConstraints();
    }

    var paymentDateInput = form.querySelector('#paymentDateInput');
    if (paymentDateInput) {
        flatpickr(paymentDateInput, {
            dateFormat: 'Y-m-d',
            disableMobile: true
        });
    }

    /* ── Element refs ────────────────────────────────────────────────── */
    const pubSel         = form.querySelector('#pubSel');
    const catSel         = form.querySelector('#catSel');
    const typeSel        = form.querySelector('#typeSel');
    const sizeSel        = form.querySelector('#sizeSel');
    const tintSel        = form.querySelector('#tintSel');
    const distSel        = form.querySelector('#districtSelect');
    const citySel        = form.querySelector('#citySelect');
    const typeCard       = form.querySelector('#typeCard');
    const sizeCard       = form.querySelector('#sizeCard');
    const sizeHints      = form.querySelector('#sizeHints');
    const wcHint         = form.querySelector('#wcHint');
    const imgHint        = form.querySelector('#imgHint');
    const adDetailsCard  = form.querySelector('#adDetailsCard');
    const locationCard   = form.querySelector('#locationCard');
    const criteriaCard   = form.querySelector('#criteriaCard');
    const criteriaBlocks = form.querySelector('#criteriaBlocks');
    const customerCard   = form.querySelector('#customerCard');
    const paymentCard    = form.querySelector('#paymentCard');
    const formActions    = form.querySelector('#formActions');
    const descTA         = form.querySelector('#descTA');
    const wcDisplay      = form.querySelector('#wcDisplay');
    const imagesHint     = form.querySelector('#imagesHint');
    const topAdToggle    = form.querySelector('#topAdToggle');
    const topAdHint      = form.querySelector('#topAdHint');
    const tintFieldWrap  = form.querySelector('#tintFieldWrap');
    const nicFrontWrap   = form.querySelector('#nicFrontFieldWrap');
    const nicBackWrap    = form.querySelector('#nicBackFieldWrap');
    const nicFrontInput  = form.querySelector('input[name="nic_front_image"]');
    const nicBackInput   = form.querySelector('input[name="nic_back_image"]');
    const paymentAmountInput = form.querySelector('input[name="payment_amount"]');

    /* ── State ───────────────────────────────────────────────────────── */
    let pendingCriterias = [];   // pre-loaded when category changes, rendered on size selection
    const METROMONIAL_KEYWORDS = {
        en: ['matrimonial'],
        si: ['මංගල යෝජනා']
    };

    /* ── Helpers ─────────────────────────────────────────────────────── */
    function lang() {
        return pubSel && pubSel.value === 'lahipita' ? 'si' : 'en';
    }

    function show(el) { if (el) el.style.display = ''; }
    function hide(el) { if (el) el.style.display = 'none'; }

    function resetSelect(sel, placeholder) {
        if (!sel) return;
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        sel.value = '';
    }

    function wordCount(text) {
        return text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
    }

    function trimToWordLimit(text, maxWords) {
        const normalized = String(text || '').replace(/\s+/g, ' ').trim();
        if (!normalized || maxWords <= 0) return normalized;

        const words = normalized.split(' ');
        if (words.length <= maxWords) return normalized;

        return words.slice(0, maxWords).join(' ');
    }

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function isMetromonialCategorySelected() {
        if (!catSel || !catSel.value || !catSel.options[catSel.selectedIndex]) {
            return false;
        }

        var selected = catSel.options[catSel.selectedIndex];
        var haystack = [
            selected.textContent || '',
            selected.dataset.en || '',
            selected.dataset.si || ''
        ].join(' ').toLowerCase();

        var activeKeywords = lang() === 'si'
            ? METROMONIAL_KEYWORDS.si
            : METROMONIAL_KEYWORDS.en;

        return activeKeywords.some(function (keyword) {
            return haystack.indexOf(keyword) !== -1;
        });
    }

    function toggleMetromonialFields() {
        var isMetromonial = isMetromonialCategorySelected();

        if (tintFieldWrap) {
            tintFieldWrap.style.display = isMetromonial ? '' : 'none';
        }
        if (nicFrontWrap) {
            nicFrontWrap.style.display = isMetromonial ? '' : 'none';
        }
        if (nicBackWrap) {
            nicBackWrap.style.display = isMetromonial ? '' : 'none';
        }

        if (nicFrontInput) {
            nicFrontInput.required = !!isMetromonial;
        }
        if (nicBackInput) {
            nicBackInput.required = !!isMetromonial;
        }

        if (!isMetromonial) {
            if (tintSel) {
                resetSelect(tintSel, 'No Tint');
            }
            if (nicFrontInput) {
                nicFrontInput.value = '';
                nicFrontInput.classList.remove('is-invalid');
            }
            if (nicBackInput) {
                nicBackInput.value = '';
                nicBackInput.classList.remove('is-invalid');
            }
        }
    }

    function getValidationFeedbackElement(field) {
        if (!field) return null;
        var parent = field.closest('.col-md-6, .col-12');
        if (!parent) return null;

        var existing = parent.querySelector('.invalid-feedback[data-dynamic-validation="1"]');
        if (existing) return existing;

        var feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.setAttribute('data-dynamic-validation', '1');
        feedback.style.display = 'none';
        parent.appendChild(feedback);
        return feedback;
    }

    function setFieldInvalid(field, message) {
        if (!field) return;
        field.classList.add('is-invalid');
        var feedback = getValidationFeedbackElement(field);
        if (feedback) {
            feedback.textContent = message || 'This field is required.';
            feedback.style.display = '';
        }
    }

    function clearFieldInvalid(field) {
        if (!field) return;
        field.classList.remove('is-invalid');
        var feedback = getValidationFeedbackElement(field);
        if (feedback) {
            feedback.textContent = '';
            feedback.style.display = 'none';
        }
    }

    function validateRequiredField(field, label) {
        if (!field || field.disabled) return true;

        var tagName = (field.tagName || '').toLowerCase();
        var type = (field.type || '').toLowerCase();
        var value = (field.value || '').trim();
        var valid = true;

        if (type === 'file') {
            valid = !!(field.files && field.files.length > 0);
        } else if (tagName === 'select') {
            valid = value !== '';
        } else {
            valid = value !== '';
        }

        if (valid && tagName === 'input' && type === 'email') {
            valid = field.checkValidity();
        }

        if (!valid) {
            setFieldInvalid(field, (label || 'This field') + ' is required.');
            return false;
        }

        clearFieldInvalid(field);
        return true;
    }

    function validateCreateForm() {
        var ok = true;
        var firstInvalid = null;

        var customerNameInput = form.querySelector('input[name="customer_name"]');
        var nicPassportInput = form.querySelector('input[name="nic_passport"]');
        var telephoneInput = form.querySelector('input[name="telephone"]');
        var emailInput = form.querySelector('input[name="email"]');
        var confirmEmailInput = form.querySelector('input[name="confirm_email"]');
        var addressInput = form.querySelector('input[name="address"]');
        var paymentStatusSelect = form.querySelector('select[name="payment_status"]');
        var paymentAmountInput = form.querySelector('input[name="payment_amount"]');

        var requiredFields = [
            { field: descTA, label: 'Description' },
            { field: publishInput, label: 'Publish Date' },
            { field: distSel, label: 'District' },
            { field: citySel, label: 'City' },
            { field: customerNameInput, label: 'Name' },
            { field: nicPassportInput, label: 'NIC / Passport' },
            { field: telephoneInput, label: 'Phone' },
            { field: emailInput, label: 'Email' },
            { field: confirmEmailInput, label: 'Confirm Email' },
            { field: addressInput, label: 'Address' },
            { field: paymentStatusSelect, label: 'Payment Status' },
            { field: paymentAmountInput, label: 'Amount (LKR)' }
        ];

        requiredFields.forEach(function (item) {
            if (!validateRequiredField(item.field, item.label)) {
                ok = false;
                if (!firstInvalid) firstInvalid = item.field;
            }
        });

        if (emailInput && confirmEmailInput && emailInput.value.trim() !== '' && confirmEmailInput.value.trim() !== '') {
            if (emailInput.value.trim().toLowerCase() !== confirmEmailInput.value.trim().toLowerCase()) {
                setFieldInvalid(confirmEmailInput, 'Confirm Email must match Email.');
                ok = false;
                if (!firstInvalid) firstInvalid = confirmEmailInput;
            }
        }

        if (isMetromonialCategorySelected()) {
            if (!validateRequiredField(nicFrontInput, 'NIC Front Photo')) {
                ok = false;
                if (!firstInvalid) firstInvalid = nicFrontInput;
            }
            if (!validateRequiredField(nicBackInput, 'NIC Back Photo')) {
                ok = false;
                if (!firstInvalid) firstInvalid = nicBackInput;
            }
        }

        if (firstInvalid && typeof firstInvalid.focus === 'function') {
            firstInvalid.focus();
        }

        return ok;
    }

    /* ── Language: filter & label category options ───────────────────── */
    function updateCategoryLabels() {
        const l = lang();
        var anyVisible = false;
        catSel.querySelectorAll('option[data-en]').forEach(function (opt) {
            var preferred = (opt.dataset[l] || '').trim();
            if (preferred === '') {
                opt.hidden   = true;
                opt.disabled = true;
            } else {
                opt.hidden   = false;
                opt.disabled = false;
                opt.textContent = preferred;
                anyVisible = true;
            }
        });
        // Reset if the currently selected option is now hidden
        if (catSel.value && catSel.options[catSel.selectedIndex] && catSel.options[catSel.selectedIndex].hidden) {
            catSel.value = '';
            loadTypes('');
        }
    }

    function updateLocationLabels() {
        const l = lang();
        // Filter district options by language
        if (distSel) {
            distSel.querySelectorAll('option[data-en]').forEach(function (opt) {
                var preferred = (opt.dataset[l] || '').trim();
                if (preferred === '') {
                    opt.hidden   = true;
                    opt.disabled = true;
                } else {
                    opt.hidden   = false;
                    opt.disabled = false;
                    opt.textContent = preferred;
                }
            });
            if (distSel.value && distSel.options[distSel.selectedIndex] && distSel.options[distSel.selectedIndex].hidden) {
                distSel.value = '';
            }
        }
        // City options are handled entirely by filterCities (language + district)
        filterCities();
    }

    function currentDescriptionRules() {
        const suffix = lang() === 'si' ? 'si' : 'en';

        return {
            maxWords: Number(generalSettings['max_words_' + suffix] || 0),
            freeWordLimit: Number(generalSettings['free_word_limit_' + suffix] || 0),
            additionalWordRate: Number(generalSettings['additional_word_rate_' + suffix] || 0),
        };
    }

    function currentTopAdRate() {
        const suffix = lang() === 'si' ? 'si' : 'en';
        return Number(generalSettings['top_ad_rate_' + suffix] || 0);
    }

    function updateTopAdHint() {
        if (!topAdHint) return;

        const rate = Math.max(0, currentTopAdRate());
        const enabled = !!(topAdToggle && topAdToggle.checked);

        if (!enabled) {
            topAdHint.textContent = 'Enable this if the ad should run in the top placement.';
            return;
        }

        topAdHint.textContent = rate > 0
            ? ('Top ad placement adds LKR ' + rate.toFixed(2) + ' to the calculated amount.')
            : 'Top ad placement is enabled.';
    }

    /* ── Live word counter ────────────────────────────────────────────── */
    function updateWordCount() {
        if (!descTA) return;

        const rules = currentDescriptionRules();
        const maxWords = Math.max(0, rules.maxWords);

        if (maxWords > 0) {
            const trimmed = trimToWordLimit(descTA.value || '', maxWords);
            if (trimmed !== (descTA.value || '')) {
                descTA.value = trimmed;
            }
        }

        const words = wordCount(descTA.value || '');
        const freeWords = Math.max(0, rules.freeWordLimit);
        const rate = Math.max(0, rules.additionalWordRate);
        const extraWords = Math.max(0, words - freeWords);
        const extraCost = extraWords * rate;
        const overLimit = maxWords > 0 && words > maxWords;

        if (wcDisplay) {
            wcDisplay.textContent = maxWords > 0
                ? (words + ' / ' + maxWords + ' words')
                : (words + ' words');
            wcDisplay.classList.toggle('wc-badge', true);
            wcDisplay.classList.toggle('over', overLimit);
        }

        const lines = [
            'Free words: ' + freeWords,
            'Extra words: ' + extraWords,
            'Extra cost: LKR ' + extraCost.toFixed(2),
        ];

        if (descTA.value.trim() === '') {
            lines.unshift('Start typing the description to calculate words and extra cost.');
        }

        if (overLimit) {
            lines.push('Maximum allowed words exceeded. Please shorten the description.');
            descTA.setCustomValidity('Description cannot exceed ' + maxWords + ' words for the selected publication.');
        } else {
            descTA.setCustomValidity('');
        }

        const descHint = form.querySelector('#descHint');
        if (descHint) {
            descHint.textContent = lines.join(' · ');
            descHint.style.display = '';
            descHint.classList.toggle('text-danger', overLimit);
        }
    }

    /* ── City filter by district + language ───────────────────────────── */
    function filterCities() {
        if (!distSel || !citySel) return;
        const selDist = distSel.value;
        const l = lang();

        if (!selDist) {
            // No district chosen — disable city entirely
            citySel.disabled = true;
            citySel.value = '';
            citySel.querySelectorAll('option:not([value=""])').forEach(function (opt) {
                opt.hidden = true; opt.disabled = true;
            });
            citySel.options[0].textContent = 'Select District first';
            return;
        }

        citySel.disabled = false;
        citySel.options[0].textContent = 'Select City';

        citySel.querySelectorAll('option').forEach(function (opt) {
            if (!opt.value) return;
            var hasLangLabel  = (opt.dataset[l] || '').trim() !== '';
            var matchDistrict = opt.dataset.district === selDist;
            var match = hasLangLabel && matchDistrict;
            opt.hidden   = !match;
            opt.disabled = !match;
            if (match) opt.textContent = opt.dataset[l].trim();
        });

        if (citySel.value && citySel.options[citySel.selectedIndex] && citySel.options[citySel.selectedIndex].disabled) {
            citySel.value = '';
        }
    }

    /* ── Build criteria HTML from JSON ───────────────────────────────── */
    function buildCriteriaHtml(criterias) {
        if (!criterias || criterias.length === 0) return '';
        return criterias.map(function (c) {
            var field = '';
            if (c.field_type === 'text') {
                field = '<input type="text" name="criteria[' + c.id + ']" class="form-control" />';
            } else if (c.field_type === 'number') {
                field = '<input type="number" name="criteria[' + c.id + ']" class="form-control" />';
            } else if (c.field_type === 'image') {
                field = '<input type="file" name="criteria_image[' + c.id + ']" class="form-control" accept="image/*" />';
            } else if (c.field_type === 'dropdown') {
                var opts = (c.options || []).map(function (o) {
                    return '<option value="' + escHtml(o.label) + '">' + escHtml(o.label) + '</option>';
                }).join('');
                field = '<select name="criteria[' + c.id + ']" class="form-select">'
                    + '<option value="">-- Select --</option>' + opts + '</select>';
            } else if (c.field_type === 'textarea') {
                field = '<textarea name="criteria[' + c.id + ']" class="form-control" rows="3"></textarea>';
            } else if (c.field_type === 'radio') {
                var opts = (c.options || []).map(function (o) {
                    return '<div class="form-check form-check-inline">'
                        + '<input class="form-check-input" type="radio" name="criteria[' + c.id + ']"'
                        + ' id="crit_' + c.id + '_' + o.id + '" value="' + escHtml(o.label) + '">'
                        + '<label class="form-check-label" for="crit_' + c.id + '_' + o.id + '">'
                        + escHtml(o.label) + '</label></div>';
                }).join('');
                field = '<div class="d-flex flex-wrap gap-3">' + opts + '</div>';
            } else {
                field = '<input type="text" name="criteria[' + c.id + ']" class="form-control" />';
            }
            return '<div class="col-12"><div class="criteria-block">'
                + '<label class="form-label">' + escHtml(c.label) + '</label>'
                + field + '</div></div>';
        }).join('');
    }

    /* ── Reveal / hide lower sections ───────────────────────────────── */
    function revealFromSize(sizeSelected) {
        if (sizeSelected) {
            show(adDetailsCard);
            show(locationCard);
            if (pendingCriterias.length > 0) {
                criteriaBlocks.innerHTML = buildCriteriaHtml(pendingCriterias);
                show(criteriaCard);
            } else {
                criteriaBlocks.innerHTML = '';
                hide(criteriaCard);
            }
            show(customerCard);
            show(paymentCard);
            show(formActions);
            calculateAndUpdatePrice();
        } else {
            hide(adDetailsCard);
            hide(locationCard);
            hide(criteriaCard);
            hide(customerCard);
            hide(paymentCard);
            hide(formActions);
        }
    }

    /* ── AJAX: calculate and auto-fill advertisement price ──────────── */
    function calculateAndUpdatePrice() {
        if (!paymentAmountInput || !paymentAmountInput.form) return;

        var formData = new FormData(paymentAmountInput.form);
        
        fetch(@json(url('/calculate-ad-price')), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && typeof data.total !== 'undefined') {
                    paymentAmountInput.value = data.total.toFixed(2);
                    clearFieldInvalid(paymentAmountInput);
                }
            })
            .catch(function (err) {
                console.error('Price calculation failed:', err);
            });
    }

    /* ── AJAX: load types for a category ─────────────────────────────── */
    function loadTypes(categoryId) {
        resetSelect(typeSel, 'Select Type');
        hide(typeCard);
        resetSelect(sizeSel, 'Select Size');
        hide(sizeCard);
        resetSelect(tintSel, 'No Tint');
        hide(sizeHints);
        revealFromSize(false);
            pendingCriterias = [];

        toggleMetromonialFields();

        if (!categoryId) return;

        var l = lang();

        fetch(@json(url('/adtypes/by-category')) + '/' + categoryId + '?lang=' + l, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (types) {
                types.forEach(function (t) {
                    var opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.label;
                    typeSel.appendChild(opt);
                });
                show(typeCard);
            })
            .catch(function () { show(typeCard); });

        if (isMetromonialCategorySelected()) {
            loadTints(categoryId);
        }

        // Pre-fetch criterias for this category
        fetch(@json(url('/adcriterias/by-category')) + '/' + categoryId + '?lang=' + l, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) { pendingCriterias = data; })
            .catch(function () { pendingCriterias = []; });
    }

    /* ── AJAX: load tints for a category ────────────────────────────── */
    function loadTints(categoryId) {
        resetSelect(tintSel, 'No Tint');
        if (!categoryId || !tintSel) return;

        var l = lang();
        var oldTintId = (tintSel.dataset.old || '').toString();

        fetch(@json(url('/tints/by-category')) + '/' + categoryId + '?lang=' + l, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (tints) {
                tints.forEach(function (t) {
                    var opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.label;

                    if (oldTintId && String(t.id) === String(oldTintId)) {
                        opt.selected = true;
                    }

                    tintSel.appendChild(opt);
                });

                // clear one-time old value once consumed
                tintSel.dataset.old = '';
            })
            .catch(function () {});
    }

    /* ── AJAX: load sizes for a type ─────────────────────────────────── */
    function loadSizes(typeId) {
        resetSelect(sizeSel, 'Select Size');
        hide(sizeCard);
        hide(sizeHints);
        revealFromSize(false);

        if (!typeId) return;

        var l = lang();
        fetch(@json(url('/adsizes/by-type')) + '/' + typeId + '?lang=' + l, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (sizes) {
                sizes.forEach(function (s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.label;
                    
                    sizeSel.appendChild(opt);
                });
                show(sizeCard);
            })
            .catch(function () { show(sizeCard); });
    }

    /* ── Apply hints from selected size ──────────────────────────────── */
    function applySize() {
        var opt = sizeSel.options[sizeSel.selectedIndex];
        if (!opt || !opt.value) {
            revealFromSize(false);
            return;
        }

        // Word count / max images no longer used — just reveal dependent sections
        revealFromSize(true);
        updateLocationLabels();
        filterCities();
    }

    /* ── Publication change: re-label everything, re-fetch with new lang ─ */
    function onPublicationChange() {
        refreshPublishDateConstraints();
        updateCategoryLabels();
        toggleMetromonialFields();
        updateLocationLabels();
        updateWordCount();
        updateTopAdHint();

        var catId  = catSel.value;
        var typeId = typeSel.value;
        var l = lang();

        if (catId) {
            var curType = typeSel.value;
            var curTint = tintSel ? tintSel.value : '';
            fetch(@json(url('/adtypes/by-category')) + '/' + catId + '?lang=' + l, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json(); })
                .then(function (types) {
                    resetSelect(typeSel, 'Select Type');
                    types.forEach(function (t) {
                        var opt = document.createElement('option');
                        opt.value = t.id;
                        opt.textContent = t.label;
                        if (String(t.id) === String(curType)) opt.selected = true;
                        typeSel.appendChild(opt);
                    });
                });

            if (tintSel && isMetromonialCategorySelected()) {
                tintSel.dataset.old = curTint;
                loadTints(catId);
            }

            fetch(@json(url('/adcriterias/by-category')) + '/' + catId + '?lang=' + l, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    pendingCriterias = data;
                    if (criteriaCard.style.display !== 'none') {
                        criteriaBlocks.innerHTML = buildCriteriaHtml(pendingCriterias);
                    }
                })
                .catch(function () {});
        }

        if (typeId) {
            var curSize = sizeSel.value;
            fetch(@json(url('/adsizes/by-type')) + '/' + typeId + '?lang=' + l, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json(); })
                .then(function (sizes) {
                    resetSelect(sizeSel, 'Select Size');
                    sizes.forEach(function (s) {
                        var opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.label;
                        
                        if (String(s.id) === String(curSize)) opt.selected = true;
                        sizeSel.appendChild(opt);
                    });
                });
        }
    }

    /* ── Event listeners ─────────────────────────────────────────────── */
    pubSel  && pubSel.addEventListener('change',  onPublicationChange);
    catSel  && catSel.addEventListener('change',  function () {
        toggleMetromonialFields();
        loadTypes(catSel.value);
    });
    typeSel && typeSel.addEventListener('change', function () {
        loadSizes(typeSel.value);
        calculateAndUpdatePrice();
    });
    sizeSel && sizeSel.addEventListener('change', function () {
        applySize();
        calculateAndUpdatePrice();
    });
    tintSel && tintSel.addEventListener('change', calculateAndUpdatePrice);
    distSel && distSel.addEventListener('change', filterCities);
    descTA  && descTA.addEventListener('input',   function () {
        updateWordCount();
        calculateAndUpdatePrice();
    });
    topAdToggle && topAdToggle.addEventListener('change', function () {
        updateTopAdHint();
        calculateAndUpdatePrice();
    });
    form.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('input', function () { clearFieldInvalid(el); });
        el.addEventListener('change', function () { clearFieldInvalid(el); });
    });
    descTA  && descTA.addEventListener('paste', function (e) {
        if (!descTA) return;

        const rules = currentDescriptionRules();
        const maxWords = Math.max(0, Number(rules.maxWords || 0));
        if (maxWords <= 0) return;

        const clipboardText = (e.clipboardData || window.clipboardData)?.getData('text') || '';
        const currentValue = descTA.value || '';
        const selectionStart = descTA.selectionStart ?? currentValue.length;
        const selectionEnd = descTA.selectionEnd ?? currentValue.length;
        const nextValue = currentValue.slice(0, selectionStart) + clipboardText + currentValue.slice(selectionEnd);

        const trimmed = trimToWordLimit(nextValue, maxWords);
        if (trimmed !== nextValue) {
            e.preventDefault();
            descTA.value = trimmed;
            descTA.setSelectionRange(descTA.value.length, descTA.value.length);
            updateWordCount();
        }
    });
    publishInput && publishInput.addEventListener('change', function () { validatePublishDateCutoff(false); });

    form.addEventListener('submit', function (e) {
        var isPublishDateValid = validatePublishDateCutoff(true);
        var isCreateFormValid = validateCreateForm();
        if (!isPublishDateValid || !isCreateFormValid) {
            e.preventDefault();
        }
    });

    /* ── Init ────────────────────────────────────────────────────────── */
    updateCategoryLabels();
    toggleMetromonialFields();
    updateWordCount();
    updateTopAdHint();

    @if($autoOpen)
    const offcanvasEl = form.closest('.offcanvas');
    if (offcanvasEl && window.bootstrap && bootstrap.Offcanvas) {
        bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
    }
    @endif
})();
</script>
@endpush

