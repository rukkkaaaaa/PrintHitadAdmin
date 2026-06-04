@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="mb-1">Publication Cutoff Settings</h4>
            <p class="text-muted mb-0">Configure until when ads can be published for each Sunday print edition.</p>
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
            Publication deadline table is not available yet. The page is open using default values, but saving changes requires running migrations first.
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ url('/publication-deadlines') }}" method="POST">
                @csrf

                @php
                    $hitad = $deadlines['hitad_print'] ?? ['cutoff_day_of_week' => 5, 'cutoff_time' => '18:00:00'];
                    $lahipita = $deadlines['lahipita'] ?? ['cutoff_day_of_week' => 2, 'cutoff_time' => '18:00:00'];
                @endphp

                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="mb-3">HitAd (Sunday publication)</h6>
                        <div class="mb-3">
                            <label class="form-label">Cutoff Day</label>
                            <select name="hitad_cutoff_day_of_week" class="form-select" required>
                                @foreach($weekDays as $dayIndex => $dayLabel)
                                    <option value="{{ $dayIndex }}"
                                        {{ (int) old('hitad_cutoff_day_of_week', $hitad['cutoff_day_of_week']) === (int) $dayIndex ? 'selected' : '' }}>
                                        {{ $dayLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cutoff Time</label>
                            <input
                                type="time"
                                name="hitad_cutoff_time"
                                class="form-control"
                                value="{{ old('hitad_cutoff_time', \Illuminate\Support\Carbon::createFromFormat('H:i:s', $hitad['cutoff_time'])->format('H:i')) }}"
                                required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="mb-3">Lahipita (Sunday publication)</h6>
                        <div class="mb-3">
                            <label class="form-label">Cutoff Day</label>
                            <select name="lahipita_cutoff_day_of_week" class="form-select" required>
                                @foreach($weekDays as $dayIndex => $dayLabel)
                                    <option value="{{ $dayIndex }}"
                                        {{ (int) old('lahipita_cutoff_day_of_week', $lahipita['cutoff_day_of_week']) === (int) $dayIndex ? 'selected' : '' }}>
                                        {{ $dayLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cutoff Time</label>
                            <input
                                type="time"
                                name="lahipita_cutoff_time"
                                class="form-control"
                                value="{{ old('lahipita_cutoff_time', \Illuminate\Support\Carbon::createFromFormat('H:i:s', $lahipita['cutoff_time'])->format('H:i')) }}"
                                required>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-primary">Save Cutoff Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
