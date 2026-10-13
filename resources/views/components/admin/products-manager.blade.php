<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\SiteLocale;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $catFilter = '';

    public bool $open = false;

    public ?int $editingId = null;

    public string $activeTab = '';

    /** @var array<string, string> locale => value */
    public array $names = [];

    public array $subtitles = [];

    public array $descriptions = [];

    public string $category_id = '';

    public string $brand_id = '';

    public string $price = '';

    public string $old_price = '';

    /** Adjustable landed-cost components (৳). Total auto-sums into `cost`. */
    public string $import_cost = '';

    public string $transport_cost = '';

    public string $packaging_cost = '';

    public string $ads_cost = '';

    public string $other_cost = '';

    public string $sku = '';

    public int $stock_qty = 0;

    public string $status = 'active';

    public bool $is_featured = false;

    public string $emoji = '';

    /** @var array<int, array{id?: int, name: string, sku: string, price_override: string, stock_qty: int}> */
    public array $variants = [];

    /** @var array<int, mixed> pending uploads */
    public array $photos = [];

    /** @var array<int, array{id: int, url: string}> */
    public array $existingMedia = [];

    public ?int $primaryMediaId = null;

    public string $toast = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCatFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function locales()
    {
        $all = SiteLocale::active();

        usort($all, fn ($a, $b) => ($b->is_default <=> $a->is_default) ?: ($a->sort_order <=> $b->sort_order));

        return $all;
    }

    public function defaultCode(): string
    {
        foreach ($this->locales as $locale) {
            if ($locale->is_default) {
                return $locale->code;
            }
        }

        return config('app.fallback_locale', 'en');
    }

    #[Computed]
    public function categories()
    {
        return Category::active()->ordered()->get();
    }

    #[Computed]
    public function formCostTotal(): float
    {
        return (float) ($this->import_cost ?: 0)
            + (float) ($this->transport_cost ?: 0)
            + (float) ($this->packaging_cost ?: 0)
            + (float) ($this->ads_cost ?: 0)
            + (float) ($this->other_cost ?: 0);
    }

    #[Computed]
    public function brands()
    {
        return Brand::active()->orderBy('name')->get();
    }

    #[Computed]
    public function rows()
    {
        return Product::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")))
            ->when($this->catFilter, fn ($q) => $q->where('category_id', $this->catFilter))
            ->with(['category', 'media', 'translations'])
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('products.manage'), 403);
        $this->resetForm();
        $this->open = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('products.manage'), 403);

        $product = Product::with(['translations', 'variants', 'media'])->findOrFail($id);

        $this->resetForm();
        $this->editingId = $product->id;
        $this->activeTab = $this->defaultCode();

        foreach ($product->translations as $t) {
            $this->names[$t->locale] = $t->name;
            $this->subtitles[$t->locale] = $t->subtitle ?? '';
            $this->descriptions[$t->locale] = $t->description ?? '';
        }

        // Fall back to the canonical columns when no translation row exists yet.
        $this->names[$this->defaultCode()] ??= $product->name;
        $this->subtitles[$this->defaultCode()] ??= $product->subtitle ?? '';

        $this->category_id = (string) $product->category_id;
        $this->brand_id = (string) ($product->brand_id ?? '');
        $this->price = (string) $product->price;
        $this->old_price = (string) ($product->old_price ?? '');
        $this->import_cost = (string) ($product->import_cost ?? '');
        $this->transport_cost = (string) ($product->transport_cost ?? '');
        $this->packaging_cost = (string) ($product->packaging_cost ?? '');
        $this->ads_cost = (string) ($product->ads_cost ?? '');
        $this->other_cost = (string) ($product->other_cost ?? '');
        $this->sku = $product->sku;
        $this->stock_qty = $product->stock_qty;
        $this->status = $product->status;
        $this->is_featured = (bool) $product->is_featured;
        $this->emoji = $product->emoji ?? '';

        foreach ($product->variants as $v) {
            $this->variants[] = [
                'id' => $v->id,
                'name' => $v->name,
                'sku' => $v->sku ?? '',
                'price_override' => $v->price_override !== null ? (string) $v->price_override : '',
                'stock_qty' => $v->stock_qty,
            ];
        }

        foreach ($product->media as $m) {
            $this->existingMedia[] = ['id' => $m->id, 'url' => $m->url];

            if ($m->is_primary) {
                $this->primaryMediaId = $m->id;
            }
        }

        $this->open = true;
    }

    public function closeForm(): void
    {
        $this->open = false;
        $this->resetForm();
    }

    public function addVariantRow(): void
    {
        $this->variants[] = ['name' => '', 'sku' => '', 'price_override' => '', 'stock_qty' => 0];
    }

    public function removeVariantRow(int $index): void
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    public function removePhoto(int $index): void
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function removeMedia(int $id): void
    {
        abort_unless(auth()->user()?->can('products.manage'), 403);

        $media = ProductMedia::find($id);

        if ($media && ($this->editingId === null || $media->product_id === $this->editingId)) {
            $this->deleteMediaFile($media->url);
            $media->delete();
        }

        $this->existingMedia = array_values(array_filter(
            $this->existingMedia,
            fn ($m) => $m['id'] !== $id
        ));

        if ($this->primaryMediaId === $id) {
            $this->primaryMediaId = null;
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('products.manage'), 403);

        $default = $this->defaultCode();

        $rules = [
            "names.{$default}" => ['required', 'string', 'max:255'],
            'names.*' => ['nullable', 'string', 'max:255'],
            'subtitles.*' => ['nullable', 'string', 'max:255'],
            'descriptions.*' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'import_cost' => ['nullable', 'numeric', 'min:0'],
            'transport_cost' => ['nullable', 'numeric', 'min:0'],
            'packaging_cost' => ['nullable', 'numeric', 'min:0'],
            'ads_cost' => ['nullable', 'numeric', 'min:0'],
            'other_cost' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($this->editingId)],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,draft'],
            'photos.*' => ['image', 'max:2048'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.price_override' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_qty' => ['required', 'integer', 'min:0'],
        ];

        $names = [];
        foreach (SiteLocale::activeCodes() as $code) {
            $names["names.{$code}"] = "Name ({$code})";
        }

        $this->validate($rules, [], $names);

        $slug = Str::slug($this->names[$default]);
        $slug = $this->uniqueSlug($slug === '' ? 'product' : $slug);

        $data = [
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id ?: null,
            'name' => $this->names[$default],
            'subtitle' => $this->subtitles[$default] ?? null,
            'price' => $this->price,
            'old_price' => $this->old_price ?: null,
            'import_cost' => $this->import_cost ?: null,
            'transport_cost' => $this->transport_cost ?: null,
            'packaging_cost' => $this->packaging_cost ?: null,
            'ads_cost' => $this->ads_cost ?: null,
            'other_cost' => $this->other_cost ?: null,
            'cost' => $this->formCostTotal ?: null,
            'sku' => $this->sku,
            'stock_qty' => $this->stock_qty,
            'emoji' => $this->emoji ?: null,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
        ];

        // Slugs are permanent URLs: only set on create, never on rename.
        if (! $this->editingId) {
            $data['slug'] = $slug;
        }

        // Keep the legacy description columns in sync for the storefront fallback.
        $defaultDesc = $this->descriptions[$default] ?? null;
        if (in_array($default, ['en', 'bn'], true)) {
            $data["description_{$default}"] = $defaultDesc;
        } else {
            $data['description_en'] = $defaultDesc;
        }

        $product = $this->editingId ? Product::findOrFail($this->editingId) : new Product;
        $product->fill($data);
        $product->save();
        $isEdit = $this->editingId !== null;

        // Translations for every active locale that has a name.
        foreach (SiteLocale::activeCodes() as $code) {
            $name = trim($this->names[$code] ?? '');

            if ($name === '') {
                $product->translations()->where('locale', $code)->delete();
                continue;
            }

            $product->translations()->updateOrCreate(
                ['locale' => $code],
                [
                    'name' => $name,
                    'subtitle' => $this->subtitles[$code] ?? null,
                    'description' => $this->descriptions[$code] ?? null,
                ]
            );
        }

        $this->syncVariants($product);
        $this->syncMedia($product);

        unset($this->rows);
        $this->closeForm();
        $this->toast = $isEdit ? 'Product updated ✓' : 'Product created ✓';
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('products.manage'), 403);

        $product = Product::with('media')->findOrFail($id);

        foreach ($product->media as $media) {
            $this->deleteMediaFile($media->url);
        }

        $product->delete();
        unset($this->rows);
        $this->toast = 'Product deleted ✓';
    }

    public function toggleFeatured(int $id): void
    {
        abort_unless(auth()->user()?->can('products.manage'), 403);

        $product = Product::findOrFail($id);
        $product->update(['is_featured' => ! $product->is_featured]);
        unset($this->rows);
    }

    public function toggleStatus(int $id): void
    {
        abort_unless(auth()->user()?->can('products.manage'), 403);

        $product = Product::findOrFail($id);
        $product->update(['status' => $product->status === 'active' ? 'draft' : 'active']);
        unset($this->rows);
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'names', 'subtitles', 'descriptions', 'category_id',
            'brand_id', 'price', 'old_price', 'import_cost', 'transport_cost',
            'packaging_cost', 'ads_cost', 'other_cost', 'sku', 'stock_qty', 'status',
            'is_featured', 'emoji', 'variants', 'photos', 'existingMedia',
            'primaryMediaId', 'toast',
        ]);
        $this->stock_qty = 0;
        $this->status = 'active';
        $this->activeTab = $this->defaultCode();
        $this->resetValidation();
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;

        while (Product::where('slug', $slug)->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function syncVariants(Product $product): void
    {
        $kept = [];

        foreach (array_values($this->variants) as $i => $row) {
            $variant = $product->variants()->updateOrCreate(
                ['id' => $row['id'] ?? 0],
                [
                    'name' => $row['name'],
                    'sku' => $row['sku'] ?: null,
                    'price_override' => $row['price_override'] === '' ? null : $row['price_override'],
                    'stock_qty' => (int) $row['stock_qty'],
                    'sort_order' => $i,
                ]
            );
            $kept[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $kept)->delete();
    }

    private function syncMedia(Product $product): void
    {
        $order = count($this->existingMedia);

        foreach ($this->photos as $upload) {
            $path = $upload->store('products', 'public');
            $media = $product->media()->create([
                'type' => 'image',
                'url' => '/storage/'.$path,
                'sort_order' => $order++,
                'is_primary' => false,
            ]);
            $this->existingMedia[] = ['id' => $media->id, 'url' => $media->url];
        }

        $this->photos = [];

        if ($this->primaryMediaId === null && $this->existingMedia !== []) {
            $this->primaryMediaId = $this->existingMedia[0]['id'];
        }

        foreach ($this->existingMedia as $i => $item) {
            ProductMedia::where('id', $item['id'])->where('product_id', $product->id)->update([
                'sort_order' => $i,
                'is_primary' => $item['id'] === $this->primaryMediaId,
            ]);
        }
    }

    private function deleteMediaFile(?string $url): void
    {
        if ($url && str_starts_with($url, '/storage/')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(substr($url, strlen('/storage/')));
        }
    }
};
?>

<div>
    <div class="card flex flex-col gap-2 sm:flex-row">
        <input wire:model.live.debounce.300ms="search" class="input sm:max-w-xs" placeholder="🔍 Search products…">
        <select wire:model.live="catFilter" class="input sm:max-w-52">
            <option value="">All categories</option>
            @foreach ($this->categories as $c)
                <option value="{{ $c->id }}">{{ $c->localizedName() }}</option>
            @endforeach
        </select>
        @can('products.manage')
            <button wire:click="openCreate" class="btn px-4 py-2.5 text-sm sm:ml-auto">+ Add product</button>
        @endcan
    </div>

    @if ($toast)
        <p class="mt-3 rounded-lg bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">{{ $toast }}</p>
    @endif

    <div class="card mt-4 overflow-x-auto p-0">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @forelse ($this->rows as $p)
                <tr class="border-t border-slate-100 hover:bg-slate-50/60" wire:key="product-{{ $p->id }}">
                    <td class="px-4 py-2.5">
                        <span class="flex items-center gap-2">
                            <x-shop.product-thumb :product="$p->toCardArray()" class="size-10" />
                            <span><b>{{ $p->name }}</b><span class="block font-mono text-[11px] text-slate-500">{{ $p->sku }}</span></span>
                            @if ($p->is_featured)
                                <span class="text-amber-500" title="Featured">★</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-xs">{{ $p->category?->localizedName() }}</td>
                    <td class="px-2 py-2.5 font-bold">৳{{ number_format((float) $p->price) }}
                        @if ($p->old_price)<span class="text-[11px] font-normal text-slate-400 line-through">৳{{ number_format((float) $p->old_price) }}</span>@endif
                    </td>
                    <td class="px-2 py-2.5"><span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $p->stock_qty < 15 ? 'bg-red-100 text-brand' : 'bg-emerald-100 text-emerald-700' }}">{{ $p->stock_qty }}</span></td>
                    <td class="px-2 py-2.5">
                        @can('products.manage')
                            <button wire:click="toggleStatus({{ $p->id }})" class="relative inline-flex h-5 w-9 items-center rounded-full {{ $p->status === 'active' ? 'bg-emerald-500' : 'bg-slate-300' }}"><span class="size-4 translate-x-{{ $p->status === 'active' ? '4' : '1' }} rounded-full bg-white"></span></button>
                        @else
                            <span class="text-xs font-semibold">{{ ucfirst($p->status) }}</span>
                        @endcan
                    </td>
                    <td class="px-4 py-2.5 text-right">
                        @can('products.manage')
                            <button wire:click="toggleFeatured({{ $p->id }})" class="btn-outline px-2.5 py-1.5 text-xs" title="Toggle featured">★</button>
                            <button wire:click="edit({{ $p->id }})" class="btn-outline px-2.5 py-1.5 text-xs">Edit</button>
                            <button wire:click="delete({{ $p->id }})" wire:confirm="Delete {{ $p->name }} permanently?" class="rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-bold text-brand">Del</button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">No products found. @can('products.manage') Click “+ Add product” to create one. @endcan</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $this->rows->links() }}</div>

    @if ($open)
        <div class="fixed inset-0 z-40 flex justify-end bg-navy/50">
            <div class="w-full max-w-xl overflow-y-auto bg-white p-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold">{{ $editingId ? 'Edit product' : 'Add product' }}</h3>
                    <button wire:click="closeForm" class="btn-outline px-2.5 py-1.5 text-xs">✕</button>
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach ($this->locales as $l)
                        <button wire:click="$set('activeTab', '{{ $l->code }}')" class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $activeTab === $l->code ? 'border-brand bg-brand text-white' : 'border-slate-200 text-slate-500' }}">
                            {{ $l->native_name }}
                            @if ($l->code === $this->defaultCode())<span title="Default language — required"> *</span>@endif
                        </button>
                    @endforeach
                </div>

                <div class="mt-3 space-y-3 text-sm">
                    @foreach ($this->locales as $l)
                        <div x-show="$wire.activeTab === '{{ $l->code }}'" dir="{{ $l->direction }}" wire:key="tab-{{ $l->code }}">
                            <div>
                                <label class="label">Title ({{ $l->native_name }}) @if ($l->code === $this->defaultCode())<span class="text-brand">*</span>@endif</label>
                                <input wire:model="names.{{ $l->code }}" class="input" placeholder="{{ $l->code === $this->defaultCode() ? 'Required' : 'Optional — falls back to '.$this->defaultCode() }}">
                                @error("names.{$l->code}") <span class="text-[11px] text-brand">{{ $message }}</span> @enderror
                            </div>
                            <div class="mt-3">
                                <label class="label">Subtitle ({{ $l->native_name }})</label>
                                <input wire:model="subtitles.{{ $l->code }}" class="input">
                            </div>
                            <div class="mt-3">
                                <label class="label">Description ({{ $l->native_name }})</label>
                                <textarea wire:model="descriptions.{{ $l->code }}" rows="3" class="input"></textarea>
                            </div>
                        </div>
                    @endforeach

                    <div class="grid grid-cols-2 gap-2" dir="ltr">
                        <div><label class="label">Category *</label>
                            <select wire:model="category_id" class="input">
                                <option value="">Select…</option>
                                @foreach ($this->categories as $c)<option value="{{ $c->id }}">{{ $c->localizedName() }}</option>@endforeach
                            </select>
                            @error('category_id') <span class="text-[11px] text-brand">{{ $message }}</span> @enderror
                        </div>
                        <div><label class="label">Brand</label>
                            <select wire:model="brand_id" class="input">
                                <option value="">None</option>
                                @foreach ($this->brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2" dir="ltr">
                        <div><label class="label">Price ৳ *</label><input wire:model="price" type="number" min="0" step="0.01" class="input">@error('price') <span class="text-[11px] text-brand">{{ $message }}</span> @enderror</div>
                        <div><label class="label">Old price ৳</label><input wire:model="old_price" type="number" min="0" step="0.01" class="input"></div>
                        <div><label class="label">Emoji</label><input wire:model="emoji" class="input" placeholder="⌚"></div>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-3" dir="ltr">
                        <div class="flex items-center justify-between">
                            <label class="label !mb-0">Landed cost breakdown ৳</label>
                            <span class="text-xs">Total: <b class="text-brand">৳{{ number_format($this->formCostTotal, 2) }}</b></span>
                        </div>
                        <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <div><label class="label">Import cost</label><input wire:model.live="import_cost" type="number" min="0" step="0.01" class="input" placeholder="0"></div>
                            <div><label class="label">Transportation</label><input wire:model.live="transport_cost" type="number" min="0" step="0.01" class="input" placeholder="0"></div>
                            <div><label class="label">Packaging</label><input wire:model.live="packaging_cost" type="number" min="0" step="0.01" class="input" placeholder="0"></div>
                            <div><label class="label">Advertisement</label><input wire:model.live="ads_cost" type="number" min="0" step="0.01" class="input" placeholder="0"></div>
                            <div><label class="label">Other cost</label><input wire:model.live="other_cost" type="number" min="0" step="0.01" class="input" placeholder="0"></div>
                            <div class="flex items-end pb-2 text-[11px] text-slate-500">
                                @if (is_numeric($price) && (float) $price > 0)
                                    Profit: <b class="ms-1 {{ ((float) $price - $this->formCostTotal) >= 0 ? 'text-emerald-600' : 'text-brand' }}">৳{{ number_format((float) $price - $this->formCostTotal, 2) }}</b>
                                @else
                                    Set a price to see profit.
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2" dir="ltr">
                        <div><label class="label">SKU *</label><input wire:model="sku" class="input font-mono">@error('sku') <span class="text-[11px] text-brand">{{ $message }}</span> @enderror</div>
                        <div><label class="label">Stock *</label><input wire:model="stock_qty" type="number" min="0" class="input"></div>
                    </div>

                    <div class="flex gap-4 text-sm" dir="ltr">
                        <label class="flex items-center gap-2"><input wire:model="is_featured" type="checkbox" class="size-4 accent-[#c8102e]"> Featured</label>
                        <label class="flex items-center gap-2"><input wire:model="status" type="radio" value="active" class="size-4"> Active</label>
                        <label class="flex items-center gap-2"><input wire:model="status" type="radio" value="draft" class="size-4"> Draft</label>
                    </div>

                    <div dir="ltr">
                        <div class="flex items-center justify-between"><label class="label">Variants</label><button wire:click="addVariantRow" class="text-xs font-bold text-brand">+ Add variant</button></div>
                        @foreach ($variants as $i => $v)
                            <div class="mt-1.5 grid grid-cols-12 gap-1.5" wire:key="variant-{{ $i }}">
                                <input wire:model="variants.{{ $i }}.name" class="input col-span-5" placeholder="Black / M">
                                <input wire:model="variants.{{ $i }}.sku" class="input col-span-3 font-mono" placeholder="SKU">
                                <input wire:model="variants.{{ $i }}.price_override" type="number" min="0" step="0.01" class="input col-span-2" placeholder="+৳">
                                <input wire:model="variants.{{ $i }}.stock_qty" type="number" min="0" class="input col-span-1 !px-1 text-center" title="Stock">
                                <button wire:click="removeVariantRow({{ $i }})" class="col-span-1 text-brand" title="Remove">✕</button>
                            </div>
                            @error("variants.{$i}.name") <span class="text-[11px] text-brand">{{ $message }}</span> @enderror
                        @endforeach
                    </div>

                    <div dir="ltr">
                        <label class="label">Images (first = primary unless starred)</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($existingMedia as $m)
                                <span class="relative" wire:key="media-{{ $m['id'] }}">
                                    <img src="{{ $m['url'] }}" alt="" class="size-16 rounded-lg border object-cover {{ $primaryMediaId === $m['id'] ? 'border-brand ring-2 ring-brand/30' : 'border-slate-200' }}">
                                    <button wire:click="$set('primaryMediaId', {{ $m['id'] }})" class="absolute left-0.5 top-0.5 rounded bg-white/90 px-1 text-xs {{ $primaryMediaId === $m['id'] ? 'text-amber-500' : 'text-slate-300' }}" title="Set primary">★</button>
                                    <button wire:click="removeMedia({{ $m['id'] }})" class="absolute right-0.5 top-0.5 rounded bg-white/90 px-1 text-xs text-brand" title="Delete">✕</button>
                                </span>
                            @endforeach
                            @foreach ($photos as $i => $photo)
                                <span class="relative" wire:key="photo-{{ $i }}">
                                    <img src="{{ $photo->temporaryUrl() }}" alt="" class="size-16 rounded-lg border border-dashed border-slate-300 object-cover">
                                    <button wire:click="removePhoto({{ $i }})" class="absolute right-0.5 top-0.5 rounded bg-white/90 px-1 text-xs text-brand">✕</button>
                                </span>
                            @endforeach
                            <label class="grid size-16 cursor-pointer place-items-center rounded-lg border-2 border-dashed border-slate-200 text-xs text-slate-400 hover:border-brand">📷 Add<input wire:model="photos" type="file" accept="image/*" multiple class="hidden"></label>
                        </div>
                        @error('photos.*') <span class="text-[11px] text-brand">{{ $message }}</span> @enderror
                    </div>

                    <button wire:click="save" wire:loading.attr="disabled" class="btn w-full py-2.5">Save product</button>
                </div>
            </div>
        </div>
    @endif
</div>
