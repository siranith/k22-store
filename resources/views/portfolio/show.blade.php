@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-8">
            <a href="{{ route('portfolio.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Portfolio</a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-8 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6 md:p-12">
                <!-- Product Image Gallery -->
                <div class="flex flex-col gap-4">
                    <!-- Main Image -->
                    <div class="bg-gray-200 rounded-lg overflow-hidden h-96 flex items-center justify-center">
                        @if($product->image)
                            <img id="mainImage" 
                                 src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Product Details -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">SKU</h3>
                        <p class="text-gray-900 font-mono">{{ $product->sku ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="flex flex-col gap-6">
                    <!-- Title & Category -->
                    <div>
                        <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium mb-3">
                            {{ $product->category->name ?? 'Uncategorized' }}
                        </span>
                        <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                    </div>

                    <!-- Price & Stock -->
                    <div class="border-t border-b py-4">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-gray-600">Price</span>
                            <span class="text-3xl font-bold text-blue-600">${{ number_format($product->price, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Stock Status</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $product->stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $product->stock > 0 ? 'In Stock (' . $product->stock . ')' : 'Out of Stock' }}
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Description</h2>
                        <p class="text-gray-600 leading-relaxed">
                            {{ $product->description ?? 'No description available for this product.' }}
                        </p>
                    </div>

                    <!-- Add to Cart Form -->
                    <div class="bg-blue-50 p-6 rounded-lg mt-4">
                        <form action="{{ route('cart.add', $product) }}" method="POST" class="flex flex-col gap-4">
                            @csrf
                            <div class="flex items-center gap-4">
                                <label for="quantity" class="text-gray-700 font-medium">Quantity:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border">
                            </div>
                            
                            @if($product->stock > 0)
                                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition flex justify-center items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Add to Cart
                                </button>
                            @else
                                <button type="button" disabled class="w-full bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                                    Out of Stock
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->count() > 0)
            <div class="mt-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Related Products</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
                            <!-- Product Image -->
                            <div class="relative h-40 bg-gray-200 overflow-hidden">
                                @if($related->image)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($related->image) }}" 
                                         alt="{{ $related->name }}"
                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="p-4">
                                <h3 class="text-base font-semibold text-gray-900 mb-2">
                                    <a href="{{ route('portfolio.product', $related) }}" class="hover:text-blue-600">
                                        {{ $related->name }}
                                    </a>
                                </h3>

                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-blue-600">
                                        ${{ number_format($related->price, 2) }}
                                    </span>
                                    <a href="{{ route('portfolio.product', $related) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                        View →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
