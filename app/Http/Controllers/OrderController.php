<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\Produk;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    /**
     * Menambahkan produk ke keranjang (order dengan status pending).
     */
    public function addToCart($id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        if (!$customer) {
            return redirect()->route('auth.redirect')
                ->with('msgError', 'Data customer tidak ditemukan, silakan login ulang');
        }

        $produk = Produk::findOrFail($id);

        $order = Order::firstOrCreate(
            ['customer_id' => $customer->id, 'status' => 'pending'],
            ['total_harga' => 0]
        );

        $orderItem = OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'produk_id' => $produk->id],
            ['quantity' => 1, 'harga' => $produk->harga]
        );

        if (!$orderItem->wasRecentlyCreated) {
            $orderItem->quantity++;
            $orderItem->save();
        }

        $order->total_harga += $produk->harga;
        $order->save();

        return redirect()->route('order.cart')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    /**
     * Menampilkan halaman keranjang belanja customer.
     */
    public function viewCart()
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        if (!$customer) {
            return redirect()->route('auth.redirect')
                ->with('msgError', 'Data customer tidak ditemukan, silakan login ulang');
        }

        $order = Order::where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'paid'])
            ->first();

        if ($order) {
            $order->load('orderItems.produk');
        }

        return view('v_order.cart', compact('order'));
    }
}
