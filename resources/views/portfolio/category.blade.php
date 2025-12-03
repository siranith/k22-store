@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-8">
            <a href="{{ route('portfolio.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Portfolio</a>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Categories -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Categories</h2>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('portfolio.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">
                                All Products
                            </a>
                        </li>
                        @foreach($categories as $cat)
                            <li>
                                <a href="{{ route('portfolio.category', $cat) }}"
                                   class="text-gray-700 hover:text-blue-600 flex justify-between {{ $cat->id === $category->id ? 'text-blue-600 font-bold' : '' }}">
                                    <span>{{ $cat->name }}</span>
                                    <span class="text-gray-500 text-sm">({{ $cat->products_count }})</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="lg:w-3/4">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ $category->name }}</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($products as $product)
                        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
                            <!-- Product Image -->
                            <div class="relative h-48 bg-gray-200 overflow-hidden">
                                @if($product->image)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    <a href="{{ route('portfolio.product', $product) }}" class="hover:text-blue-600">
                                        {{ $product->name }}
                                    </a>
                                </h3>

                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                    {{ $product->description ?? 'No description available' }}
                                </p>

                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-blue-600">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                    <a href="{{ route('portfolio.product', $product) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                        View →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <p class="text-gray-500 text-lg">No products in this category</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
