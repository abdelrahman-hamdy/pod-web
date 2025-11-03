@props([
    'src' => null,
    'name' => 'User',
    'size' => 'md',
    'class' => '',
    'showDefault' => true,
    'color' => null
])

@php
    $sizes = [
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-12 h-12 text-base',
        'lg' => 'w-32 h-32 text-2xl',
        'xl' => 'w-20 h-20 text-lg'
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $initials = strtoupper(substr($name, 0, 2));
    
    // Use provided color or fallback to default
    $colorClass = $color ?? 'bg-slate-100 text-slate-600';
    
    // Convert avatar path to full URL if needed
    if ($src && !str_starts_with($src, 'http://') && !str_starts_with($src, 'https://')) {
        // If it doesn't start with http, check if it starts with /
        if (!str_starts_with($src, '/')) {
            // It's a storage path without /storage prefix
            $src = '/storage/' . $src;
        }
    }
@endphp

<div class="relative inline-flex items-center justify-center {{ $sizeClass }} {{ $class }} rounded-full {{ $colorClass }} font-medium overflow-hidden avatar">
    @if($src && $src !== '' && $src !== 'null')
        <img src="{{ $src }}" 
             alt="{{ $name }}" 
             class="w-full h-full object-cover rounded-full"
             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="w-full h-full flex items-center justify-center rounded-full" style="display: none;">
            {{ $initials }}
        </div>
    @else
        <div class="w-full h-full flex items-center justify-center rounded-full">
            {{ $initials }}
        </div>
    @endif
</div>
