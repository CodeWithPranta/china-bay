<?php

use App\Models\Product;
use App\Support\Catalog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

// Server-side catalogue: filters + sort + search run in SQL with
// pagination, so the grid stays fast on huge catalogues. Pure
// presentational behaviour (drawer, pills scroll) stays in Alpine/JS.
new class extends Component
{
    use WithPagination;

    #[Url(as: 'cat')]
    public string $cat = 'all';

    #[Url(as: 'q')]
    public string $q = '';

    #[Url(as: 'sort')]
    public string $sort = '';

    /** @var array<int, string> */
    public array $cats = [];

    public string $price = '';

    public float $rating = 0;

    public bool $inStock = false;

    public function mount(): void
    {
        $this->cat = request('cat', 'all');
        $this->q = request('q', '');
        $this->sort = request('sort', '');
        if ($this->cat !== 'all') {
            $this->cats = [$this->cat];
        }
    }

    public function updatedCats(): void
    {
        // Keep the pill highlight in sync with the checkbox group.
        $this->cat = count($this->cats) === 1 ? $this->cats[0] : 'all';
        $this->resetPage();
    }

    public function updatedPrice(): void
    {
        $this->resetPage();
    }

    public function updatedRating(): void
    {
        $this->resetPage();
    }

    public function updatedInStock(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function pick(string $slug): void
    {
        $this->cat = $slug;
        $this->cats = $slug === 'all' ? [] : [$slug];
        $this->resetPage();
    }

    public function clearAll(): void
    {
        $this->reset(['cat', 'cats', 'price', 'sort', 'q']);
        $this->rating = 0;
        $this->inStock = false;
        $this->resetPage();
    }

    #[Computed]
    public function pills()
    {
        return Catalog::categories();
    }

    #[Computed]
    public function items()
    {
        $query = Product::query()->active()->with(['category', 'media']);

        if (! empty($this->cats) && ! in_array('all', $this->cats, true)) {
            $query->whereHas('category', fn ($q) => $q->whereIn('slug', $this->cats));
        } elseif ($this->cat !== 'all') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $this->cat));
        }

        if ($this->q !== '') {
            $term = '%'.$this->q.'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('subtitle', 'like', $term));
        }

        if ($this->price !== '') {
            [$min, $max] = array_map('intval', explode('-', $this->price) + [0, PHP_INT_MAX]);
            $query->whereBetween('price', [$min, $max]);
        }

        if ($this->rating > 0) {
            $query->where('rating_avg', '>=', $this->rating);
        }

        if ($this->inStock) {
            $query->where('stock_qty', '>', 0);
        }

        match ($this->sort) {
            'lo' => $query->orderBy('price'),
            'hi' => $query->orderByDesc('price'),
            'off' => $query->orderByRaw('(1 - price / NULLIF(old_price, 0)) DESC'),
            default => $query->orderBy('id'),
        };

        return $query->paginate(12);
    }

    #[Computed]
    public function activeCount(): int
    {
        return count($this->cats)
            + ($this->price !== '' ? 1 : 0)
            + ($this->rating > 0 ? 1 : 0)
            + ($this->inStock ? 1 : 0);
    }
};
?>

