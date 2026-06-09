<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\View\View;
use Illuminate\Http\Request;
class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withCount('products')->get();
        $query = Product::where('is_active', true)->with('category');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        $products = $query->paginate(12)->withQueryString();

        return view('portfolio.index', compact('products', 'categories'));
    }

    public function category(Category $category): View
    {
        $categories = Category::withCount('products')->get();
        $products = $category->products()
            ->where('is_active', true)
            ->paginate(12)
            ->withQueryString();

        return view('portfolio.index', compact('category', 'products', 'categories'));
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
