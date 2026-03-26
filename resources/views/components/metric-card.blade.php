@props(['title', 'value', 'icon', 'description' => null])
<div class="card metric-card shadow-sm mb-3">
    <div class="card-body d-flex align-items-center gap-3">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
            <i class="{{ $icon }}"></i>
        </div>
        <div>
            <div class="text-uppercase small text-muted">{{ $title }}</div>
            <div class="h4 mb-0">{{ $value }}</div>
            @if($description)
                <div class="small text-success">{{ $description }}</div>
            @endif
        </div>
    </div>
</div>
