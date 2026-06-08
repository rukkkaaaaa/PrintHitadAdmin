@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="mb-1">General Settings</h4>
            <p class="text-muted mb-0">Configure limits and pricing values used for print advertisement calculations.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!empty($schemaMissing))
        <div class="alert alert-warning" role="alert">
            General settings table is not available yet. You can view default values, but saving requires the <code>general_settings</code> table.
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ url('/general-settings') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label" for="max_words_en">Max Words (English)</label>
                        <input type="number" min="0" step="1" class="form-control" id="max_words_en" name="max_words_en" value="{{ old('max_words_en', $settings['max_words_en']) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="max_words_si">Max Words (Sinhala)</label>
                        <input type="number" min="0" step="1" class="form-control" id="max_words_si" name="max_words_si" value="{{ old('max_words_si', $settings['max_words_si']) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="additional_word_rate_en">Additional Word Rate (English)</label>
                        <input type="number" min="0" step="0.01" class="form-control" id="additional_word_rate_en" name="additional_word_rate_en" value="{{ old('additional_word_rate_en', number_format((float) $settings['additional_word_rate_en'], 2, '.', '')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="additional_word_rate_si">Additional Word Rate (Sinhala)</label>
                        <input type="number" min="0" step="0.01" class="form-control" id="additional_word_rate_si" name="additional_word_rate_si" value="{{ old('additional_word_rate_si', number_format((float) $settings['additional_word_rate_si'], 2, '.', '')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="free_word_limit_en">Free Word Limit (English)</label>
                        <input type="number" min="0" step="1" class="form-control" id="free_word_limit_en" name="free_word_limit_en" value="{{ old('free_word_limit_en', $settings['free_word_limit_en']) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="free_word_limit_si">Free Word Limit (Sinhala)</label>
                        <input type="number" min="0" step="1" class="form-control" id="free_word_limit_si" name="free_word_limit_si" value="{{ old('free_word_limit_si', $settings['free_word_limit_si']) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="top_ad_rate_en">Top Ad Rate (English)</label>
                        <input type="number" min="0" step="0.01" class="form-control" id="top_ad_rate_en" name="top_ad_rate_en" value="{{ old('top_ad_rate_en', number_format((float) $settings['top_ad_rate_en'], 2, '.', '')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="top_ad_rate_si">Top Ad Rate (Sinhala)</label>
                        <input type="number" min="0" step="0.01" class="form-control" id="top_ad_rate_si" name="top_ad_rate_si" value="{{ old('top_ad_rate_si', number_format((float) $settings['top_ad_rate_si'], 2, '.', '')) }}" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">Save General Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