<div x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false">
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2">
        <p class="text-xs text-slate-500">{{ $this->items->total() }} products found
            @if ($q !== '') · <span>“{{ $q }}”</span> @endif
        </p>
        <div class="ms-auto flex items-center gap-2 text-xs">
            <label class="flex items-center gap-1.5 text-slate-500">Sort
                <select wire:model.live="sort" class="max-w-36 rounded-md border border-slate-200 bg-white px-2 py-1.5 text-ink sm:max-w-none">
                    <option value="">Featured</option>
                    <option value="lo">Price: low → high</option>
                    <option value="hi">Price: high → low</option>
                    <option value="off">Biggest discount</option>
                </select>
            </label>
        </div>
    </div>

    {{-- Category pills --}}
    <div class="-mx-3 mt-3 flex snap-x gap-2 overflow-x-auto px-3 pb-1 text-xs [scrollbar-width:none] sm:mx-0 sm:px-0 [&::-webkit-scrollbar]:hidden">
        @php $pillBase = 'shrink-0 snap-start whitespace-nowrap rounded-full border px-4 py-2 shadow-sm transition '; @endphp
        <button wire:click="pick('all')" class="{{ $pillBase }} {{ $cat === 'all' ? 'border-brand bg-brand font-semibold text-white' : 'border-slate-200 bg-white font-medium text-ink hover:border-brand hover:text-brand' }}">All · সব</button>
        @foreach ($this->pills as $c)
            <button wire:click="pick('{{ $c['slug'] }}')" class="{{ $pillBase }} {{ $cat === $c['slug'] ? 'border-brand bg-brand font-semibold text-white' : 'border-slate-200 bg-white font-medium text-ink hover:border-brand hover:text-brand' }}">{{ $c['emoji'] }} {{ $c['en'] }}</button>
        @endforeach
    </div>

    <div class="mt-4 pb-8 lg:flex lg:gap-5">
        {{-- Mobile filter bar --}}
        <div class="mb-3 lg:hidden">
            <button @click="filtersOpen = true" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold shadow-sm transition active:scale-[.98]">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
                Filters · ফিল্টার
                @if ($this->activeCount > 0)
                    <span class="grid min-w-5 place-items-center rounded-full bg-brand px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $this->activeCount }}</span>
                @endif
            </button>
        </div>

        <div x-show="filtersOpen" x-cloak x-transition.opacity.duration.200ms @click="filtersOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden" aria-hidden="true"></div>

        {{-- Sidebar filters --}}
        <aside :class="filtersOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] space-y-4 overflow-y-auto bg-white p-4 text-xs shadow-2xl transition-transform duration-300 lg:static lg:z-auto lg:block lg:w-56 lg:shrink-0 lg:translate-x-0 lg:bg-transparent lg:p-0 lg:shadow-none"
            aria-label="Product filters">
            <div class="flex items-center justify-between lg:hidden">
                <b class="text-sm">Filters · ফিল্টার</b>
                <button @click="filtersOpen = false" class="grid size-9 place-items-center rounded-full bg-slate-100 hover:bg-brand hover:text-white" aria-label="Close filters">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18" /></svg>
                </button>
            </div>
            <div class="card !p-3">
                <div class="flex items-center justify-between">
                    <b class="text-sm">Category · ক্যাটাগরি</b>
                    <button wire:click="clearAll" class="text-[11px] font-medium text-brand hover:underline lg:hidden">Clear</button>
                </div>
                <ul class="mt-2 space-y-1.5">
                    @foreach ($this->pills as $c)
                        <li><label class="flex items-center gap-2 py-0.5"><input type="checkbox" wire:model.live="cats" value="{{ $c['slug'] }}" class="size-4 accent-[#c8102e]"> {{ $c['emoji'] }} {{ $c['en'] }}</label></li>
                    @endforeach
                </ul>
            </div>
            <div class="card !p-3">
                <b class="text-sm">Price · দাম (৳)</b>
                <div class="mt-2 space-y-1.5">
                    <label class="flex items-center gap-2 py-0.5"><input type="radio" wire:model.live="price" value="" class="size-4 accent-[#c8102e]"> All prices</label>
                    <label class="flex items-center gap-2 py-0.5"><input type="radio" wire:model.live="price" value="0-1500" class="size-4 accent-[#c8102e]"> Under ৳1,500</label>
                    <label class="flex items-center gap-2 py-0.5"><input type="radio" wire:model.live="price" value="1500-3000" class="size-4 accent-[#c8102e]"> ৳1,500 – ৳3,000</label>
                    <label class="flex items-center gap-2 py-0.5"><input type="radio" wire:model.live="price" value="3000-99999" class="size-4 accent-[#c8102e]"> Above ৳3,000</label>
                </div>
            </div>
            <div class="card !p-3">
                <b class="text-sm">Rating · রেটিং</b>
                <div class="mt-2 space-y-1.5">
                    <label class="flex items-center gap-2 py-0.5"><input type="radio" wire:model.live="rating" value="0" class="size-4 accent-[#c8102e]"> Any</label>
                    <label class="flex items-center gap-2 py-0.5"><input type="radio" wire:model.live="rating" value="4.5" class="size-4 accent-[#c8102e]"> ★ 4.5+</label>
                    <label class="flex items-center gap-2 py-0.5"><input type="radio" wire:model.live="rating" value="4.7" class="size-4 accent-[#c8102e]"> ★ 4.7+</label>
                </div>
                <label class="mt-2 flex items-center gap-2 py-0.5"><input type="checkbox" wire:model.live="inStock" class="size-4 accent-[#c8102e]"> In stock only</label>
            </div>
            <div class="sticky bottom-0 flex gap-2 bg-white pb-1 pt-2 lg:hidden">
                <button wire:click="clearAll" class="btn-outline flex-1 py-2.5 text-xs">Clear all</button>
                <button @click="filtersOpen = false" class="btn flex-1 py-2.5 text-xs">Show results</button>
            </div>
        </aside>

        {{-- Grid --}}
        <div class="min-w-0 flex-1" wire:loading.class="opacity-60">
            @if ($this->items->isEmpty())
                <div class="py-16 text-center">
                    <p class="text-5xl">🔍</p>
                    <b class="mt-2 block">No products found</b>
                    <p class="mt-1 text-xs text-slate-500">দুঃখিত, কোনো পণ্য পাওয়া যায়নি। ফিল্টার বদলে আবার চেষ্টা করুন।</p>
                    <button wire:click="clearAll" class="btn mt-4 px-5 py-2 text-xs">Show all products</button>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                    @foreach ($this->items as $item)
                        <x-shop.product-card :product="$item->toCardArray()" />
                    @endforeach
                </div>
                <div class="mt-6">{{ $this->items->links() }}</div>
            @endif
            <section class="mt-8">
                <x-shop.section-head title="Recently <span>Viewed</span>" />
                <p class="mt-2 rounded-lg border border-dashed border-slate-200 py-6 text-center text-xs text-slate-400">আপনি যে পণ্যগুলো দেখবেন সেগুলো এখানে থাকবে</p>
            </section>
        </div>
    </div>
</div>
