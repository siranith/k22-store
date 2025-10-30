<x-filament-panels::page>
    {{-- Customer info form --}}
    {{ $this->form }}

    <hr class="my-4">

    {{-- Product table list --}}
    {{ $this->table }}

    <hr class="my-4">

    {{-- Cart display --}}
    <div class="mt-6">
        <h2 class="text-lg font-semibold mb-2">Cart</h2>
        <h2 class="text-md font-medium mb-2">Total: {{ number_format(array_sum(array_column($cart, 'line_total')), 2) }}</h2>
        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Product</th>
                    <th class="p-2 text-center">Qty</th>
                    <th class="p-2 text-right">Price</th>
                    <th class="p-2 text-right">Total</th>
                    <th class="p-2 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cart as $index => $item)
                    <tr class="border-t">
                        <td class="p-2">{{ $item['product_name'] }}</td>
                        <td class="p-2 text-center">{{ $item['quantity'] }}</td>
                        <td class="p-2 text-right">{{ number_format($item['unit_price'], 2) }}</td>
                        <td class="p-2 text-right">{{ number_format($item['line_total'], 2) }}</td>
                        <td class="p-2 text-center">
                            <x-filament::button
                                color="danger"
                                size="sm"
                                wire:click="removeProduct({{ $index }})"
                            >
                                Remove
                            </x-filament::button>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if (!empty($cart))
            <div class="text-right mt-4">
                <x-filament::button color="success" wire:click="submit">
                    Submit Sale
                </x-filament::button>
            </div>
        @endif
    </div>

</x-filament-panels::page>
