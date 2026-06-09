{{-- Reusable filters partial. Expects $action (URL) --}}
@php
    $action = $action ?? url()->current();
@endphp
<form action="{{ $action }}" method="GET">
    <div class="row g-2 align-items-center">
        <div class="col-md-3">
            <input type="text" name="customer_name" class="form-control" placeholder="Customer name"
                   value="{{ request('customer_name') }}">
        </div>

        <div class="col-md-2">
            <input type="date" name="publish_date" class="form-control" placeholder="Publish date"
                   value="{{ request('publish_date') }}">
        </div>

        <div class="col-md-3">
            <input type="text" name="title" class="form-control" placeholder="Ad title"
                   value="{{ request('title') }}">
        </div>

        <div class="col-md-2">
            <input type="text" name="phone" class="form-control" placeholder="Phone"
                   value="{{ request('phone') }}">
        </div>

        <div class="col-md-2 d-flex">
            <input type="text" name="email" class="form-control me-2" placeholder="Email"
                   value="{{ request('email') }}">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </div>

    <div class="row g-2 mt-2">
        <div class="col-md-4">
            <input type="text" name="category" class="form-control" placeholder="Category name"
                   value="{{ request('category') }}">
        </div>
        <div class="col-md-8 text-end">
            <a href="{{ url('/advertisements/create') }}" class="btn btn-success me-2">
                <i class="bx bx-plus"></i> New Ad
            </a>
            <a href="#" class="btn btn-outline-secondary">
                <i class="bx bx-cloud-download"></i> Export
            </a>
        </div>
    </div>
</form>
