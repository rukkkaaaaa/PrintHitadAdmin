@extends('layouts.app')

@section('content')

<div style="min-height: calc(100vh - 220px); display:flex; flex-direction:column;">
<div class="container mt-4">
    <h2 class="mb-3">Lahipita - Unpaid Advertisements</h2>

        {{-- ✅ EMAIL SUCCESS NOTIFICATION --}}
    @if(session('success'))
        <div id="emailSuccessNotification" class="email-success-notification">
            <div class="email-success-content">
                <i class="bx bx-check-circle email-success-icon"></i>

                <span class="email-success-message">
                    {{ session('success') }}
                </span>

                <button type="button"
                        class="email-success-close"
                        onclick="closeEmailSuccessNotification()"
                        aria-label="Close">
                    &times;
                </button>
            </div>
        </div>
    @endif

    <style>
        /* Unified page tweaks for all advertisement tables */
        .ads-card { border-radius: 12px; box-shadow: 0 6px 18px rgba(18,38,63,0.06); overflow: hidden; }
        .ads-table thead th { background: #f8fafc; border-bottom: 2px solid #e9eef4; font-weight:600; color:#5b6b7a; }
        .ads-table tbody tr:hover { background: rgba(99,102,241,0.04); }
        .ads-table td, .ads-table th { vertical-align: middle; }
        .ads-table td .text-muted { display:block; }
        .badge-pill { border-radius: 999px; padding: .35rem .6rem; font-weight:600; }
        .action-btns .btn { margin-right: .35rem; }
        .search-input .form-control { border-right: 0; }
        .search-input .input-group-text { background: transparent; border-left: 0; }
        .table-responsive { padding: 0.75rem 1rem; }
        @media (max-width: 767px) {
            .action-btns .btn { margin-bottom: .35rem; }
        }

        /* ✅ EMAIL SUCCESS NOTIFICATION */
        .email-success-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            min-width: 320px;
            max-width: 450px;
            background-color: #198754;
            color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.20);
            animation: emailNotificationSlideIn 0.4s ease;
        }

        .email-success-content {
            display: flex;
            align-items: center;
            padding: 15px 18px;
        }

        .email-success-icon {
            font-size: 24px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .email-success-message {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        }

        .email-success-close {
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 26px;
            font-weight: 400;
            line-height: 1;
            margin-left: 15px;
            padding: 0;
            cursor: pointer;
            opacity: 0.85;
        }

        .email-success-close:hover {
            opacity: 1;
        }

        .email-notification-hide {
            animation: emailNotificationSlideOut 0.4s ease forwards;
        }

        @keyframes emailNotificationSlideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes emailNotificationSlideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        @media (max-width: 576px) {
            .email-success-notification {
                left: 15px;
                right: 15px;
                min-width: auto;
                max-width: none;
            }
        }
    </style>

    <!-- Search + actions -->
    <div class="row mb-3">
        <div class="col-12 mb-2">
            @include('advertisements._filters', ['action' => url('/advertisements/lahipita/unpaid')])
        </div>
    </div>

    <div class="card ads-card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover ads-table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Category</th>
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
                        <td>{{ $ad->customer_name }}</td>
                        <td>{{ $ad->category_name }}</td>

                        <td>
                            {{ $ad->amount ? 'Rs. ' . number_format($ad->amount, 2) : '-' }}
                        </td>

                        <td>
    {{ $ad->publish_date
        ? \Carbon\Carbon::parse($ad->publish_date)->format('Y-m-d')
        : '-'
    }}
</td>

                        {{-- PAYMENT STATUS --}}
                        <td>
                            @include('partials.payment-status-badge', ['status' => $ad->payment_status])
                        </td>

                        {{-- SEND LINK --}}
                        <td>
                            <form method="POST" action="{{ url('/advertisements/' . $ad->id . '/send-link-email') }}" style="display:inline;">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-sm btn-outline-primary"
                                        title="Send ad link via email"
                                        onclick="return confirm('Send payment link to customer?');">
                                    <i class="bx bx-send"></i>
                                </button>
                            </form>
                        </td>

                        {{-- ACTIONS --}}
                        <td class="action-btns">
                            <a href="{{ url('/advertisements/' . $ad->id . '/view') }}" class="btn btn-sm btn-outline-info">
                                <i class="bx bx-show"></i>
                            </a>

                            <a href="{{ url('/advertisements/' . $ad->id . '/edit') }}" class="btn btn-sm btn-outline-warning">
                                <i class="bx bx-edit-alt"></i>
                            </a>

                            <button class="btn btn-sm btn-outline-success"
        onclick="confirmDownload(this)"
        data-download-url="{{ url('/advertisements/' . $ad->id . '/download') }}">
    <i class="bx bx-download"></i>
</button>

                            <!--button class="btn btn-sm btn-outline-success" onclick="confirmDownload({{ $ad->id }})">
                                <i class="bx bx-download"></i>
                            </button -->
                            
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No Lahipita unpaid advertisements found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if(method_exists($ads, 'links'))
            <div class="mt-3 px-3">
                @include('advertisements._pagination_count', ['ads' => $ads])
                {{ $ads->links() }}
            </div>
        @endif

        </div>
      </div>
    </div>

</div>
</div>

{{-- DOWNLOAD CONFIRM SCRIPT --}}
<script>
	function confirmDownload(btn) {
    if (confirm("Do you want to download the ad details?")) {
        window.location.href = btn.dataset.downloadUrl;
    }
}
/** function confirmDownload(adId) {
    if (confirm("Do you want to download the ad details?")) {
        window.location.href = "/advertisements/" + adId + "/download";
    }
} */


{{-- ✅ EMAIL SUCCESS NOTIFICATION SCRIPT --}}
function closeEmailSuccessNotification() {
    const notification = document.getElementById('emailSuccessNotification');

    if (notification) {
        notification.classList.add('email-notification-hide');

        setTimeout(function () {
            notification.remove();
        }, 400);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const notification = document.getElementById('emailSuccessNotification');

    if (notification) {
        setTimeout(function () {
            closeEmailSuccessNotification();
        }, 3000);
    }
});
</script>

@endsection