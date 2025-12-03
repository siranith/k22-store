<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->get();
        $products = Product::where('is_active', true)
            ->with('category')
            ->paginate(12);

        return view('portfolio.index', compact('products', 'categories'));
    }

    public function category(Category $category): View
    {
        $categories = Category::withCount('products')->get();
        $products = $category->products()
            ->where('is_active', true)
            ->paginate(12);

        return view('portfolio.category', compact('category', 'products', 'categories'));
    }

    public function product(Product $product): View
    {
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return view('portfolio.show', compact('product', 'relatedProducts'));
    }
}
