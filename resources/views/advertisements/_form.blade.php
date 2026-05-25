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
                    <small class="help-note">Only Sundays are selectable.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label d-block">Web Combined Ad</label>
                    <select name="web_combined_ad" class="form-select">
                        <option value="0" {{ old('web_combined_ad', 0) == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('web_combined_ad') == 1 ? 'selected' : '' }}>Yes</option>
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
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Email</label>
                    <input type="email" name="confirm_email" class="form-control" value="{{ old('confirm_email') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address') }}" required>
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
                    <select name="payment_status" class="form-select">
                        <option value="pending"   {{ old('payment_status', 'pending') === 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ old('payment_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed"    {{ old('payment_status') === 'failed'    ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Amount (LKR)</label>
                    <input type="number" name="payment_amount" class="form-control" min="0" step="0.01"
                           value="{{ old('payment_amount') }}" placeholder="0.00">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Date</label>
                    <input type="text" name="payment_date" id="paymentDateInput" class="form-control"
                           value="{{ old('payment_date') }}" placeholder="Select date" autocomplete="off">
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

    /* ── Flatpickr: Sundays only ─────────────────────────────────────── */
    var publishInput = form.querySelector('#publishDateInput');
    if (publishInput) {
        flatpickr(publishInput, {
            dateFormat: 'Y-m-d',
            disableMobile: true,
            minDate: 'today',
            disable: [
                function (date) { return date.getDay() !== 0; }
            ]
        });
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

    /* ── State ───────────────────────────────────────────────────────── */
    // word count / max images feature removed
    let pendingCriterias = [];   // pre-loaded when category changes, rendered on size selection

    /* ── Helpers ─────────────────────────────────────────────────────── */
    function lang() {
        return pubSel && pubSel.value === 'lahipita' ? 'si' : 'en';
    }

    function show(el) { if (el) el.style.display = ''; }
    function hide(el) { if (el) el.style.display = 'none'; }

    function resetSelect(sel, placeholder) {
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        sel.value = '';
    }

    function wordCount(text) {
        return text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
    }

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
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

    /* ── Live word counter ────────────────────────────────────────────── */
    function updateWordCount() {
        // word count display removed
        return;
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
            if (c.field_type === 'textarea') {
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
                var opts = (c.options || []).map(function (o) {
                    return '<option value="' + escHtml(o.label) + '">' + escHtml(o.label) + '</option>';
                }).join('');
                field = '<select name="criteria[' + c.id + ']" class="form-select">'
                    + '<option value="">-- Select --</option>' + opts + '</select>';
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
        } else {
            hide(adDetailsCard);
            hide(locationCard);
            hide(criteriaCard);
            hide(customerCard);
            hide(paymentCard);
            hide(formActions);
        }
    }

    /* ── AJAX: load types for a category ─────────────────────────────── */
    function loadTypes(categoryId) {
        resetSelect(typeSel, 'Select Type');
        hide(typeCard);
        resetSelect(sizeSel, 'Select Size');
        hide(sizeCard);
        hide(sizeHints);
        revealFromSize(false);
            pendingCriterias = [];

        if (!categoryId) return;

        var l = lang();

        fetch('/adtypes/by-category/' + categoryId + '?lang=' + l)
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

        // Pre-fetch criterias for this category
        fetch('/adcriterias/by-category/' + categoryId + '?lang=' + l)
            .then(function (r) { return r.json(); })
            .then(function (data) { pendingCriterias = data; })
            .catch(function () { pendingCriterias = []; });
    }

    /* ── AJAX: load sizes for a type ─────────────────────────────────── */
    function loadSizes(typeId) {
        resetSelect(sizeSel, 'Select Size');
        hide(sizeCard);
        hide(sizeHints);
        revealFromSize(false);
            pendingCriterias = [];

        if (!typeId) return;

        var l = lang();
        fetch('/adsizes/by-type/' + typeId + '?lang=' + l)
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
        updateCategoryLabels();
        updateLocationLabels();

        var catId  = catSel.value;
        var typeId = typeSel.value;
        var l = lang();

        if (catId) {
            var curType = typeSel.value;
            fetch('/adtypes/by-category/' + catId + '?lang=' + l)
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

            fetch('/adcriterias/by-category/' + catId + '?lang=' + l)
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
            fetch('/adsizes/by-type/' + typeId + '?lang=' + l)
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
    catSel  && catSel.addEventListener('change',  function () { loadTypes(catSel.value); });
    typeSel && typeSel.addEventListener('change', function () { loadSizes(typeSel.value); });
    sizeSel && sizeSel.addEventListener('change', applySize);
    distSel && distSel.addEventListener('change', filterCities);
    descTA  && descTA.addEventListener('input',   updateWordCount);

    /* ── Init ────────────────────────────────────────────────────────── */
    updateCategoryLabels();

    @if($autoOpen)
    const offcanvasEl = form.closest('.offcanvas');
    if (offcanvasEl && window.bootstrap && bootstrap.Offcanvas) {
        bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
    }
    @endif
})();
</script>
@endpush

