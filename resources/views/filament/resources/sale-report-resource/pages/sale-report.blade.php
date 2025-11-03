<x-filament-panels::page>
    <div class="mb-4">
    <div class="grid grid-cols-6 gap-4">

        {{-- 1st Row --}}
        <div class="col-span-3">
            <h2 class="text-lg font-bold mb-3">Sale Report</h2>
            <div class="bg-gray-50 p-3 rounded-xl shadow-sm">
                <div class="text-gray-600">Total</div>
                <div class="text-xl font-bold text-green-700">
                    ${{ number_format($this->total, 2) }}
                </div>
            </div>
        </div>

        <div class="col-span-3 flex items-end">
            <div class="bg-gray-50 p-3 rounded-xl shadow-sm w-full">
                <div class="text-gray-600">Total Received (after discount)</div>
                <div class="text-xl font-bold text-green-700">
                    ${{ number_format($this->totalPaid, 2) }}
                </div>
            </div>
        </div>

        {{-- 2nd Row --}}
        <div class="col-span-2">
            <div class="bg-gray-50 p-3 rounded-xl shadow-sm">
                <div class="text-gray-600">Costing</div>
                <div class="text-xl font-bold text-green-700">
                    ${{ number_format($this->totalCost, 2) }}
                </div>
            </div>
        </div>

        <div class="col-span-2">
            <div class="bg-gray-50 p-3 rounded-xl shadow-sm">
                <div class="text-gray-600">Discount</div>
                <div class="text-xl font-bold text-blue-700">
                    ${{ number_format($this->totalDiscount, 2) }}
                </div>
            </div>
        </div>

        <div class="col-span-2">
            <div class="bg-gray-50 p-3 rounded-xl shadow-sm">
                <div class="text-gray-600">Total Benefit (After Discount)</div>
                <div class="text-xl font-bold text-blue-700">
                    ${{ number_format($this->totalBenefit, 2) }}
                </div>
            </div>
        </div>

    </div>
</div>

    {{ $this->table }}
</x-filament-panels::page>
