<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Quick Presets --}}
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2 flex items-center gap-2">
                <x-heroicon-o-bolt class="w-4 h-4 text-amber-500" />
                Quick Presets (តម្លៃពេញនិយម)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                Click any rate below to quickly fill in the exchange rate:
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach([4000, 4050, 4100, 4120, 4150, 4200] as $preset)
                    <button
                        type="button"
                        wire:click="setPresetRate({{ $preset }})"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150
                            {{ ($this->currentRate == $preset)
                                ? 'bg-amber-500 text-white border-amber-500 shadow-sm'
                                : 'bg-gray-50 hover:bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600' }}"
                    >
                        ៛{{ number_format($preset) }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Form --}}
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            {{-- Live Conversion Preview --}}
            @php
                $rate = $this->currentRate > 0 ? $this->currentRate : 4000;
            @endphp
            <div class="p-4 bg-amber-50/50 dark:bg-gray-850 rounded-xl border border-amber-200/60 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-300 mb-3 flex items-center gap-2">
                    <x-heroicon-o-calculator class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                    Live Conversion Preview (គំរូគណនា COD លើវិក្កយបត្រ)
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    @foreach([5, 10, 20, 50] as $usd)
                        <div class="p-2.5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-xs">
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">${{ number_format($usd, 2) }} USD</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                ៛{{ number_format($usd * $rate) }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 text-xs text-amber-800 dark:text-amber-300/80">
                    ℹ️ When COD is checked on a sale, the customer receipt will show: <span class="font-bold">៛{{ number_format(10 * $rate) }} (COD)</span> for a $10.00 purchase.
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="flex items-center gap-3">
                <x-filament::button type="submit" size="lg">
                    <span wire:loading.remove wire:target="save">Save Exchange Rate</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
