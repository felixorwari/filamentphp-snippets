@props(['title' => '', 'value' => 0, 'max' => 100])

@php
    // Ensure value is between 0 and the max
    $percentage = min(100, max(0, ($value / $max) * 100));
    // Round to 1 decimal place if needed, otherwise integer
    $displayValue = round($percentage);
@endphp

<div class="w-full">
    <div class="mb-1.5 flex items-center justify-between text-sm font-medium">
        <h4 class="font-medium!">{{ $title }}</h4>
        <p class="er-mono">{{ $displayValue }}%</p>
    </div>

    <div class="relative h-2 w-full">
        <svg viewBox="0 0 100 2" preserveAspectRatio="none" class="h-full w-full overflow-hidden rounded-full">
            <!-- Background Track -->
            <rect x="0" y="0" width="100" height="2" fill="currentColor"
                class="text-zinc-200 dark:text-white/10" />

            <!-- Foreground Progress -->
            <rect x="0" y="0" width="{{ $percentage }}" height="2" fill="currentColor" rx="1"
                class="text-accent transition-all duration-500 ease-out" />
        </svg>
    </div>
</div>
