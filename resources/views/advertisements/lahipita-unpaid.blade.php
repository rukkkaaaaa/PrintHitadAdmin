@extends('layouts.app')

@section('content')

<div style="min-height: calc(100vh - 220px); display:flex; flex-direction:column;">
    <div class="container mt-4">
        <h2 class="mb-3">Lahipita - Unpaid Advertisements</h2>

        <style>
            /* Unified page tweaks for all advertisement tables */
            .ads-card {
                border-radius: 12px;
                box-shadow: 0 6px 18px rgba(18, 38, 63, 0.06);
                overflow: hidden;
            }

            .ads-table thead th {
                background: #f8fafc;
                border-bottom: 2px solid #e9eef4;
                font-weight: 600;
                color: #5b6b7a;
            }

            .ads-table tbody tr:hover {
                background: rgba(99, 102, 241, 0.04);
            }

            .ads-table td,
            .ads-table th {
                vertical-align: middle;
            }

            .ads-table td .text-muted {
                display: block;
            }

            .badge-pill {
                border-radius: 999px;
                padding: .35rem .6rem;
                font-weight: 600;
            }

            .action-btns .btn {
                margin-right: .35rem;
            }

            .search-input .form-control {
                border-right: 0;
            }

            .search-input .input-group-text {
                background: transparent;
                border-left: 0;
            }

            .table-responsive {
                padding: 0.75rem 1rem;
            }

            @media (max-width: 767px) {
                .action-btns .btn {
                    margin-bottom: .35rem;
                }
            }
        </style>

        <!-- Search + actions -->
        <div class="row mb-3">
            <div class="col-12 mb-2">
                @include('advertisements._filters', ['action' => url('/advertisements/lahipita/unpaid')])
            </div>
        </div>


        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ads-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Publish Date</th>
                            <th>Payment Status</th>
                            <th>Send Link</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ads as $ad)
                        <tr>
                            <td>{{ $ad->id }}</td>
                            <td>{{ $ad->customer->customer_name }}</td>
                            <td>{{ $ad->category->category_name }}</td>

                            <td>
                                {{ \Illuminate\Support\Str::limit($ad->advertisement_description, 40) }}
                            </td>

                           {{-- AMOUNT --}}
<td>
    @if(!is_null($ad->latestPayment?->amount))
        Rs. {{ number_format($ad->latestPayment->amount, 2) }}
    @else
        -
    @endif
</td>

{{-- PUBLISH DATE --}}
<td>
    {{ $ad->publish_date
        ? \Carbon\Carbon::parse($ad->publish_date)->format('Y-m-d')
        : '-'
    }}
</td>
                            {{-- PAYMENT STATUS --}}
                            <td>
                                @include('partials.payment-status-badge', ['status' => $ad->latestPayment->payment_status])
                            </td>

                            {{-- SEND LINK --}}
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Send ad link via email"
                                    onclick="sendPaymentLink({{ $ad->id }}, this)">
                                    <i class="bx bx-send"></i>
                                </button>
                            </td>

                            {{-- ACTIONS --}}
                            <td class="action-btns">
                                <a href="{{ url('/advertisements/' . $ad->id . '/view') }}" class="btn btn-sm btn-outline-info">
                                    <i class="bx bx-show"></i>
                                </a>

                                <a href="{{ url('/advertisements/' . $ad->id . '/edit') }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bx bx-edit-alt"></i>
                                </a>

                                <button class="btn btn-sm btn-outline-success" onclick="confirmDownload({{ $ad->id }})">
                                    <i class="bx bx-download"></i>
                                </button>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">
                                No Lahipita unpaid advertisements found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if(method_exists($ads, 'links'))
                <div class="mt-3 px-3">
                    {{ $ads->links() }}
                </div>
                @endif

            </div>
        </div>


    </div>
</div>

{{-- DOWNLOAD CONFIRM SCRIPT --}}
<script>
    function confirmDownload(adId) {
        if (confirm("Do you want to download the ad details?")) {
            window.location.href = "{{ url('/advertisements') }}/" + adId + "/download";
        }
    }
</script>
<script>
    function sendPaymentLink(adId, btn) {
        if (!confirm('Send payment link to customer?')) return;

        // Disable button & show loading
        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';

        fetch(`/api/advertisement/${adId}/send-unpaid-advertisement-mail-si`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // optional for API routes, but safe to include
                },
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '<i class="bx bx-check"></i>';
                    btn.classList.replace('btn-outline-primary', 'btn-success');
                    showToast('success', data.message);
                } else {
                    btn.innerHTML = '<i class="bx bx-send"></i>';
                    btn.disabled = false;
                    btn.classList.replace('btn-outline-primary', 'btn-outline-danger');
                    showToast('error', data.message);
                }
            })
            .catch(err => {
                btn.innerHTML = '<i class="bx bx-send"></i>';
                btn.disabled = false;
                showToast('error', 'Something went wrong. Please try again.');
                console.error(err);
            });
    }

    function showToast(type, message) {
        // Simple alert fallback — replace with your toast library if available
        const icon = type === 'success' ? '✅' : '❌';
        const div = document.createElement('div');
        div.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            padding: 12px 20px; border-radius: 8px; font-size: 14px;
            background: ${type === 'success' ? '#d1fae5' : '#fee2e2'};
            color: ${type === 'success' ? '#065f46' : '#991b1b'};
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        `;
        div.textContent = `${icon} ${message}`;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 4000);
    }
</script>
@endsection