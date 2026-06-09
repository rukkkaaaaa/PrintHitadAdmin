@php
    $status = strtolower((string) ($status ?? ''));

    $badgeClass = 'bg-secondary';
    $label = 'No Payment';

    if ($status === 'pending') {
        $badgeClass = 'bg-warning text-dark';
        $label = 'Pending';
    } elseif ($status === 'completed') {
        $badgeClass = 'bg-success';
        $label = 'Completed';
    } elseif ($status === 'failed') {
        $badgeClass = 'bg-danger';
        $label = 'Failed';
    }
@endphp

<span class="badge {{ $badgeClass }}">{{ $label }}</span>
