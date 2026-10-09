<?php

use App\Models\SiteLocale;
use App\Models\Translation;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $locale = '';

    public string $group = '';

    public string $search = '';

    /** @var array<int, string|null> */
    public array $editing = [];

    public ?int $savedId = null;

    public function mount(): void
    {
        $this->locale = SiteLocale::defaultCode() ?? 'bn';
        $this->loadEditing();
    }

    public function updatedLocale(): void
    {
        $this->resetPage();
        $this->loadEditing();
    }

    public function updatedGroup(): void
    {
        $this->resetPage();
        $this->loadEditing();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->loadEditing();
    }

    public function updatedPage(): void
    {
        $this->loadEditing();
    }

    #[Computed]
    public function groups()
    {
        return Translation::query()->select('group')->distinct()->orderBy('group')->pluck('group');
    }

    #[Computed]
    public function rows()
    {
        return Translation::query()
            ->when($this->locale, fn ($q) => $q->where('locale', $this->locale))
            ->when($this->group, fn ($q) => $q->where('group', $this->group))
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w->where('key', 'like', "%{$this->search}%")->orWhere('value', 'like', "%{$this->search}%")))
            ->orderBy('group')->orderBy('key')
            ->paginate(12);
    }

    public function loadEditing(): void
    {
        foreach ($this->rows as $row) {
            $this->editing[$row->id] ??= $row->value;
        }
    }

    public function saveRow(int $id): void
    {
        $row = Translation::findOrFail($id);
        $row->update(['value' => $this->editing[$id] ?? $row->value]);
        $this->savedId = $id;
    }
};
?>

<div class="card">
    <div class="flex flex-wrap items-end gap-3">
        <label class="block"><span class="label">Locale</span>
            <select wire:model.live="locale" class="input min-w-32">
                @foreach (\App\Models\SiteLocale::orderBy('sort_order')->get() as $l)
                    <option value="{{ $l->code }}">{{ $l->native_name }} ({{ $l->code }})</option>
                @endforeach
            </select>
        </label>
        <label class="block"><span class="label">Group</span>
            <select wire:model.live="group" class="input min-w-32">
                <option value="">All groups</option>
                @foreach ($this->groups as $g)
                    <option value="{{ $g }}">{{ $g }}</option>
                @endforeach
            </select>
        </label>
        <label class="block min-w-52 flex-1"><span class="label">Search key or text</span>
            <input wire:model.live.debounce.300ms="search" class="input" placeholder="e.g. cart">
        </label>
    </div>

    <div class="mt-4 space-y-3">
        @forelse ($this->rows as $row)
            <div class="rounded-lg border border-slate-100 p-3">
                <p class="font-mono text-[11px] text-slate-500">{{ $row->group }}.{{ $row->key }}</p>
                <div class="mt-1.5 flex flex-col gap-2 sm:flex-row">
                    <input wire:model="editing.{{ $row->id }}" class="input flex-1">
                    <button wire:click="saveRow({{ $row->id }})" class="btn px-4 py-2 text-xs data-loading:opacity-60">Save</button>
                </div>
                @if ($savedId === $row->id)
                    <p class="mt-1 text-[11px] font-semibold text-emerald-600">Saved · live immediately.</p>
                @endif
            </div>
        @empty
            <p class="py-8 text-center text-xs text-slate-400">No strings match. Add the language first, or clear the search.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $this->rows->links() }}</div>
</div>
