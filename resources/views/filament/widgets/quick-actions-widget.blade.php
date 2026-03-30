<x-filament-widgets::widget>
    <x-filament::section heading="Aksi Cepat" icon="heroicon-o-bolt">

        <div class="flex flex-col gap-4 sm:flex-row">
            @foreach ($this->getActions() as $action)
                <a href="{{ $action['url'] }}"
                   class="flex flex-1 items-center gap-3 rounded-xl p-4 transition-all hover:shadow-md {{ $action['bg'] }}">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm">
                        <x-filament::icon :icon="$action['icon']" class="h-5 w-5 {{ $action['color'] }}" />
                    </div>

                    <span class="text-sm font-medium text-gray-700 leading-tight">
                        {{ $action['label'] }}
                    </span>
                </a>
            @endforeach
        </div>

    </x-filament::section>
</x-filament-widgets::widget>