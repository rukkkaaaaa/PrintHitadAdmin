<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2 { margin: 0 0 4px; font-size: 18px; text-align: center; }
        .sub { text-align: center; color: #666; margin-bottom: 16px; }
        .meta { margin-bottom: 14px; }
        .meta div { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d9dde3; padding: 7px 6px; vertical-align: top; }
        th { background: #f5f7fa; text-align: left; font-size: 10px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; color: #fff; font-size: 10px; font-weight: bold; }
        .success { background: #28a745; }
        .warning { background: #f0ad4e; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <div class="sub">Monthly report for {{ $monthLabel }}</div>

    <div class="meta">
        <div><strong>Month:</strong> {{ $monthInput }}</div>
        <div><strong>Total Records:</strong> {{ $report['count'] }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">Customer</th>
                <th style="width: 15%">Category</th>
                <th style="width: 12%">District</th>
                <th style="width: 12%">City</th>
                <th style="width: 10%">Publish Date</th>
                <th style="width: 10%">Amount</th>
                <th style="width: 10%">Payment Status</th>
                <th style="width: 11%">Payment Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['ads'] as $ad)
                <tr>
                    <td>{{ $ad->id }}</td>
                    <td>{{ $ad->customer_name }}</td>
                    <td>{{ $ad->category_name }}</td>
                    <td>{{ $ad->district_name }}</td>
                    <td>{{ $ad->city_name }}</td>
                    <td>{{ $ad->publish_date ? \Illuminate\Support\Carbon::parse($ad->publish_date)->format('Y-m-d') : '-' }}</td>
                    <td>{{ is_null($ad->amount) ? '-' : 'Rs. ' . number_format($ad->amount, 2) }}</td>
                    <td>
                        @php($status = strtolower((string) ($ad->payment_status ?? '')))
                        @if($status === 'completed')
                            <span class="badge success">Completed</span>
                        @elseif($status === 'pending')
                            <span class="badge warning">Pending</span>
                        @elseif($status === 'failed')
                            <span class="badge warning">Failed</span>
                        @else
                            <span class="muted">No Payment</span>
                        @endif
                    </td>
                    <td>{{ $ad->payment_date ? \Illuminate\Support\Carbon::parse($ad->payment_date)->format('Y-m-d') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="muted" style="text-align:center; padding: 14px;">No records found for this month.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>