@extends('layouts.app')

@section('title', 'Translate Advertisement')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">Translate Advertisement #{{ $advertisement->id }}</h4>
            <p class="text-muted mb-0">Enter the translated description and its reference number.</p>
        </div>
        <a href="{{ route('translation.queue') }}" class="btn btn-outline-secondary">Back to Queue</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label mb-0" for="retypedTranslationSource">Retyped Description (source)</label>
                    <button type="button" id="copyRetypedTranslationSource" class="btn btn-sm btn-outline-secondary">Copy retyped</button>
                </div>
                <textarea id="retypedTranslationSource" class="form-control" rows="6" readonly>{{ $advertisement->retyped_advertisement_description }}</textarea>
            </div>

            <form method="POST" action="{{ route('translation.queue.complete', $advertisement->id) }}">
                @csrf
                <div class="mb-3">
                    <label for="translatedDescription" class="form-label">Translated Description</label>
                    <textarea name="translated_advertisement_description" id="translatedDescription" class="form-control @error('translated_advertisement_description') is-invalid @enderror" rows="7" required>{{ old('translated_advertisement_description', $advertisement->translated_advertisement_description) }}</textarea>
                    @error('translated_advertisement_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="translatedReferenceNumber" class="form-label">Translated Reference Number</label>
                    <input type="text" name="translated_reference_number" id="translatedReferenceNumber" class="form-control @error('translated_reference_number') is-invalid @enderror" value="{{ old('translated_reference_number', $advertisement->translated_reference_number) }}" maxlength="255" required>
                    @error('translated_reference_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">Complete Translation & Approve Advertisement</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var copyButton = document.getElementById('copyRetypedTranslationSource');
    var sourceDescription = document.getElementById('retypedTranslationSource');
    if (!copyButton || !sourceDescription) return;

    copyButton.addEventListener('click', function () {
        function showCopied() {
            copyButton.textContent = 'Copied!';
            window.setTimeout(function () {
                copyButton.textContent = 'Copy retyped';
            }, 1500);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(sourceDescription.value || '').then(showCopied);
            return;
        }

        sourceDescription.select();
        document.execCommand('copy');
        sourceDescription.setSelectionRange(0, 0);
        showCopied();
    });
});
</script>
@endsection
