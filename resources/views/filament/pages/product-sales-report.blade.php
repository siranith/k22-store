<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">Product Sales Report</h2>
    </div>

    <form method="GET" class="flex gap-4 items-end flex-wrap">
        <!-- <div>
            <label class="block text-sm font-medium text-gray-700">Period</label>
            <select name="period" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Day</option>
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Week</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Month</option>
                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom</option>
            </select>
        </div> -->

        <div>
            <label class="block text-sm font-medium text-gray-700">Search Product</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name..." class="mt-1 block rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:outline-none focus:ring-primary-500 focus:border-primary-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">From</label>
            <input type="date" name="from" value="{{ $from }}" class="mt-1 block rounded-md border-gray-300 shadow-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Until</label>
            <input type="date" name="until" value="{{ $until }}" class="mt-1 block rounded-md border-gray-300 shadow-sm">
        </div>

        <div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
                Filter
            </button>
        </div>

        <div class="ml-auto text-right">
            <div class="text-sm text-gray-700">
                <strong>Total Quantity:</strong> {{ number_format($totalQuantity) }}
            </div>
            <div class="text-sm text-gray-700">
                <strong>Total Revenue:</strong> ${{ number_format($totalRevenue, 2) }}
            </div>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-sm font-semibold text-gray-900">Product</th>
                    <th class="px-6 py-3 text-sm font-semibold text-gray-900">SKU</th>
                    <th class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">Qty Sold</th>
                    <th class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $row->product_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $row->sku }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($row->total_quantity) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($row->total_revenue, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            No data for selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
