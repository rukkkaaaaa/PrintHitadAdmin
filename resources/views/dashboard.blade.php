@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
  <div class="col-lg-12 mb-4 order-0">
    <div class="card">
      <div class="d-flex align-items-end row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary">Welcome, {{ $user['name'] }} 🎉</h5>
            <p class="mb-4">This is PrintHitAd admin dashboard.</p>
            <a href="{{ url('/advertisements') }}" class="btn btn-sm btn-outline-primary">View All Print Ads</a>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="/assets/img/illustrations/man-with-laptop-light.png" height="140" alt="Admin" />
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Statistic Cards --}}
  <div class="col-lg-4 col-md-6 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <h6 class="card-title">Total Customers</h6>
        <h3 class="text-primary">{{ $customerCount }}</h3>
      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <h6 class="card-title">Total Admins</h6>
        <h3 class="text-info">{{ $adminCount }}</h3>
      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <h6 class="card-title">Total Advertisements</h6>
        <h3 class="text-success">{{ $adCount }}</h3>
      </div>
    </div>
  </div>
</div>
@endsection
