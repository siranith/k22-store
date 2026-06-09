<div 
    x-data="{ show: false, product: null, quantity: 1 }"
    @open-product-sheet.window="
        product = $event.detail;
        quantity = 1;
        show = true;
        document.body.style.overflow = 'hidden';
    "
>
    <!-- Overlay -->
    <div 
        x-show="show" 
        x-transition.opacity 
        class="fixed inset-0 bg-black bg-opacity-50 z-[60]"
        @click="show = false; document.body.style.overflow = ''"
        style="display: none;"
    ></div>

    <!-- Bottom Sheet -->
    <div 
        x-show="show" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed inset-x-0 bottom-0 z-[70] bg-white rounded-t-3xl shadow-xl flex flex-col max-h-[90vh]"
        style="display: none;"
    >
        <!-- Drag handle -->
        <div class="flex justify-center p-4" @click="show = false; document.body.style.overflow = ''">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>

        <template x-if="product">
            <div class="overflow-y-auto px-6 pb-6 flex-1">
                <!-- Product Image -->
                <div class="w-full h-64 bg-gray-100 rounded-2xl mb-6 overflow-hidden flex items-center justify-center">
                    <template x-if="product?.image_url">
                        <img :src="product.image_url" :alt="product?.name" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!product?.image_url">
                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </template>
                </div>

                <!-- Details -->
                <div class="mb-4">
                    <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold mb-2" x-text="product?.category_name"></span>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1" x-text="product?.name"></h2>
                    <div class="text-2xl font-extrabold text-blue-600 mb-4">$<span x-text="Number(product?.price).toFixed(2)"></span></div>
                    
                    <p class="text-gray-600 text-sm leading-relaxed" x-text="product?.description || 'No description available.'"></p>
                </div>
                
                <div class="mt-4 p-4 bg-gray-50 rounded-xl flex items-center justify-between mb-2">
                    <span class="text-gray-700 font-medium">Stock Available</span>
                    <span class="font-bold" :class="product?.stock > 0 ? 'text-green-600' : 'text-red-600'" x-text="product?.stock > 0 ? product.stock + ' items' : 'Out of Stock'"></span>
                </div>
            </div>
        </template>

        <!-- Sticky Bottom Action Bar -->
        <template x-if="product">
            <div class="p-4 border-t bg-white">
                <form :action="'/cart/add/' + product.id" method="POST" class="flex gap-4">
                    @csrf
                    <div class="flex items-center border rounded-xl bg-gray-50">
                        <button type="button" @click="if(quantity > 1) quantity--" class="px-4 py-3 text-gray-600 hover:text-blue-600 font-bold text-xl">-</button>
                        <input type="number" name="quantity" x-model="quantity" min="1" :max="product?.stock" class="w-12 text-center bg-transparent border-none font-bold p-0 focus:ring-0" readonly>
                        <button type="button" @click="if(quantity < product?.stock) quantity++" class="px-4 py-3 text-gray-600 hover:text-blue-600 font-bold text-xl">+</button>
                    </div>
                    
                    <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl font-bold text-lg py-3 hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" :disabled="product?.stock < 1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Add to Cart
                    </button>
                </form>
            </div>
        </template>
    </div>
</div>
