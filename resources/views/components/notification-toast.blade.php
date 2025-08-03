@props(['type' => 'info', 'message' => '', 'title' => '', 'duration' => 3000])

@php
    $config = [
        'success' => [
            'icon' => 'fas fa-check-circle',
            'bg' => 'bg-success',
            'text' => 'text-white'
        ],
        'error' => [
            'icon' => 'fas fa-exclamation-triangle',
            'bg' => 'bg-danger',
            'text' => 'text-white'
        ],
        'warning' => [
            'icon' => 'fas fa-exclamation-circle',
            'bg' => 'bg-warning',
            'text' => 'text-dark'
        ],
        'info' => [
            'icon' => 'fas fa-info-circle',
            'bg' => 'bg-info',
            'text' => 'text-white'
        ]
    ];

    $config = $config[$type] ?? $config['info'];
@endphp

<div class="toast-notification {{ $config['bg'] }} {{ $config['text'] }} alert-dismissible fade show"
     style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
        <span aria-hidden="true">&times;</span>
    </button>
    <div class="d-flex align-items-start">
        <i class="{{ $config['icon'] }} fa-lg mr-3 mt-1"></i>
        <div class="flex-grow-1">
            @if($title)
                <h6 class="mb-1 font-weight-bold">{{ $title }}</h6>
            @endif
            <p class="mb-0">{{ $message }}</p>
        </div>
    </div>
</div>

<script>
setTimeout(function() {
    $('.toast-notification').fadeOut();
}, {{ $duration }});
</script>
