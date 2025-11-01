@props([
    'status' => 'default',
    'label' => null,
    'size' => 'md'
])

@php
    // Define status color mapping
    $statusMap = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'reviewed' => 'bg-blue-100 text-blue-800',
        'accepted' => 'bg-green-100 text-green-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'active' => 'bg-green-100 text-green-800',
        'inactive' => 'bg-slate-100 text-slate-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'completed' => 'bg-blue-100 text-blue-800',
        'draft' => 'bg-gray-100 text-gray-800',
        'published' => 'bg-green-100 text-green-800',
        'expired' => 'bg-red-100 text-red-800',
        'closed' => 'bg-gray-100 text-gray-800',
        'open' => 'bg-green-100 text-green-800',
        'default' => 'bg-gray-100 text-gray-800',
    ];
    
    // Size classes
    $sizes = [
        'sm' => 'text-xs px-2 py-1',
        'md' => 'text-sm px-3 py-1',
        'lg' => 'text-base px-4 py-1.5'
    ];
    
    $statusClass = $statusMap[$status] ?? $statusMap['default'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span class="inline-flex items-center rounded-full font-medium {{ $statusClass }} {{ $sizeClass }}">
    {{ $label ?? ucfirst($status) }}
</span>

