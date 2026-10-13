<?php

use App\Models\Brand;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public bool $open = false;

    public ?int $editingId = null;

    public string $name = '';

    public bool $is_active = true;

    public $logo = null;

    public ?string $logoUrl = null;

    public bool $removeLogo = false;

    public string $toast = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function rows()
    {
        return Brand::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('slug', 'like', "%{$this->search}%")))
            ->withCount('products')
            ->orderBy('name')
            ->paginate(12);
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

        $brand = Brand::findOrFail($id);

        $this->resetForm();
        $this->editingId = $brand->id;
        $this->name = $brand->name;
        $this->is_active = (bool) $brand->is_active;
        $this->logoUrl = $brand->logo;
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

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $brand = $this->editingId ? Brand::findOrFail($this->editingId) : new Brand;

        if (! $this->editingId) {
            $base = Str::slug($this->name) ?: 'brand';
            $slug = $base;
            $i = 2;
            while (Brand::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $brand->slug = $slug;
        }

        $brand->name = $this->name;
        $brand->is_active = $this->is_active;

        if ($this->removeLogo && $brand->logo) {
            $this->deleteLogoFile($brand->logo);
            $brand->logo = null;
        }

        if ($this->logo) {
            if ($brand->logo) {
                $this->deleteLogoFile($brand->logo);
            }
            $brand->logo = '/storage/'.$this->logo->store('brands', 'public');
        }

        $brand->save();
        $isEdit = $this->editingId !== null;

        unset($this->rows);
        $this->closeForm();
        $this->toast = $isEdit ? 'Brand updated ✓' : 'Brand created ✓';
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('categories.manage'), 403);

        $brand = Brand::withCount('products')->findOrFail($id);

        if ($brand->products_count > 0) {
            $this->toast = 'Cannot delete: brand has products.';
            return;
        }

        $this->deleteLogoFile($brand->logo);
        $brand->delete();
        unset($this->rows);
        $this->toast = 'Brand deleted ✓';
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()?->can('categories.manage'), 403);

        $brand = Brand::findOrFail($id);
        $brand->update(['is_active' => ! $brand->is_active]);
        unset($this->rows);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'is_active', 'logo', 'logoUrl', 'removeLogo', 'toast']);
        $this->is_active = true;
        $this->resetValidation();
    }

    private function deleteLogoFile(?string $url): void
    {
        if ($url && str_starts_with($url, '/storage/')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(substr($url, strlen('/storage/')));
        }
    }
};
?>

<div>
    <div class="card flex flex-col gap-2 sm:flex-row">
        <input wire:model.live.debounce.300ms="search" class="input sm:max-w-xs" placeholder="🔍 Search brands…">
        @can('categories.manage')
            <button wire:click="openCreate" class="btn px-4 py-2.5 text-sm sm:ml-auto">+ Add brand</button>
        @endcan
    </div>

    @if ($toast)
        <p class="mt-3 rounded-lg bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">{{ $toast }}</p>
    @endif

    <div class="card mt-4 overflow-x-auto p-0">
        <table class="w-full min-w-[560px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Brand</th><th>Slug</th><th>Products</th><th>Active</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @forelse ($this->rows as $b)
                <tr class="border-t border-slate-100" wire:key="brand-{{ $b->id }}">
                    <td class="px-4 py-2.5">
                        <span class="flex items-center gap-2">
                            @if ($b->logo)<img src="{{ $b->logo }}" alt="" class="size-8 rounded border object-contain">@endif
                            <b>{{ $b->name }}</b>
                        </span>
                    </td>
                    <td class="px-2 py-2.5 font-mono text-xs text-slate-500">{{ $b->slug }}</td>
                    <td class="px-2 py-2.5">{{ $b->products_count }}</td>
                    <td class="px-2 py-2.5">
                        @can('categories.manage')
                            <button wire:click="toggleActive({{ $b->id }})" class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $b->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $b->is_active ? 'On' : 'Off' }}</button>
                        @else
                            <span class="text-xs">{{ $b->is_active ? 'On' : 'Off' }}</span>
                        @endcan
                    </td>
                    <td class="px-4 py-2.5 text-right">
                        @can('categories.manage')
                            <button wire:click="edit({{ $b->id }})" class="btn-outline px-2.5 py-1.5 text-xs">Edit</button>
                            <button wire:click="delete({{ $b->id }})" wire:confirm="Delete {{ $b->name }}?" class="rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-bold text-brand">Del</button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400">No brands found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $this->rows->links() }}</div>

    @if ($open)
        <div class="fixed inset-0 z-40 flex justify-end bg-navy/50">
            <div class="w-full max-w-md overflow-y-auto bg-white p-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold">{{ $editingId ? 'Edit brand' : 'Add brand' }}</h3>
                    <button wire:click="closeForm" class="btn-outline px-2.5 py-1.5 text-xs">✕</button>
                </div>

                <div class="mt-3 space-y-3 text-sm">
                    <div>
                        <label class="label">Name *</label>
                        <input wire:model="name" class="input" placeholder="Xiaomi">
                        @error('name') <span class="text-[11px] text-brand">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label">Logo</label>
                        <div class="flex items-center gap-2">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" alt="" class="size-14 rounded-lg border object-contain">
                            @elseif ($logoUrl && ! $removeLogo)
                                <img src="{{ $logoUrl }}" alt="" class="size-14 rounded-lg border object-contain">
                                <button wire:click="$set('removeLogo', true)" class="text-xs text-brand">Remove</button>
                            @endif
                            <label class="grid h-14 flex-1 cursor-pointer place-items-center rounded-lg border-2 border-dashed border-slate-200 text-xs text-slate-400 hover:border-brand">📷 Upload logo<input wire:model="logo" type="file" accept="image/*" class="hidden"></label>
                        </div>
                        @error('logo') <span class="text-[11px] text-brand">{{ $message }}</span> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm"><input wire:model="is_active" type="checkbox" class="size-4 accent-[#c8102e]"> Active</label>

                    <button wire:click="save" wire:loading.attr="disabled" class="btn w-full py-2.5">Save brand</button>
                </div>
            </div>
        </div>
    @endif
</div>
