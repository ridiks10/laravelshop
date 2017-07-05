<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CoinGate\Merchant\Order as CoinGateOrder;
use App\Order;
use App\Cart;
use App\Product;

class CartController extends Controller
{
    public function show()
    {

        if (! session()->has('cart')) {
            return view("cart.show");
        }

        $oldCart = session()->get('cart');
        $cart = new Cart($oldCart);

        return view("cart.show", [
            'cartitems'  => $cart->items,
            'totalPrice' => $cart->totalPrice,
        ]);
    }

    public function add(Request $request, $id)
    {
        $product = Product::find($id);
        $oldCart = session()->has('cart') ? session()->get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->add($product, $product->id);
        $request->session()->put('cart', $cart);

        return redirect("/");
    }

    public function destroy()
    {
        session()->forget('cart');

        return redirect("/");
    }

    public function getCheckout()
    {
        if (! session()->has('cart')) {
            return view("checkout.checkout");
        }

        $oldCart = session()->get('cart');
        $cart = new Cart($oldCart);
        $total = $cart->totalPrice;

        return view('checkout.checkout', ['total' => $total]);
    }

    public function pay(Request $request)
    {
        $oldCart = session()->get('cart');
        $cart = collect(new Cart($oldCart));

        $cart->map(function ($product){
            dd($product);
        });

        $order = Order::create([
            'user_id'             => auth()->id(),
            'coingate_invoice_id' => 1,
            'token'               => 1221,
            'total_price'         => request('total'),
            'status'              => 'unpaid',
        ]);

        if ($request->input('method') == 'coingate') {
            $order = $this->coinGateCreate($order);
        }

        session()->forget('cart');

        if ($order) {
            echo $order->status;

            return redirect($order->payment_url);
        } else {
            print_r($order);
        }
    }

    /**
     * @param \CoinGate\Merchant\Order $order
     * @return bool|\CoinGate\Merchant\Order
     */
    public function coinGateCreate(CoinGateOrder $order): CoinGateOrder
    {
        $order = CoinGateOrder::create([
            'order_id'         => (int) $order->id,
            'price'            => (float) $order->total_price,
            'currency'         => env('COINGATE_CURRENCY', 'COINGATE_CURRENCY'),
            'receive_currency' => env('COINGATE_RECEIVE_CURRENCY', 'COINGATE_RECEIVE_CURRENCY'),
            'callback_url'     => route('card.callback', $order->token),
            'cancel_url'       => route('card.destroy', $order->id),
            'success_url'      => route('card.orders'),
        ], [], [
            'environment' => 'sandbox',
            'app_id'      => env('COINGATE_APP_ID', 'COINGATE_APP_ID'),
            'api_key'     => env('COINGATE_API_KEY', 'COINGATE_API_KEY'),
            'api_secret'  => env('COINGATE_API_SECRET', 'COINGATE_API_SECRET'),
        ]);

        return $order;
    }

    public function callback(Request $request)
    {
        $order = Order::find($request->input('order_id'));
        if ($request->input('token') == $order->token) {
            $status = null;
            if ($request->input('status') == 'paid') {
                if ($request->input('price') >= $order->total_price) {
                    $status = 'paid';
                }
            } else {
                $status = $request->input('status');
            }

            if (! is_null($status)) {
                $order->update(['status' => $status]);
            }
        }
    }

    public function myOrders()
    {
        $user_id = auth()->id();
        $myOrders = Order::get()->where('user_id', $user_id);

        return view('myOrders.myOrders', compact('myOrders'));
    }
}
