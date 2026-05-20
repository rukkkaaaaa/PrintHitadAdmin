@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">Add Advertisement</h2>
            <div class="text-muted">Use this dedicated page to create a new ad for HitAd or Lahipita.</div>
        </div>
        <a href="{{ url('/advertisements') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back"></i> Back to Ads
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @include('advertisements._form', [
                'formId' => 'advertisementCreateForm',
                'action' => url('/advertisements/store'),
                'submitLabel' => 'Save Advertisement',
                'publication' => old('publication', 'hitad_print'),
                'categories' => $categories ?? collect(),
                'districts' => $districts ?? collect(),
                'cities' => $cities ?? collect(),
                'criterias' => $criterias ?? collect(),
                'criteriaOptions' => $criteriaOptions ?? collect(),
            ])
        </div>
    </div>
</div>
@endsection
