@if($user)
<div class="messenger-list-item favorite-list-item px-3 py-3 rounded-lg hover:bg-slate-50 cursor-pointer transition-colors" data-contact="{{ $user->id }}">
    <div class="flex items-center space-x-3" data-action="0">
        {{-- Avatar with Online Status --}}
        <div class="flex-shrink-0 relative" data-id="{{ $user->id }}" style="padding: 4px; margin-right: 4px;">
            <x-chatify-avatar 
                :src="$user->avatar ?? null"
                :name="$user->name ?? 'User'"
                size="av-m"
                :showDefault="false"
                :showStatus="true"
                :isOnline="$user->active_status ?? false"
                :color="$user->avatar_color ?? null" />
        </div>
        
        {{-- User Name --}}
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-slate-800 truncate" data-id="{{ $user->id }}" data-type="user">
                {{ strlen($user->name) > 18 ? trim(substr($user->name,0,18)).'..' : $user->name }}
            </p>
        </div>
        
        {{-- Favorite Star Indicator --}}
        <div class="flex-shrink-0">
            <i class="ri-star-fill text-yellow-500 text-xl"></i>
        </div>
    </div>
</div>
@endif