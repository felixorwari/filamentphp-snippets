<?php
// Anonymous blade component for displaying a single value in a donut chart
@props(['score' => 0, 'label' => 'SCORE', 'size' => 'h-48 w-48'])

@php
    // Sanitize and cap the score
    $score = max(0, min(100, (int) $score));
    // Circumference for r=80 is 2 * pi * 80 ≈ 502.65
    $circumference = 502.65;
    $dashoffset = $circumference - ($score / 100) * $circumference;
@endphp

<svg viewBox="0 0 200 200" class="{{ $size }} shrink-0" role="img"
    aria-label="{{ $label }} of {{ $score }} out of 100">

    <circle cx="100" cy="100" r="80" fill="none" stroke="currentColor" stroke-width="14"
        class="text-zinc-200 dark:text-white/10" />

    <circle cx="100" cy="100" r="80" fill="none" stroke="currentColor" stroke-width="14"
        stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $dashoffset }}"
        transform="rotate(-90 100 100)" class="text-yellow-500" />

    <text x="100" y="94" text-anchor="middle" class="font-mono text-zinc-800 dark:text-zinc-50" fill="currentColor"
        font-size="42" font-weight="600">{{ $score }}</text>

    <text x="100" y="122" text-anchor="middle" class="font-mono" fill="#a3a3a3" font-size="13" letter-spacing="1">
        {{ $label }}
    </text>
</svg>
