<x-filament-panels::page>
    {{-- Customer info form --}}
    {{ $this->form }}

    <hr class="my-1">

    {{-- Product table list --}}
    {{ $this->table }}

    <hr class="my-1">

    {{-- Cart display --}}
    <div class="mt-2">
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

        {{-- Always render submit button fixed bottom-right; visually disable when cart is empty --}}
        <div class="fixed bottom-6 right-6 z-50">
            <x-filament::button
                color="success"
                wire:click="submit"
                size="lg"
                class="{{ empty($cart) ? 'opacity-50 pointer-events-none' : '' }}"
            >
                Submit Sale
            </x-filament::button>
        </div>
    </div>

</x-filament-panels::page>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (document.getElementById('submit-sale-btn')) return;

                var btn = document.createElement('button');
                btn.id = 'submit-sale-btn';
                btn.type = 'button';
                btn.textContent = 'Save';
                btn.style.position = 'fixed';
                btn.style.top = '5.5rem';
                btn.style.right = '1.5rem';
                btn.style.zIndex = '99999';
                btn.style.padding = '0.75rem 1rem';
                btn.style.background = '#10b981';
                btn.style.color = '#ffffff';
                btn.style.border = 'none';
                btn.style.borderRadius = '0.375rem';
                btn.style.boxShadow = '0 2px 6px rgba(0,0,0,0.15)';
                btn.style.cursor = 'pointer';
                btn.onclick = function () {
                    // Prefer clicking the existing Livewire button (if present)
                    var original = document.querySelector('[wire\\:click="submit"]');
                    if (original) {
                        original.click();
                        return;
                    }

                    // Fallback to emitting Livewire event
                    if (typeof Livewire !== 'undefined') {
                        Livewire.emit('submitSale');
                    }
                };

                document.body.appendChild(btn);
            });
        </script>
    @endpush
@endonce
