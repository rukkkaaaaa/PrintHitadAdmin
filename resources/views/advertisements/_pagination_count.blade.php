@if(method_exists($ads, 'total'))
    <div class="text-muted small mb-2">
        @if($ads->total() > 0)
            Showing {{ $ads->firstItem() }} to {{ $ads->lastItem() }} of {{ $ads->total() }} ads
        @else
            Showing 0 ads
        @endif
    </div>
@endif
