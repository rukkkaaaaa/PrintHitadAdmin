@extends('layouts.app') {{-- Adjust if you're using a layout --}}

@section('content')
<div class="container mt-4">
    <h4 class="fw-bold mb-4">User Management</h4>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @elseif(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- User Create Form -->
    <div class="card mb-4">
        <div class="card-header">Create New User</div>
        <div class="card-body">
            <form method="POST" action="{{ url('/users') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required />
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required />
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required />
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Confirm</label>
                        <input type="password" name="password_confirmation" class="form-control" required />
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create User</button>
            </form>
        </div>
    </div>

    <!-- User Table -->
    <div class="card">
        <div class="card-header">All Users</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
