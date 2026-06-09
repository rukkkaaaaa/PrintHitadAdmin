@php
    $offcanvasId = $offcanvasId ?? 'advertisementCreateDrawer';
    $autoOpen = $autoOpen ?? false;
@endphp

<div class="offcanvas offcanvas-end" tabindex="-1" id="{{ $offcanvasId }}" aria-labelledby="{{ $offcanvasId }}Label" style="width: min(920px, 100vw);">
    <div class="offcanvas-header border-bottom bg-white">
        <div>
            <h5 class="offcanvas-title mb-1" id="{{ $offcanvasId }}Label">Create Advertisement</h5>
            <small class="text-muted">Choose HitAd or Lahipita first, then the labels and options will follow automatically.</small>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">
        @include('advertisements._form', [
            'formId' => $offcanvasId . 'Form',
            'action' => url('/advertisements/store'),
            'submitLabel' => 'Save Advertisement',
            'publication' => old('publication', 'hitad_print'),
            'autoOpen' => $autoOpen,
            'categories' => $categories ?? collect(),
            'districts' => $districts ?? collect(),
            'cities' => $cities ?? collect(),
            'criterias' => $criterias ?? collect(),
            'criteriaOptions' => $criteriaOptions ?? collect(),
        ])
    </div>
</div>
