<!DOCTYPE html>
<html>
<head>
    <title>Advertisement Details</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h2 { text-align: center; margin: 0 0 18px; }
        h3 { margin: 18px 0 8px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        td { padding: 8px; border: 1px solid #ccc; vertical-align: top; }
        .label { width: 28%; font-weight: bold; background: #f7f7f7; }
        .muted { color: #666; }
        .section { margin-bottom: 14px; }
        .criteria-grid { width: 100%; }
        .criteria-grid td { width: 50%; }
    </style>
</head>
<body>

<h2>Advertisement Details</h2>

<div class="section">
    <h3>Advertisement Info</h3>
    <table>
        <tr><td class="label">ID</td><td>{{ $ad->id }}</td></tr>
        <tr><td class="label">Description</td><td>{{ $ad->advertisement_description }}</td></tr>
        <tr><td class="label">Publication</td><td>{{ $ad->publication }}</td></tr>
        <tr><td class="label">Publish Date</td><td>{{ $ad->publish_date }}</td></tr>
        <tr><td class="label">Status</td><td>{{ (int) $ad->status === 1 ? 'Active' : 'Inactive' }}</td></tr>
    </table>
</div>

<div class="section">
    <h3>Customer Info</h3>
    <table>
        <tr><td class="label">Customer</td><td>{{ $ad->customer_name }}</td></tr>
        <tr><td class="label">Address</td><td>{{ $ad->address }}</td></tr>
        <tr><td class="label">Telephone</td><td>{{ $ad->telephone }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $ad->email }}</td></tr>
        <tr><td class="label">NIC/Passport</td><td>{{ $ad->nic_passport }}</td></tr>
    </table>
</div>

<div class="section">
    <h3>Location</h3>
    <table>
        <tr><td class="label">Category</td><td>{{ $ad->category_name }}</td></tr>
        <tr><td class="label">District</td><td>{{ $ad->district_name }}</td></tr>
        <tr><td class="label">City</td><td>{{ $ad->city_name }}</td></tr>
    </table>
</div>

<div class="section">
    <h3>Payment Details</h3>
    <table>
        @if($ad->payment_method)
            <tr><td class="label">Payment Method</td><td>{{ $ad->payment_method }}</td></tr>
            <tr><td class="label">Amount</td><td>Rs. {{ number_format((float) $ad->amount, 2) }}</td></tr>
            <tr><td class="label">Payment Date</td><td>{{ $ad->payment_date }}</td></tr>
            <tr><td class="label">Payment Status</td><td>{{ ucfirst((string) $ad->payment_status) }}</td></tr>
        @else
            <tr><td class="label">Payment</td><td class="muted">No Payment Found</td></tr>
        @endif
    </table>
</div>

<div class="section">
    <h3>Criteria</h3>
    @if(isset($criterias) && $criterias->count() > 0)
        <table class="criteria-grid">
            @foreach($criterias as $crit)
                @php
                    $critLabel = trim($crit->advertisement_criteria_name_en ?? $crit->advertisement_criteria_name_si ?? '');
                    $value = $criteriaValues[$crit->id] ?? null;
                @endphp
                @if($critLabel !== '')
                    <tr>
                        <td class="label">{{ $critLabel }}</td>
                        <td>{{ $value ?? '—' }}</td>
                    </tr>
                @endif
            @endforeach
        </table>
    @else
        <p class="muted">No criteria available.</p>
    @endif
</div>

</body>
</html>