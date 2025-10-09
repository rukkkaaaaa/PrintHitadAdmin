@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="row">
    <div class="col-lg-8 mb-4 order-0">
      <div class="card">
        <div class="d-flex align-items-end row">
          <div class="col-sm-7">
            <div class="card-body">
              <h5 class="card-title text-primary">Welcome, {{ $user['name'] }} 🎉</h5>
              <p class="mb-4">This is your admin dashboard. Start managing your content here.</p>
              <a href="javascript:;" class="btn btn-sm btn-outline-primary">Get Started</a>
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
  </div>
@endsection
