@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24">
    <!-- Search Section -->
    <!-- <div class="bg-blue-600 pt-6 pb-6 px-4 sticky top-16 z-40 shadow-sm">
        <form action="{{ route('portfolio.index') }}" method="GET" class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" class="block w-full pl-10 pr-3 py-3 border border-transparent rounded-xl leading-5 bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white focus:border-white sm:text-sm shadow-sm" placeholder="Search products...">
        </form>
    </div> -->

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <!-- Success Message from cart addition -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Category Navigation (Horizontal Scroll) -->
        <div class="mb-6 overflow-x-auto pb-2 scrollbar-hide">
            <div class="flex gap-3 whitespace-nowrap">
                <a href="{{ route('portfolio.index') }}" class="px-5 py-2 rounded-full font-medium text-sm transition shadow-sm {{ request()->routeIs('portfolio.index') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                    All
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('portfolio.category', $cat) }}" class="px-5 py-2 rounded-full font-medium text-sm transition shadow-sm {{ isset($category) && $category->id == $cat->id ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Products Grid (2-column on mobile) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($products as $product)
                @php
                    $productData = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => $product->price,
                        'stock' => $product->stock,
                        'category_name' => $product->category->name ?? 'Uncategorized',
                        'image_url' => $product->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) : null,
                    ];
                @endphp
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col relative" x-data @click="$dispatch('open-product-sheet', {{ json_encode($productData) }})">
                    <!-- Product Image -->
                    <div class="relative aspect-square bg-gray-100 overflow-hidden">
                        @if($product->image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <div class="p-3 flex flex-col flex-grow justify-between">
                        <div>
                            <p class="text-xs text-blue-600 font-semibold mb-1 truncate">{{ $product->category->name ?? 'Uncategorized' }}</p>
                            <h3 class="text-sm font-bold text-gray-900 leading-tight mb-2 line-clamp-2">
                                {{ $product->name }}
                            </h3>
                        </div>
                        
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-base font-extrabold text-gray-900">${{ number_format($product->price, 2) }}</span>
                            <button class="bg-blue-100 text-blue-600 p-2 rounded-full hover:bg-blue-200 transition" @click.stop="$dispatch('open-product-sheet', {{ json_encode($productData) }})">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 bg-white rounded-2xl shadow-sm">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-gray-500 font-medium">No products found.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- Include the Bottom Sheet Component -->
@include('components.product-bottom-sheet')

<style>
    /* Hide scrollbar for category horizontal scroll */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection
