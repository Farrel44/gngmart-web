<x-filament-widgets::widget>
    <x-filament::section heading="Aktivitas Terbaru" icon="heroicon-o-clock">
        @php
            $activities = $this->getActivities();
        @endphp

        @if ($activities->isEmpty())
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                    <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6 text-gray-400" />
                </div>
                <p class="text-sm font-medium text-gray-500">Belum ada aktivitas</p>
                <p class="mt-1 text-xs text-gray-400">Aktivitas terbaru akan muncul di sini</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($activities as $activity)
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $activity['bg'] }}">
                            <x-filament::icon :icon="$activity['icon']" class="h-4 w-4 {{ $activity['color'] }}" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm text-gray-700">{{ $activity['message'] }}</p>
                            <p class="text-xs text-gray-400">{{ $activity['time']->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
