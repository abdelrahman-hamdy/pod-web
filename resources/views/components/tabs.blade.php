@props(['tabs' => []])

<div class="bg-white rounded-xl shadow-sm border border-slate-100 mb-6" x-data="{ activeTab: '{{ array_key_first($tabs) }}' }">
    <div class="flex px-6">
        @foreach($tabs as $key => $tab)
            <button @click="activeTab = '{{ $key }}'; $dispatch('tab-switched', { tab: '{{ $key }}' })"
                    :class="activeTab === '{{ $key }}' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-600 hover:text-slate-800'"
                    class="px-6 py-4 font-medium text-sm transition-colors">
                {{ $tab['label'] }}
                @if(isset($tab['count']))
                    ({{ $tab['count'] }})
                @endif
            </button>
        @endforeach
    </div>
</div>
