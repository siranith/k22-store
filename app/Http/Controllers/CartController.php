<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);
        $subtotal = collect($cart)->sum('line_total');

        return view('cart.index', compact('cart', 'subtotal'));
    }

    public function add(Request $request, Product $product)
    {
        $cart = Session::get('cart', []);

        $quantity = $request->input('quantity', 1);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
            $cart[$product->id]['line_total'] = $cart[$product->id]['quantity'] * $cart[$product->id]['unit_price'];
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'image' => $product->image,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'line_total' => $product->price * $quantity,
            ];
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function remove(Request $request, $id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Product removed from cart.');
    }

    public function update(Request $request, $id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            $quantity = $request->input('quantity', 1);
            if ($quantity > 0) {
                $cart[$id]['quantity'] = $quantity;
                $cart[$id]['line_total'] = $cart[$id]['quantity'] * $cart[$id]['unit_price'];
                Session::put('cart', $cart);
            } else {
                unset($cart[$id]);
                Session::put('cart', $cart);
            }
        }

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
    }

    public function checkout()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = collect($cart)->sum('line_total');

        return view('cart.checkout', compact('cart', 'subtotal'));
    }

    public function processCheckout(Request $request, \App\Services\TelegramService $telegramService)
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        $data = [
            'contact_name' => $request->input('contact_name'),
            'contact_number' => $request->input('contact_number'),
            'address' => $request->input('address'),
            'cod' => true, // To mark note as 'pending' automatically in Sale::createFromCart
            'customer_type' => 'guest',
            'source' => 'portfolio',
        ];

        // createFromCart expects $cart as array of items with product_id, unit_price, quantity, line_total
        // Our session cart format is already compatible:
        // [ 'product_id' => ..., 'unit_price' => ..., 'quantity' => ..., 'line_total' => ... ]
        $cartItems = array_values($cart); // Re-index array

        // Get the first admin user ID to assign as the creator of this online order
        $adminUser = \App\Models\User::first();
        $adminUserId = $adminUser ? $adminUser->id : 1;

        $sale = Sale::createFromCart($data, $cartItems, $adminUserId);

        // Optionally, ensure the note is explicitly 'pending' if createFromCart changes behavior
        if ($sale->note !== 'pending') {
            $sale->update(['note' => 'pending']);
        }

        // Send Telegram Notification
        $telegramService->sendNewOrderNotification($sale);

        // Clear cart
        Session::forget('cart');

        return redirect()->route('checkout.success')->with('success', 'Order placed successfully! Waiting for admin approval.');
    }

    public function success()
    {
        return view('cart.success');
    }
}
