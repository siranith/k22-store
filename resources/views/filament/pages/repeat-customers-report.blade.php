<x-filament-panels::page>
    <div class="mb-6 w-full">
        <h2 class="text-lg font-bold mb-4">Repeat Customers Report</h2>

        <div class="grid gap-4" style="grid-template-columns: repeat(2, 1fr);">

            <div>
                <div class="bg-gray-50 p-3 rounded-xl shadow-sm">
                    <div class="text-gray-600">Total Repeat Customers</div>
                    <div class="text-xl font-bold text-indigo-700">
                        {{ $this->totalRepeatCustomers }}
                    </div>
                </div>
            </div>

            <div>
                <div class="bg-gray-50 p-3 rounded-xl shadow-sm">
                    <div class="text-gray-600">Total Revenue from Repeat Customers</div>
                    <div class="text-xl font-bold text-green-700">
                        ${{ number_format($this->totalRevenue, 2) }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
