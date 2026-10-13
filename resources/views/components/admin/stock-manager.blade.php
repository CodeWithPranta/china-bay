<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $lowOnly = true;

    /** @var array<int, int> product_id => qty */
    public array $qty = [];

    /** @var array<string, int> "v-{id}" => qty for variants */
    public array $variantQty = [];

    public string $toast = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLowOnly(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function rows()
    {
        return Product::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")))
            ->when($this->lowOnly, fn ($q) => $q->where('stock_qty', '<', 15))
            ->with(['category', 'variants'])
            ->orderBy('stock_qty')
            ->paginate(10);
    }

    public function loadQty(): void
    {
        foreach ($this->rows as $p) {
            $this->qty[$p->id] ??= $p->stock_qty;

            foreach ($p->variants as $v) {
                $this->variantQty["v-{$v->id}"] ??= $v->stock_qty;
            }
        }
    }

    public function saveProduct(int $id): void
    {
        abort_unless(auth()->user()?->can('stock.manage'), 403);

        $product = Product::findOrFail($id);
        $product->update(['stock_qty' => max(0, (int) ($this->qty[$id] ?? $product->stock_qty))]);
        unset($this->rows);
        $this->toast = "{$product->name}: stock set to {$product->stock_qty} ✓";
    }

    public function saveVariant(int $id): void
    {
        abort_unless(auth()->user()?->can('stock.manage'), 403);

        $variant = \App\Models\ProductVariant::findOrFail($id);
        $variant->update(['stock_qty' => max(0, (int) ($this->variantQty["v-{$id}"] ?? $variant->stock_qty))]);
        unset($this->rows);
        $this->toast = "{$variant->name}: stock set to {$variant->stock_qty} ✓";
    }
};
?>

<div>
    @php $this->loadQty(); @endphp

    <div class="card flex flex-col gap-2 sm:flex-row sm:items-center">
        <input wire:model.live.debounce.300ms="search" class="input sm:max-w-xs" placeholder="🔍 Search products…">
        <label class="flex items-center gap-2 text-sm"><input wire:model.live="lowOnly" type="checkbox" class="size-4 accent-[#c8102e]"> Low stock only (&lt; 15)</label>
    </div>

    @if ($toast)
        <p class="mt-3 rounded-lg bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">{{ $toast }}</p>
    @endif

    <div class="card mt-4 overflow-x-auto p-0">
        <table class="w-full min-w-[680px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Product</th><th>Category</th><th>Current</th><th>Adjust</th><th class="text-right">Save</th></tr></thead>
            <tbody>
            @forelse ($this->rows as $p)
                <tr class="border-t border-slate-100" wire:key="stock-{{ $p->id }}">
                    <td class="px-4 py-2.5"><b>{{ $p->name }}</b><span class="block font-mono text-[11px] text-slate-500">{{ $p->sku }}</span></td>
                    <td class="px-2 py-2.5 text-xs">{{ $p->category?->localizedName() }}</td>
                    <td class="px-2 py-2.5"><span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $p->stock_qty < 15 ? 'bg-red-100 text-brand' : 'bg-emerald-100 text-emerald-700' }}">{{ $p->stock_qty }}</span></td>
                    <td class="px-2 py-2.5">
                        <div class="flex items-center gap-1">
                            <button wire:click="$set('qty.{{ $p->id }}', {{ max(0, ($qty[$p->id] ?? $p->stock_qty) - 1) }})" class="btn-outline size-7">−</button>
                            <input wire:model="qty.{{ $p->id }}" type="number" min="0" class="input w-20 !px-2 text-center">
                            <button wire:click="$set('qty.{{ $p->id }}', {{ ($qty[$p->id] ?? $p->stock_qty) + 1 }})" class="btn-outline size-7">+</button>
                        </div>
                    </td>
                    <td class="px-4 py-2.5 text-right"><button wire:click="saveProduct({{ $p->id }})" class="btn px-3 py-1.5 text-xs">Save</button></td>
                </tr>
                @foreach ($p->variants as $v)
                    <tr class="bg-slate-50/60 text-xs text-slate-500" wire:key="stock-v-{{ $v->id }}">
                        <td class="px-8 py-1.5" colspan="2">↳ {{ $v->name }}{{ $v->sku ? " · {$v->sku}" : '' }}</td>
                        <td class="py-1.5">{{ $v->stock_qty }}</td>
                        <td class="py-1.5"><input wire:model="variantQty.v-{{ $v->id }}" type="number" min="0" class="input w-20 !px-2 py-1 text-center text-xs"></td>
                        <td class="px-4 py-1.5 text-right"><button wire:click="saveVariant({{ $v->id }})" class="btn-outline px-2.5 py-1 text-xs">Save</button></td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400">No products match this filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $this->rows->links() }}</div>
</div>
