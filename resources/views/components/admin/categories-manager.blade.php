<?php

use App\Models\Category;
use App\Models\SiteLocale;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $open = false;

    public ?int $editingId = null;

    public string $activeTab = '';

    /** @var array<string, string> locale => name */
    public array $names = [];

    public string $icon = '';

    public string $parent_id = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    public string $toast = '';

    public function updatedSearch(): void
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
    public function rows()
    {
        return Category::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name_en', 'like', "%{$this->search}%")
                ->orWhere('name_bn', 'like', "%{$this->search}%")
                ->orWhere('slug', 'like', "%{$this->search}%")))
            ->with(['translations', 'products'])
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->paginate(12);
    }

    #[Computed]
    public function parents()
    {
        return Category::ordered()
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->get();
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('categories.manage'), 403);
        $this->resetForm();
        $this->open = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('categories.manage'), 403);

        $category = Category::with('translations')->findOrFail($id);

        $this->resetForm();
        $this->editingId = $category->id;
        $this->activeTab = $this->defaultCode();

        foreach ($category->translations as $t) {
            $this->names[$t->locale] = $t->name;
        }

        $this->names[$this->defaultCode()] ??= $category->name_en ?: $category->name_bn;

        $this->icon = $category->icon ?? '';
        $this->parent_id = (string) ($category->parent_id ?? '');
        $this->sort_order = $category->sort_order;
        $this->is_active = (bool) $category->is_active;
        $this->open = true;
    }

    public function closeForm(): void
    {
        $this->open = false;
        $this->resetForm();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('categories.manage'), 403);

        $default = $this->defaultCode();

        $labels = [];
        foreach (SiteLocale::activeCodes() as $code) {
            $labels["names.{$code}"] = "Name ({$code})";
        }

        $this->validate([
            "names.{$default}" => ['required', 'string', 'max:255'],
            'names.*' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ], [], $labels);

        $data = [
            'icon' => $this->icon ?: null,
            'parent_id' => $this->parent_id ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        // Keep legacy columns in sync so the storefront never breaks.
        $data['name_en'] = $this->names['en'] ?? $this->names[$default] ?? null;
        $data['name_bn'] = $this->names['bn'] ?? $this->names[$default] ?? null;

        $category = $this->editingId ? Category::findOrFail($this->editingId) : new Category;

        if (! $this->editingId) {
            $base = Str::slug($this->names[$default]) ?: 'category';
            $slug = $base;
            $i = 2;
            while (Category::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $data['slug'] = $slug;
        }

        $category->fill($data);
        $category->save();
        $isEdit = $this->editingId !== null;

        foreach (SiteLocale::activeCodes() as $code) {
            $name = trim($this->names[$code] ?? '');

            if ($name === '') {
                $category->translations()->where('locale', $code)->delete();
                continue;
            }

            $category->translations()->updateOrCreate(['locale' => $code], ['name' => $name]);
        }

        unset($this->rows, $this->parents);
        $this->closeForm();
        $this->toast = $isEdit ? 'Category updated ✓' : 'Category created ✓';
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('categories.manage'), 403);

        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            $this->toast = 'Cannot delete: category has products.';
            return;
        }

        Category::where('parent_id', $id)->update(['parent_id' => null]);
        $category->delete();
        unset($this->rows, $this->parents);
        $this->toast = 'Category deleted ✓';
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()?->can('categories.manage'), 403);

        $category = Category::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);
        SiteLocale::flushCache();
        unset($this->rows);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'names', 'icon', 'parent_id', 'sort_order', 'is_active', 'toast']);
        $this->sort_order = 0;
        $this->is_active = true;
        $this->activeTab = $this->defaultCode();
        $this->resetValidation();
    }
};
?>

<div>
    <div class="card flex flex-col gap-2 sm:flex-row">
        <input wire:model.live.debounce.300ms="search" class="input sm:max-w-xs" placeholder="🔍 Search categories…">
        @can('categories.manage')
            <button wire:click="openCreate" class="btn px-4 py-2.5 text-sm sm:ml-auto">+ Add category</button>
        @endcan
    </div>

    @if ($toast)
        <p class="mt-3 rounded-lg bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">{{ $toast }}</p>
    @endif

    <div class="card mt-4 overflow-x-auto p-0">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Category</th><th>Slug</th><th>Products</th><th>Active</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @forelse ($this->rows as $c)
                <tr class="border-t border-slate-100" wire:key="cat-{{ $c->id }}">
                    <td class="px-4 py-2.5"><span class="flex items-center gap-2"><span class="text-lg">{{ $c->icon }}</span><b>{{ $c->localizedName() }}</b></span></td>
                    <td class="px-2 py-2.5 font-mono text-xs text-slate-500">{{ $c->slug }}</td>
                    <td class="px-2 py-2.5">{{ $c->products_count }}</td>
                    <td class="px-2 py-2.5">
                        @can('categories.manage')
                            <button wire:click="toggleActive({{ $c->id }})" class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $c->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $c->is_active ? 'On' : 'Off' }}</button>
                        @else
                            <span class="text-xs">{{ $c->is_active ? 'On' : 'Off' }}</span>
                        @endcan
                    </td>
                    <td class="px-4 py-2.5 text-right">
                        @can('categories.manage')
                            <button wire:click="edit({{ $c->id }})" class="btn-outline px-2.5 py-1.5 text-xs">Edit</button>
                            <button wire:click="delete({{ $c->id }})" wire:confirm="Delete {{ $c->slug }}?" class="rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-bold text-brand">Del</button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400">No categories found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $this->rows->links() }}</div>

    @if ($open)
        <div class="fixed inset-0 z-40 flex justify-end bg-navy/50">
            <div class="w-full max-w-md overflow-y-auto bg-white p-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold">{{ $editingId ? 'Edit category' : 'Add category' }}</h3>
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
                        <div x-show="$wire.activeTab === '{{ $l->code }}'" dir="{{ $l->direction }}" wire:key="cat-tab-{{ $l->code }}">
                            <label class="label">Name ({{ $l->native_name }}) @if ($l->code === $this->defaultCode())<span class="text-brand">*</span>@endif</label>
                            <input wire:model="names.{{ $l->code }}" class="input" placeholder="{{ $l->code === $this->defaultCode() ? 'Required' : 'Optional — falls back to '.$this->defaultCode() }}">
                            @error("names.{$l->code}") <span class="text-[11px] text-brand">{{ $message }}</span> @enderror
                        </div>
                    @endforeach

                    <div class="grid grid-cols-2 gap-2" dir="ltr">
                        <div><label class="label">Icon (emoji)</label><input wire:model="icon" class="input" placeholder="📱"></div>
                        <div><label class="label">Sort order</label><input wire:model="sort_order" type="number" min="0" class="input"></div>
                    </div>

                    <div dir="ltr">
                        <label class="label">Parent category</label>
                        <select wire:model="parent_id" class="input">
                            <option value="">None (top level)</option>
                            @foreach ($this->parents as $p)<option value="{{ $p->id }}">{{ $p->localizedName() }}</option>@endforeach
                        </select>
                    </div>

                    <label class="flex items-center gap-2 text-sm" dir="ltr"><input wire:model="is_active" type="checkbox" class="size-4 accent-[#c8102e]"> Active</label>

                    <button wire:click="save" wire:loading.attr="disabled" class="btn w-full py-2.5">Save category</button>
                </div>
            </div>
        </div>
    @endif
</div>
