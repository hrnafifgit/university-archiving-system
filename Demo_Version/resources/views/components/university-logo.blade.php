@props([
    'class' => '',
    'imgClass' => 'max-h-full max-w-full object-contain p-0.5',
    'fallbackIcon' => 'fa-solid fa-building-columns',
    'fallbackSize' => 'text-base text-gold',
    'alt' => 'شعار جامعة إب'
])

@php
    $logoCandidates = [
        'images/logo.png',
        'images/logo.svg',
        'images/logo.jpg',
        'images/logo.jpeg',
    ];
    $logoUrl = null;
    foreach ($logoCandidates as $candidate) {
        if (file_exists(public_path($candidate))) {
            $logoUrl = asset($candidate);
            break;
        }
    }
@endphp

<div {{ $attributes->merge(['class' => "flex items-center justify-center overflow-hidden shrink-0 " . ($class ?: 'w-10 h-10')]) }}>
    @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $alt }}" class="{{ $imgClass }}">
    @else
        <i class="{{ $fallbackIcon }} {{ $fallbackSize }}"></i>
    @endif
</div>
