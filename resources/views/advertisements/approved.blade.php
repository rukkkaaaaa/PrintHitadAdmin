@extends('layouts.app')

@section('content')

@php

    $currentRole = strtolower(
        trim((string) data_get(session('user'), 'role', ''))
    );

    $hideAuditColumns = $currentRole === 'super admin';

@endphp

<div style="min-height: calc(100vh - 220px); display:flex; flex-direction:column;">

    <div class="container mt-4">

        <h2 class="mb-3">{{ $pageTitle }}</h2>

        <style>
            .ads-card {
                border-radius: 12px;
                box-shadow: 0 6px 18px rgba(18,38,63,0.06);
                overflow: hidden;
            }

            .ads-table thead th {
                background: #f8fafc;
                border-bottom: 2px solid #e9eef4;
                font-weight: 600;
                color: #5b6b7a;
            }

            .ads-table td,
            .ads-table th {
                vertical-align: middle;
            }

            .ads-table tbody tr:hover {
                background: rgba(99,102,241,0.04);
            }

            .viewed-row > td {
                background-color: #f3f4f6 !important;
            }

            .viewed-row:hover > td {
                background-color: #e9ecef !important;
            }
        </style>


        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        {{-- ERROR MESSAGE --}}
        @if(session('error'))

            <div class="alert alert-danger">
                {{ session('error') }}
            </div>

        @endif


        <div class="card ads-card">

            <div class="card-body p-0">

                <div class="table-responsive p-3">

                    <table class="table table-hover ads-table align-middle mb-0">

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Customer</th>

                                <th>Category</th>

                                <th>Publish Date</th>

                                @unless($hideAuditColumns)
                                    <th>Viewed By</th>
                                @endunless

                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>

                        @forelse($ads as $ad)

                            <tr class="{{ !empty($ad->viewed_by_admin_id) ? 'viewed-row' : '' }}">

                                {{-- ID --}}
                                <td>
                                    {{ $ad->id }}
                                </td>


                                {{-- CUSTOMER --}}
                                <td>
                                    {{ $ad->customer_name }}
                                </td>


                                {{-- CATEGORY --}}
                                <td>
                                    {{ $ad->category_name }}
                                </td>


                                {{-- PUBLISH DATE --}}
                                <td>
                                    {{ $ad->publish_date }}
                                </td>

                                @unless($hideAuditColumns)
                                {{-- VIEWED BY --}}
                                <td>

                                    @if(!empty($ad->viewed_by_admin_id))

                                        <span class="badge bg-success">

                                            <i class="bx bx-user-check"></i>

                                            {{ $ad->viewed_admin_name ?? 'Admin' }}

                                        </span>


                                        @if(!empty($ad->viewed_at))

                                            <small class="text-muted d-block mt-1">

                                                {{ \Carbon\Carbon::parse($ad->viewed_at)->format('Y-m-d H:i') }}

                                            </small>

                                        @endif

                                    @else

                                        <span class="text-muted">
                                            Not Viewed
                                        </span>

                                    @endif

                                </td>
                                @endunless


                                {{-- ACTION --}}
                                <td>

                                    @if(empty($ad->viewed_by_admin_id))

                                        @php

                                            $viewUrl = $publication === 'lahipita'
                                                ? url('/advertisements/lahipita/approved/' . $ad->id . '/view')
                                                : url('/advertisements/hitad/approved/' . $ad->id . '/view');

                                        @endphp


                                        <form
                                            method="POST"
                                            action="{{ $viewUrl }}"
                                            class="d-inline"
                                        >

                                            @csrf

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-info"
                                                onclick="return confirm('View this advertisement? You can only click View once.');"
                                            >

                                                <i class="bx bx-show"></i>

                                                View

                                            </button>

                                        </form>

                                    @else

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-secondary"
                                            disabled
                                        >

                                            <i class="bx bx-check"></i>

                                            Viewed

                                        </button>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="{{ $hideAuditColumns ? 5 : 6 }}"
                                    class="text-center text-muted py-4"
                                >

                                    No approved advertisements found.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>


                    <div class="mt-3">

                        {{ $ads->links() }}

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection