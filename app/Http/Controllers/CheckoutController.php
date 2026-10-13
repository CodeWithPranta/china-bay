<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Notifications\QueuedOrderPlaced;
use App\Support\ShopDemo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Place a storefront order from the cart.
     */
    public function store(Request $request): RedirectResponse
    {
        $zones = collect(ShopDemo::deliveryZones())->mapWithKeys(fn ($z) => [$z['district'] => $z]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^01[0-9]{9}$/'],
            'address' => ['required', 'string', 'max:500'],
            'district' => ['required', 'string', 'in:'.implode(',', $zones->keys()->all())],
            'thana' => ['nullable', 'string', 'max:120'],
            'delivery_method' => ['required', 'in:inside,outside'],
            'payment_method' => ['required', 'in:cod,bkash,nagad,card'],
            'coupon_code' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $products = Product::active()->whereIn('id', collect($data['items'])->pluck('id'))->get()->keyBy('id');

        if ($products->count() !== collect($data['items'])->pluck('id')->unique()->count()) {
            return back()->withErrors(['items' => 'Some products are no longer available.'])->withInput();
        }

        $coupon = strtoupper(trim($data['coupon_code'] ?? ''));
        $coupon = in_array($coupon, ['CHINABAY10', 'FLASH50'], true) ? $coupon : null;

        $order = DB::transaction(function () use ($data, $products, $zones, $coupon, $request) {
            $subtotal = 0;
            $lines = [];

            foreach ($data['items'] as $item) {
                $product = $products[$item['id']];

                if ($product->stock_qty < $item['qty']) {
                    throw ValidationException::withMessages(
                        ['items' => "Only {$product->stock_qty} left for {$product->name}."]
                    );
                }

                $subtotal += (float) $product->price * $item['qty'];
                $lines[] = ['product' => $product, 'qty' => $item['qty']];
            }

            $discount = match ($coupon) {
                'CHINABAY10' => (int) round($subtotal * 0.10),
                'FLASH50' => min(50, (int) $subtotal),
                default => 0,
            };

            $zone = $zones[$data['district']];
            $delivery = $data['delivery_method'] === 'inside' ? $zone['charge_inside'] : $zone['charge_outside'];

            $code = 'CB-'.now()->format('Ymd').'-'.str_pad((string) (Order::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'code' => $code,
                'user_id' => $request->user()?->id,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'district' => $data['district'],
                'thana' => $data['thana'] ?? null,
                'delivery_method' => $data['delivery_method'],
                'delivery_charge' => $delivery,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'coupon_code' => $coupon,
                'total' => $subtotal - $discount + $delivery,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'note' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'name' => $line['product']->name,
                    'unit_price' => $line['product']->price,
                    'qty' => $line['qty'],
                ]);
                $line['product']->decrement('stock_qty', $line['qty']);
            }

            return $order;
        });

        $user = $request->user();

        if ($user && $user->email) {
            try {
                $user->notify(new QueuedOrderPlaced($order->code, '৳'.number_format((float) $order->total)));
            } catch (\Throwable $e) {
                Log::warning('Order notification failed.', ['order' => $order->code, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('order.success', ['code' => $order->code]);
    }

    /**
     * Thank-you page for a freshly placed order.
     */
    public function success(Request $request): View
    {
        $order = Order::with('items')->where('code', $request->query('code'))->firstOrFail();

        return view('pages.order-success', ['order' => $order]);
    }
}
