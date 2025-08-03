@props(['title', 'percentage', 'color' => 'success', 'size' => 'lg', 'animated' => true])

<div class="progress-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">{{ $title }}</h6>
        <span class="badge badge-{{ $color }}">{{ $percentage }}%</span>
    </div>

    <div class="progress mb-3" style="height: {{ $size === 'lg' ? '25px' : '8px' }};">
        <div class="progress-bar {{ $animated ? 'progress-bar-striped progress-bar-animated' : '' }} bg-{{ $color }}"
             role="progressbar"
             style="width: {{ $percentage }}%"
             aria-valuenow="{{ $percentage }}"
             aria-valuemin="0"
             aria-valuemax="100">
            @if($size === 'lg')
                <strong class="text-white">{{ $percentage }}% Completado</strong>
            @endif
        </div>
    </div>
</div>

<style>
.progress-card {
    transition: all 0.3s ease;
}

.progress-card:hover {
    transform: scale(1.02);
}

.progress-bar {
    transition: width 0.6s ease;
}

.progress {
    border-radius: 0.5rem;
    overflow: hidden;
}
</style>
