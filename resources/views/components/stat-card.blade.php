@props(['title', 'value', 'icon', 'color' => 'primary', 'trend' => null, 'trendValue' => null])

<div class="card border-0 shadow-sm h-100 stat-card">
    <div class="card-body text-center">
        <div class="d-flex align-items-center justify-content-center mb-3">
            <div class="bg-{{ $color }} bg-opacity-10 p-3 rounded-circle">
                <i class="{{ $icon }} fa-2x text-{{ $color }}"></i>
            </div>
        </div>
        <h3 class="text-{{ $color }} mb-1 stat-value">{{ $value }}</h3>
        <p class="text-muted mb-0">{{ $title }}</p>

        @if($trend)
            <div class="mt-2">
                <small class="text-{{ $trend === 'up' ? 'success' : 'danger' }}">
                    <i class="fas fa-arrow-{{ $trend }}"></i>
                    {{ $trendValue }}
                </small>
            </div>
        @endif
    </div>
</div>

<style>
.stat-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.stat-value {
    font-weight: 600;
    font-size: 2rem;
}

.rounded-circle {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
