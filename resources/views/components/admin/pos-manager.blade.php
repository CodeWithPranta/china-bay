<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $catFilter = '';

    /** @var array<string, array{key: string, product_id: int, variant_id: ?int, name: string, detail: string, price: float, stock: int, qty: int}> */
    public array $cart = [];

    public float $discount = 0;

    public string $customerSearch = '';

    public ?int $customer_id = null;

    public string $payment_method = 'cash';

    public float $paid = 0;

    public string $note = '';

    public ?int $receiptId = null;

    public function updatedSearch(): void
    {
        $this->resetPage('productsPage');
    }

    public function updatedCatFilter(): void
    {
        $this->resetPage('productsPage');
    }

    #[Computed]
    public function categories()
    {
        return \App\Models\Category::active()->ordered()->get();
    }

    #[Computed]
    public function results()
    {
        return Product::query()
            ->where('status', 'active')
            ->where('stock_qty', '>', 0)
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")))
            ->when($this->catFilter, fn ($q) => $q->where('category_id', $this->catFilter))
            ->with(['media', 'variants'])
            ->orderBy('name')
            ->paginate(12, pageName: 'productsPage');
    }

    #[Computed]
    public function customers()
    {
        if (trim($this->customerSearch) === '') {
            return collect();
        }

        return User::query()
            ->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->customerSearch}%")
                ->orWhere('phone', 'like', "%{$this->customerSearch}%"))
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function subtotal(): float
    {
        $sum = 0;

        foreach ($this->cart as $line) {
            $sum += $line['price'] * $line['qty'];
        }

        return round($sum, 2);
    }

    #[Computed]
    public function total(): float
    {
        return max(0, round($this->subtotal - $this->discount, 2));
    }

    #[Computed]
    public function receipt()
    {
        return $this->receiptId ? Sale::with(['items.product', 'customer', 'cashier'])->find($this->receiptId) : null;
    }

    #[Computed]
    public function todaySales()
    {
        return Sale::with('customer')
            ->whereDate('created_at', today())
            ->orderByDesc('id')
            ->limit(10)
            ->get();
    }

    public function addToCart(int $productId, ?int $variantId = null): void
    {
        $product = Product::with('variants')->findOrFail($productId);
        $variant = $variantId ? $product->variants->firstWhere('id', $variantId) : null;

        $key = $variant ? "v-{$variant->id}" : "p-{$product->id}";
        $stock = $variant ? $variant->stock_qty : $product->stock_qty;
        $inCart = $this->cart[$key]['qty'] ?? 0;

        if ($inCart + 1 > $stock) {
            $this->addError('cart', "Only {$stock} in stock for {$product->name}.");
            return;
        }

        $this->cart[$key] ??= [
            'key' => $key,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'name' => $product->name,
            'detail' => $variant?->name ?? $product->sku,
            'price' => (float) ($variant?->price_override ?? $product->price),
            'stock' => $stock,
            'qty' => 0,
        ];
        $this->cart[$key]['qty']++;
        $this->resetValidation('cart');
    }

    public function setQty(string $key, int $qty): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['qty'] = max(0, min($qty, $this->cart[$key]['stock']));

        if ($this->cart[$key]['qty'] === 0) {
            unset($this->cart[$key]);
        }
    }

    public function setLinePrice(string $key, float $price): void
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['price'] = max(0, $price);
        }
    }

    public function removeLine(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function clearCart(): void
    {
        $this->reset(['cart', 'discount', 'paid', 'note', 'customer_id', 'customerSearch', 'receiptId']);
        $this->resetValidation();
    }

    public function applyPercent(int $percent): void
    {
        $this->discount = round($this->subtotal * $percent / 100, 2);
    }

    public function pickCustomer(int $id): void
    {
        $this->customer_id = $id;
        $user = User::find($id);
        $this->customerSearch = $user ? "{$user->name} · {$user->phone}" : '';
    }

    public function clearCustomer(): void
    {
        $this->customer_id = null;
        $this->customerSearch = '';
    }

    public function complete(): void
    {
        abort_unless(auth()->user()?->can('pos.sell'), 403);

        $this->validate([
            'discount' => ['numeric', 'min:0', "max:{$this->subtotal}"],
            'paid' => ['numeric', 'min:0'],
            'payment_method' => ['in:cash,bkash,nagad,card'],
            'customer_id' => ['nullable', 'exists:users,id'],
        ]);

        if ($this->cart === []) {
            $this->addError('cart', 'Cart is empty.');
            return;
        }

        $sale = DB::transaction(function () {
            $code = 'POS-'.now()->format('Ymd').'-'.str_pad((string) (Sale::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

            $sale = Sale::create([
                'code' => $code,
                'customer_id' => $this->customer_id,
                'cashier_id' => auth()->id(),
                'subtotal' => $this->subtotal,
                'discount' => $this->discount,
                'total' => $this->total,
                'paid' => $this->paid,
                'payment_method' => $this->payment_method,
                'note' => $this->note ?: null,
                'status' => 'completed',
            ]);

            foreach ($this->cart as $line) {
                $product = Product::lockForUpdate()->findOrFail($line['product_id']);

                if ($line['variant_id']) {
                    $variant = ProductVariant::lockForUpdate()->findOrFail($line['variant_id']);

                    if ($variant->stock_qty < $line['qty']) {
                        throw new \RuntimeException("Insufficient stock for {$line['name']} ({$variant->name}).");
                    }

                    $variant->decrement('stock_qty', $line['qty']);
                } else {
                    if ($product->stock_qty < $line['qty']) {
                        throw new \RuntimeException("Insufficient stock for {$line['name']}.");
                    }

                    $product->decrement('stock_qty', $line['qty']);
                }

                $sale->items()->create([
                    'product_id' => $product->id,
                    'variant_id' => $line['variant_id'],
                    'qty' => $line['qty'],
                    'unit_price' => $line['price'],
                    'unit_cost' => $product->totalCost(),
                ]);
            }

            return $sale;
        });

        $this->receiptId = $sale->id;
        $this->reset(['cart', 'discount', 'paid', 'note', 'customer_id', 'customerSearch']);
        unset($this->todaySales);
    }

    public function void(int $id): void
    {
        abort_unless(auth()->user()?->can('pos.sell'), 403);

        DB::transaction(function () use ($id) {
            $sale = Sale::with('items')->where('status', 'completed')->findOrFail($id);
            $sale->update(['status' => 'voided']);

            foreach ($sale->items as $item) {
                if ($item->variant_id) {
                    ProductVariant::where('id', $item->variant_id)->increment('stock_qty', $item->qty);
                } else {
                    Product::where('id', $item->product_id)->increment('stock_qty', $item->qty);
                }
            }
        });

        unset($this->todaySales);
    }
};
?>

<div class="grid gap-4 xl:grid-cols-3">
    <div class="space-y-3 xl:col-span-2">
        <div class="card flex flex-col gap-2 sm:flex-row">
            <input wire:model.live.debounce.300ms="search" class="input sm:max-w-xs" placeholder="🔍 Search product / SKU…">
            <select wire:model.live="catFilter" class="input sm:max-w-52">
                <option value="">All categories</option>
                @foreach ($this->categories as $c)<option value="{{ $c->id }}">{{ $c->localizedName() }}</option>@endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($this->results as $p)
                <div class="card !p-3" wire:key="pos-{{ $p->id }}">
                    <button wire:click="addToCart({{ $p->id }})" class="block w-full text-start">
                        <span class="flex items-center gap-2">
                            <x-shop.product-thumb :product="$p->toCardArray()" class="size-10" />
                            <span class="min-w-0"><b class="block truncate text-xs">{{ $p->name }}</b><span class="text-[11px] font-bold text-brand">৳{{ number_format((float) $p->price) }}</span> <span class="text-[10px] text-slate-400">({{ $p->stock_qty }})</span></span>
                        </span>
                    </button>
                    @if ($p->variants->isNotEmpty())
                        <div class="mt-1.5 flex flex-wrap gap-1">
                            @foreach ($p->variants as $v)
                                <button wire:click="addToCart({{ $p->id }}, {{ $v->id }})" class="rounded border border-slate-200 px-1.5 py-0.5 text-[10px] hover:border-brand hover:text-brand" title="{{ $v->stock_qty }} in stock">{{ $v->name }}</button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="col-span-full py-6 text-center text-xs text-slate-400">No in-stock products match.</p>
            @endforelse
        </div>

        <div>{{ $this->results->links() }}</div>

        <div class="card">
            <div class="mb-2 flex items-center justify-between"><h3 class="font-bold">Today's sales</h3><span class="text-xs text-slate-500">{{ $this->todaySales->where('status', 'completed')->count() }} completed · ৳{{ number_format($this->todaySales->where('status', 'completed')->sum('total')) }}</span></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] text-left text-xs">
                    <thead class="text-slate-500"><tr><th class="py-1.5">Code</th><th>Items</th><th>Total</th><th>Pay</th><th>Status</th><th class="text-right">Void</th></tr></thead>
                    <tbody>
                    @forelse ($this->todaySales as $s)
                        <tr class="border-t border-slate-100" wire:key="sale-{{ $s->id }}">
                            <td class="py-1.5 font-mono font-bold">{{ $s->code }}</td>
                            <td class="py-1.5">{{ $s->customer?->name ?? 'Walk-in' }}</td>
                            <td class="py-1.5 font-bold">৳{{ number_format((float) $s->total) }}</td>
                            <td class="py-1.5">{{ ucfirst($s->payment_method) }}</td>
                            <td class="py-1.5"><span class="rounded-full px-2 py-0.5 font-bold {{ $s->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $s->status }}</span></td>
                            <td class="py-1.5 text-right">@if ($s->status === 'completed')<button wire:click="void({{ $s->id }})" wire:confirm="Void {{ $s->code }} and restore stock?" class="text-brand hover:underline">Void</button>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-slate-400">No sales today yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card h-fit xl:sticky xl:top-20">
        <div class="flex items-center justify-between">
            <h3 class="font-bold">🧾 Current sale</h3>
            @if ($cart)<button wire:click="clearCart" class="text-xs text-slate-400 hover:text-brand">Clear</button>@endif
        </div>

        @error('cart') <p class="mt-1 text-[11px] text-brand">{{ $message }}</p> @enderror

        <div class="mt-2 max-h-64 space-y-2 overflow-y-auto">
            @forelse ($cart as $line)
                <div class="rounded-lg border border-slate-100 p-2 text-xs" wire:key="cart-{{ $line['key'] }}">
                    <div class="flex items-start justify-between gap-2">
                        <b class="min-w-0 flex-1">{{ $line['name'] }}<span class="block font-normal text-slate-500">{{ $line['detail'] }}</span></b>
                        <button wire:click="removeLine('{{ $line['key'] }}')" class="text-brand">✕</button>
                    </div>
                    <div class="mt-1.5 flex items-center gap-1.5">
                        <button wire:click="setQty('{{ $line['key'] }}', {{ $line['qty'] - 1 }})" class="btn-outline size-6 text-xs">−</button>
                        <span class="w-6 text-center font-bold">{{ $line['qty'] }}</span>
                        <button wire:click="setQty('{{ $line['key'] }}', {{ $line['qty'] + 1 }})" class="btn-outline size-6 text-xs">+</button>
                        <span class="text-slate-400">×</span>
                        <input type="number" min="0" step="0.01" value="{{ $line['price'] }}" wire:change="setLinePrice('{{ $line['key'] }}', $event.target.value)" class="input w-20 !px-1.5 !py-1 text-xs">
                        <b class="ms-auto">৳{{ number_format($line['price'] * $line['qty']) }}</b>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-xs text-slate-400">Tap a product to add it here.</p>
            @endforelse
        </div>

        <div class="mt-3 space-y-2 border-t border-slate-100 pt-3 text-sm">
            <div class="flex justify-between text-xs text-slate-500"><span>Subtotal</span><b class="text-ink">৳{{ number_format($this->subtotal) }}</b></div>
            <div class="flex items-center gap-2">
                <label class="label !mb-0 text-xs">Discount ৳</label>
                <input wire:model.live="discount" type="number" min="0" step="0.01" class="input !py-1.5 text-xs">
                <button wire:click="applyPercent(5)" class="btn-outline px-2 py-1.5 text-[11px]">5%</button>
                <button wire:click="applyPercent(10)" class="btn-outline px-2 py-1.5 text-[11px]">10%</button>
            </div>
            @error('discount') <p class="text-[11px] text-brand">{{ $message }}</p> @enderror
            <div class="flex justify-between font-extrabold"><span>Total</span><span class="text-brand">৳{{ number_format($this->total) }}</span></div>

            <div>
                <label class="label text-xs">Customer (optional)</label>
                @if ($customer_id)
                    <p class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold">{{ $customerSearch }} <button wire:click="clearCustomer" class="text-brand">✕</button></p>
                @else
                    <input wire:model.live.debounce.300ms="customerSearch" class="input text-xs" placeholder="Search name / phone…">
                    @if ($this->customers->isNotEmpty())
                        <div class="mt-1 overflow-hidden rounded-lg border border-slate-100">
                            @foreach ($this->customers as $u)
                                <button wire:click="pickCustomer({{ $u->id }})" class="block w-full px-3 py-1.5 text-start text-xs hover:bg-slate-50">{{ $u->name }} <span class="text-slate-400">{{ $u->phone }}</span></button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div><label class="label text-xs">Payment</label>
                    <select wire:model="payment_method" class="input text-xs">
                        <option value="cash">Cash</option><option value="bkash">bKash</option><option value="nagad">Nagad</option><option value="card">Card</option>
                    </select>
                </div>
                <div><label class="label text-xs">Paid ৳</label><input wire:model.live="paid" type="number" min="0" step="0.01" class="input text-xs"></div>
            </div>
            <div class="flex justify-between text-xs"><span class="text-slate-500">{{ $paid >= $this->total ? 'Change' : 'Due' }}</span><b>৳{{ number_format(abs($paid - $this->total)) }}</b></div>

            <div><label class="label text-xs">Note</label><input wire:model="note" class="input text-xs" placeholder="Optional…"></div>

            <button wire:click="complete" wire:loading.attr="disabled" class="btn w-full py-2.5" @disabled($cart === [])>Complete sale →</button>
        </div>
    </div>
</div>

@if ($this->receipt)
    <div class="fixed inset-0 z-50 grid place-items-center bg-navy/60 p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-5 text-sm">
            <div class="text-center">
                <b class="text-base">ChinaBayBD</b>
                <p class="text-xs text-slate-500">{{ $this->receipt->code }} · {{ $this->receipt->created_at->format('d M Y, h:i A') }}</p>
                <p class="text-xs text-slate-500">Cashier: {{ $this->receipt->cashier?->name }} · {{ ucfirst($this->receipt->payment_method) }}</p>
            </div>
            <div class="my-3 border-t border-dashed border-slate-200"></div>
            @foreach ($this->receipt->items as $item)
                <p class="flex justify-between gap-2 py-0.5"><span>{{ $item->product->name }} × {{ $item->qty }}</span><b>৳{{ number_format((float) $item->unit_price * $item->qty) }}</b></p>
            @endforeach
            <div class="my-3 border-t border-dashed border-slate-200"></div>
            <p class="flex justify-between text-xs text-slate-500"><span>Subtotal</span><span>৳{{ number_format((float) $this->receipt->subtotal) }}</span></p>
            <p class="flex justify-between text-xs text-slate-500"><span>Discount</span><span>− ৳{{ number_format((float) $this->receipt->discount) }}</span></p>
            <p class="flex justify-between font-extrabold"><span>Total</span><span>৳{{ number_format((float) $this->receipt->total) }}</span></p>
            <p class="flex justify-between text-xs text-slate-500"><span>Paid</span><span>৳{{ number_format((float) $this->receipt->paid) }}</span></p>
            <p class="flex justify-between text-xs"><span>{{ (float) $this->receipt->paid >= (float) $this->receipt->total ? 'Change' : 'Due' }}</span><b>৳{{ number_format(abs((float) $this->receipt->paid - (float) $this->receipt->total)) }}</b></p>
            <p class="mt-1 flex justify-between text-[11px] text-emerald-600"><span>Profit on this sale</span><b>৳{{ number_format($this->receipt->profit()) }}</b></p>
            <div class="mt-4 flex gap-2">
                <button onclick="window.print()" class="btn-outline flex-1 py-2 text-xs">🖨 Print</button>
                <button wire:click="clearCart" class="btn flex-1 py-2 text-xs">New sale →</button>
            </div>
        </div>
    </div>
@endif
