<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Quick Actions</x-slot>

        <div class="grid gap-2 grid-cols-2 md:grid-cols-4 xl:grid-cols-4">
            @foreach ($this->getQuickActions() as $action)
                <x-filament::button
                    tag="a"
                    :href="$action['url']"
                    :icon="$action['icon']"
                    :color="$action['color']"
                    size="lg"
                    :class="'w-full justify-start h-12 !px-4 ' . ($action['classes'] ?? '')"
                >
                    {{ $action['label'] }}
                </x-filament::button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>


