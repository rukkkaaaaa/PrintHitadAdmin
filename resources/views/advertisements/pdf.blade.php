<!DOCTYPE html>
<html>
<head>
    <title>Advertisement Details</title>
    <style>
        body { font-family: DejaVu Sans; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border: 1px solid #ccc; }
    </style>
</head>
<body>

<h2>Advertisement Details</h2>

<table>
    <tr><td><b>ID</b></td><td>{{ $ad->id }}</td></tr>
    <tr><td><b>Customer</b></td><td>{{ $ad->customer_name }}</td></tr>
    <tr><td><b>Category</b></td><td>{{ $ad->category_name }}</td></tr>
    <tr><td><b>Description</b></td><td>{{ $ad->advertisement_description }}</td></tr>
    <tr><td><b>District</b></td><td>{{ $ad->district_name }}</td></tr>
    <tr><td><b>City</b></td><td>{{ $ad->city_name }}</td></tr>
    <tr><td><b>Publish Date</b></td><td>{{ $ad->publish_date }}</td></tr>
    <tr><td><b>Payment Status</b></td><td>{{ $ad->payment_status }}</td></tr>
    <tr><td><b>Payment Method</b></td><td>{{ $ad->payment_method }}</td></tr>
</table>

</body>
</html>