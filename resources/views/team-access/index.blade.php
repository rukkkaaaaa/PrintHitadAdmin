@extends('layouts.app')

@section('title', 'Team Access')

@section('content')
<div class="container-fluid px-0">
    <h4 class="fw-bold mb-4">Team Area Access</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <p class="text-muted">Select work areas for each Team Chandana or Team Nalaka account. Unselected areas are denied. Other user roles are not affected.</p>
            <form method="POST" action="{{ url('/team-access') }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Team account</th>
                                <th>Role</th>
                                @foreach($areas as $area)
                                    <th class="text-center">{{ $area['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admins as $admin)
                                <tr>
                                    <td>{{ $admin->admin_name }}<br><small class="text-muted">{{ $admin->email }}</small></td>
                                    <td>{{ ucwords($admin->role) }}</td>
                                    @foreach($areas as $areaKey => $area)
                                        <td class="text-center">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="permissions[{{ $admin->id }}][]"
                                                value="{{ $areaKey }}"
                                                aria-label="{{ $admin->admin_name }} access to {{ $area['label'] }}"
                                                disabled
                                                {{ in_array($areaKey, $permissions[$admin->id] ?? [], true) ? 'checked' : '' }}
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($areas) + 2 }}" class="text-center">No Team Chandana or Team Nalaka accounts found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" id="edit-team-access" class="btn btn-outline-primary">Edit</button>
                    <button type="submit" id="save-team-access" class="btn btn-primary d-none">Save Team Access</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButton = document.getElementById('edit-team-access');
        const saveButton = document.getElementById('save-team-access');
        const permissionCheckboxes = document.querySelectorAll('input[name^="permissions["]');

        editButton.addEventListener('click', function () {
            permissionCheckboxes.forEach(function (checkbox) {
                checkbox.disabled = false;
            });

            editButton.classList.add('d-none');
            saveButton.classList.remove('d-none');
        });
    });
</script>
@endpush
