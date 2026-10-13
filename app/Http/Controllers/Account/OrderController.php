<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Logged-in shopper's order history.
     */
    public function index(Request $request): View
    {
        $orders = Order::with('items')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(10);

        return view('pages.account.orders', ['orders' => $orders]);
    }

    /**
     * Order detail (ownership enforced).
     */
    public function show(Request $request, string $code): View
    {
        $order = Order::with(['items.product'])
            ->where('code', $code)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return view('pages.account.order-detail', ['order' => $order]);
    }

    /**
     * Customer cancel request for pending/confirmed orders.
     */
    public function cancel(Request $request, string $code): RedirectResponse
    {
        $order = Order::with('items')
            ->where('code', $code)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($order->isCancellable(), 422, 'This order can no longer be cancelled.');

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)->increment('stock_qty', $item->qty);
            }
        });

        return back()->with('status', "Order {$order->code} cancelled; stock restored.");
    }
}
