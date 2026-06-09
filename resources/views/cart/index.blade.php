@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-32">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Shopping Cart</h1>

        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if(empty($cart))
            <div class="bg-white rounded-2xl shadow-sm p-10 text-center">
                <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="text-gray-500 font-medium mb-6">Your cart is completely empty.</p>
                <a href="{{ route('portfolio.index') }}" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">Start Shopping</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($cart as $id => $item)
                    <div class="bg-white rounded-2xl shadow-sm p-4 flex gap-4 relative">
                        <!-- Remove btn -->
                        <form action="{{ route('cart.remove', $id) }}" method="POST" class="absolute top-4 right-4">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-red-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </form>

                        <div class="w-24 h-24 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center">
                            @if($item['image'])
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            @endif
                        </div>

                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900 leading-tight pr-6">{{ $item['name'] }}</h3>
                                <div class="text-blue-600 font-bold mt-1">${{ number_format($item['unit_price'], 2) }}</div>
                            </div>
                            
                            <div class="flex items-center justify-between mt-3">
                                <form action="{{ route('cart.update', $id) }}" method="POST" class="flex items-center bg-gray-50 rounded-lg border">
                                    @csrf
                                    <button type="submit" onclick="this.form.quantity.value=Math.max(1, parseInt(this.form.quantity.value)-1);" class="px-3 py-1 text-gray-600 font-bold">-</button>
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" class="w-10 text-center bg-transparent border-none p-0 text-sm font-bold focus:ring-0">
                                    <button type="submit" onclick="this.form.quantity.value=parseInt(this.form.quantity.value)+1;" class="px-3 py-1 text-gray-600 font-bold">+</button>
                                </form>
                                <span class="font-bold text-gray-900">${{ number_format($item['line_total'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Sticky Checkout Bar -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="max-w-3xl mx-auto flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total</p>
                        <p class="text-2xl font-extrabold text-gray-900">${{ number_format($subtotal, 2) }}</p>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="bg-blue-600 text-white px-8 py-3.5 rounded-xl font-bold text-lg hover:bg-blue-700 transition shadow-sm">
                        Checkout
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
