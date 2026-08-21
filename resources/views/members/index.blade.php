@extends('layouts.app')

@section('title', 'Members')

@section('content')
<div class="container mt-4">
    <h4 class="fw-bold mb-4">Member List</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

        {{-- Search --}}
    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ url()->current() }}">
                <div class="row g-2 align-items-center">

                    <div class="col-md-9">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search by name, email, NIC or passport..."
                            value="{{ request('search') }}"
                        >
                    </div>

                    <div class="col-md-3 d-flex gap-2">

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Search
                        </button>

                        @if(request('search'))
                            <a href="{{ url()->current() }}" class="btn btn-secondary">
                                Clear
                            </a>
                        @endif

                    </div>

                </div>
            </form>

        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>All Members</strong>
            <span class="badge bg-primary">{{ $members->count() }} total</span>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="60">#</th>
                        <th>Member Name</th>
                        <th>Address</th>
                        <th>Telephone</th>
                        <th>NIC / Passport</th>
                        <th>Email</th>
                        <th width="120">Status</th>
                        <th width="180">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $index => $member)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $member->customer_name }}</td>
                            <td>{{ $member->address }}</td>
                            <td>{{ $member->telephone }}</td>
                            <td>{{ $member->nic_passport }}</td>
                            <td>{{ $member->email ?: 'N/A' }}</td>
                            <td>
                                @if($member->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($member->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">No members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
