@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-lg p-10 text-center relative overflow-hidden">
        <!-- Decorative bg circle -->
        <div class="absolute -top-16 -right-16 w-32 h-32 bg-blue-50 rounded-full"></div>
        <div class="absolute -bottom-16 -left-16 w-32 h-32 bg-green-50 rounded-full"></div>
        
        <div class="relative z-10">
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-8 shadow-sm">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h2 class="text-3xl font-extrabold text-gray-900 mb-4">Order Placed!</h2>
            
            @if(session('success'))
                <p class="text-lg text-gray-600 mb-8">{{ session('success') }}</p>
            @else
                <p class="text-gray-600 mb-8 text-lg">Your order has been sent directly to the store admin. We will contact you shortly!</p>
            @endif

            <div class="bg-gray-50 rounded-xl p-4 mb-8 text-sm text-gray-500">
                <p>Keep an eye on your phone, our team might call you to confirm the delivery.</p>
            </div>

            <a href="{{ route('portfolio.index') }}" class="inline-flex w-full justify-center items-center gap-2 bg-blue-600 text-white font-bold py-4 px-4 rounded-xl hover:bg-blue-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
