@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-32">
    <!-- Simple Header -->
    <div class="bg-white px-4 py-4 border-b flex items-center shadow-sm sticky top-16 z-40">
        <a href="{{ route('cart.index') }}" class="text-gray-500 hover:text-blue-600 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Checkout</h1>
    </div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 space-y-6">
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-3">
                        <ul class="text-sm text-red-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
            @csrf
            
            <!-- Delivery Details Form -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
                <div class="p-4 border-b bg-gray-50">
                    <h2 class="font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Delivery Details
                    </h2>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name') }}" required class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 bg-gray-50 border">
                    </div>
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="tel" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 bg-gray-50 border">
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                        <textarea id="address" name="address" rows="3" required class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 bg-gray-50 border">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-8">
                <div class="p-4 border-b bg-gray-50">
                    <h2 class="font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Order Summary
                    </h2>
                </div>
                <div class="p-4 space-y-3">
                    @foreach($cart as $item)
                        <div class="flex justify-between items-start text-sm">
                            <span class="text-gray-600 pr-4"><span class="font-bold text-gray-900">{{ $item['quantity'] }}x</span> {{ $item['name'] }}</span>
                            <span class="font-medium text-gray-900">${{ number_format($item['line_total'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="p-4 border-t bg-gray-50">
                    <div class="flex justify-between items-center text-lg">
                        <span class="font-bold text-gray-700">Total to Pay</span>
                        <span class="font-extrabold text-blue-600 text-2xl">${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 text-center">Payment will be collected upon delivery/approval.</p>
                </div>
            </div>

            <!-- Sticky Submit Button -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <div class="max-w-2xl mx-auto">
                    <button type="submit" class="w-full bg-blue-600 text-white px-6 py-4 rounded-xl font-bold text-lg hover:bg-blue-700 transition shadow-sm flex justify-center items-center gap-2">
                        Place Order
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
