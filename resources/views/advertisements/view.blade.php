@extends('layouts.app')

@section('content')

@php
    $approvedReadOnly = $approvedReadOnly ?? false;
@endphp

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        .advertisement-print-area,
        .advertisement-print-area * {
            visibility: visible;
        }

        .advertisement-print-area {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            margin: 0;
        }

        .advertisement-print-area .card {
            border: 0;
            box-shadow: none;
        }

        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

<div class="container mt-4 advertisement-print-area">
    <h2>Advertisement Details</h2>


<div class="card">
    <div class="card-body">

        <!-- Title -->
        <h4>Advertisement #{{ $ad->id }}</h4>

        <!-- Ad Info -->
        <p><strong>Description:</strong> {{ $ad->advertisement_description }}</p>
        <hr>

        <p><strong>Publication:</strong> {{ $ad->publication }}</p>
        <p><strong>Publish Date:</strong> {{ $ad->publish_date }}</p>
        <p><strong>Advertisement Type:</strong> {{ $ad->advertisement_type_name ?? '—' }}</p>
        <p><strong>Advertisement Size:</strong> {{ $ad->advertisement_size_name ?? '—' }}</p>
        <p><strong>Tint:</strong> {{ $ad->advertisement_tint_name ?: 'No Tint' }}</p>
        <p><strong>Top Ad:</strong> {{ ($ad->top_ad ?? false) ? 'Yes' : 'No' }}</p>
        <p><strong>Web Combined Ad:</strong> {{ (int)($ad->web_combined_ad_hitadlk ?? 0) === 1 ? 'Yes' : 'No' }}</p>
        <p><strong>Print on Hitad Paper:</strong>{{ (int)($ad->print_combined_ad_hitadprint ?? 0) === 1 ? 'Yes' : 'No' }}</p>

        <hr>

        <!-- Customer Info -->
        <h5>Customer Info</h5>
        <p><strong>Name:</strong> {{ $ad->customer_name }}</p>
        <p><strong>Address:</strong> {{ $ad->address }}</p>
        <p><strong>Telephone:</strong> {{ $ad->telephone }}</p>
        <p><strong>Email:</strong> {{ $ad->email }}</p>
        <p><strong>NIC/Passport:</strong> {{ $ad->nic_passport }}</p>

        <!-- NIC Photos -->
        @if($ad->nic_front_img_url || $ad->nic_back_img_url)
            <div class="row mt-2">
                @if($ad->nic_front_img_url)
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>NIC Front</strong></p>
                        <a href="{{ $ad->nic_front_img_url }}" target="_blank">
                            <img src="{{ $ad->nic_front_img_url }}" alt="NIC Front" class="img-fluid rounded border" style="max-height: 200px;">
                        </a>
                        @if(!$approvedReadOnly)
                        <div class="mt-1">
                            <a href="{{ $ad->nic_front_img_url }}" download class="btn btn-sm btn-outline-secondary no-print">
                                <i class="bx bx-download"></i> Download
                            </a>
                        </div>
                        @endif
                    </div>
                @endif

                @if($ad->nic_back_img_url)
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>NIC Back</strong></p>
                        <a href="{{ $ad->nic_back_img_url }}" target="_blank">
                            <img src="{{ $ad->nic_back_img_url }}" alt="NIC Back" class="img-fluid rounded border" style="max-height: 200px;">
                        </a>
                        @if(!$approvedReadOnly)
                        <div class="mt-1">
                            <a
                                href="{{ $ad->nic_back_img_url }}"
                                download
                                class="btn btn-sm btn-outline-secondary no-print"
                            >
                                <i class="bx bx-download"></i>
                                Download
                            </a>
                        </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <hr>

        <!-- Location -->
        <h5>Location</h5>
        <p><strong>Category:</strong> {{ $ad->category_name }}</p>
        <p><strong>District:</strong> {{ $ad->district_name }}</p>
        <p><strong>City:</strong> {{ $ad->city_name }}</p>

        <hr>

        <!-- Payment Section -->
        <h5>Payment Details</h5>

        @if($ad->payment_method)

            <p><strong>Payment Method:</strong> {{ $ad->payment_method }}</p>

            <p><strong>Amount:</strong> Rs. {{ number_format($ad->amount, 2) }}</p>

            <p><strong>Payment Date:</strong> {{ $ad->payment_date }}</p>

            <p><strong>Status:</strong>
                @include('partials.payment-status-badge', ['status' => $ad->payment_status])
            </p>

        @else

            <p class="text-danger"><strong>No Payment Found</strong></p>

        @endif

        <hr>

        <!-- Criteria -->
        @if(isset($criterias) && $criterias->count() > 0)
            <h5>Criteria</h5>
            <div class="row">
                @foreach($criterias as $crit)
                    @php
                        // Use Sinhala labels for Lahipita publication, otherwise English
                        $critLabel = trim((($ad->publication ?? '') === 'lahipita') ? ($crit->advertisement_criteria_name_si ?? '') : ($crit->advertisement_criteria_name_en ?? ''));
                        $value = $criteriaValues[$crit->id] ?? null;
                    @endphp

                    @if($critLabel !== '')
                        <div class="col-md-6 mb-2">
                            <p><strong>{{ $critLabel }}:</strong> {{ !empty($value) ? $value : '—' }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            <hr>
        @endif

        <!-- Uploaded Advertisement Images -->
        @if(isset($images) && $images->count() > 0)
            <h5>Uploaded Images</h5>
            <div class="row">
                @foreach($images as $image)
                    @if($image->display_url)
                        <div class="col-md-3 mb-3">
                            <a href="{{ $image->display_url }}" target="_blank">
                                <img src="{{ $image->display_url }}" alt="Advertisement Image" class="img-fluid rounded border" style="max-height: 180px;">
                            </a>
                            <div class="mt-1">
                                <!-- <a href="{{ $image->display_url }}" download class="btn btn-sm btn-outline-secondary">
                                    <i class="bx bx-download"></i> Download
                                </a> -->
                                @if(!$approvedReadOnly)
                            <div class="mt-1">
                                <a
                                    href="{{ $image->display_url }}"
                                    download
                                    class="btn btn-sm btn-outline-secondary no-print"
                                >
                                    <i class="bx bx-download"></i>
                                    Download
                                </a>
                            </div>
                        @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <p class="text-muted">No images uploaded.</p>
        @endif

    </div>
</div>

<div class="mt-3 d-flex gap-2 no-print">
    <button
        type="button"
        class="btn btn-info"
        onclick="window.print();"
    >
        <i class="bx bx-printer"></i>
        Print Advertisement
    </button>

    @if(!$approvedReadOnly)
        <a
            href="{{ url('/advertisements/' . $ad->id . '/download') }}"
            class="btn btn-success"
        >
            <i class="bx bx-download"></i>
            Download Advertisement
        </a>
        @if($ad->email)
            <form
                method="POST"
                action="{{ url('/advertisements/' . $ad->id . '/send-link-email') }}"
                style="display:inline;"
            >
                @csrf
                <button
                    type="submit"
                    class="btn btn-primary"
                    onclick="return confirm('Send payment invoice to customer?');"
                >
                    <i class="bx bx-send"></i>
                    Send Email to Customer
                </button>
            </form>
        @endif

        <a
            href="{{ url('/advertisements') }}"
            class="btn btn-secondary"
        >

            Back to All Ads

        </a>


@else

    @php

        $defaultBackUrl = $ad->publication === 'lahipita'
            ? url('/advertisements/lahipita/approved')
            : url('/advertisements/hitad/approved');

        $backUrl = $readOnlyBackUrl ?? $defaultBackUrl;

        $backLabel = $readOnlyBackLabel ?? 'Back to Approved Ads';

    @endphp


    <a
        href="{{ $backUrl }}"
        class="btn btn-secondary"
    >

        <i class="bx bx-arrow-back"></i>

        {{ $backLabel }}

    </a>

@endif

</div>


</div>
@endsection