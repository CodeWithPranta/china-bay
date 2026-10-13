<?php

use App\Models\SiteLocale;
use App\Models\Translation;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $code = '';

    public string $name = '';

    public string $native = '';

    public string $direction = 'ltr';

    #[Computed]
    public function locales()
    {
        return SiteLocale::query()->orderBy('sort_order')->orderBy('code')->get();
    }

    public function toggleActive(string $code): void
    {
        $locale = SiteLocale::findOrFail($code);
        if ($locale->is_default && $locale->is_active) {
            return;
        }
        $locale->update(['is_active' => ! $locale->is_active]);
        SiteLocale::flushCache();
        unset($this->locales);
    }

    public function makeDefault(string $code): void
    {
        SiteLocale::query()->update(['is_default' => false]);
        SiteLocale::where('code', $code)->update(['is_default' => true, 'is_active' => true]);
        SiteLocale::flushCache();
        unset($this->locales);
    }

    public function add(): void
    {
        $this->validate([
            'code' => ['required', 'alpha_dash', 'max:10', 'unique:locales,code'],
            'name' => ['required', 'max:60'],
            'native' => ['required', 'max:60'],
            'direction' => ['required', 'in:ltr,rtl'],
        ]);

        $order = (int) (SiteLocale::max('sort_order') ?? 0) + 1;
        SiteLocale::create([
            'code' => $this->code, 'name' => $this->name, 'native_name' => $this->native,
            'direction' => $this->direction, 'is_active' => true, 'is_default' => false,
            'sort_order' => $order,
        ]);

        // Clone fallback strings so the new language works immediately.
        $fallback = config('app.fallback_locale', 'en');
        foreach (Translation::where('locale', $fallback)->get() as $row) {
            Translation::firstOrCreate(
                ['locale' => $this->code, 'group' => $row->group, 'key' => $row->key],
                ['value' => $row->value]
            );
        }

        $this->reset(['code', 'name', 'native', 'direction']);
        SiteLocale::flushCache();
        unset($this->locales);
    }

    public function remove(string $code): void
    {
        $locale = SiteLocale::findOrFail($code);
        if ($locale->is_default) {
            return;
        }
        Translation::where('locale', $code)->delete();
        $locale->delete();
        SiteLocale::flushCache();
        unset($this->locales);
    }
};
?>

<div class="space-y-4">
    <section class="card overflow-x-auto">
        <b class="text-sm">Languages · {{ $this->locales->count() }} total</b>
        <table class="mt-3 w-full min-w-[560px] text-xs">
            <thead>
                <tr class="text-start text-slate-500">
                    <th class="py-1.5 pe-3 text-start font-semibold">Code</th>
                    <th class="py-1.5 pe-3 text-start font-semibold">Name</th>
                    <th class="py-1.5 pe-3 text-start font-semibold">Direction</th>
                    <th class="py-1.5 pe-3 text-start font-semibold">Strings</th>
                    <th class="py-1.5 pe-3 text-start font-semibold">Active</th>
                    <th class="py-1.5 text-start font-semibold">Default / Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->locales as $locale)
                    <tr class="border-t border-slate-100">
                        <td class="py-2 pe-3 font-mono font-bold">{{ $locale->code }}</td>
                        <td class="py-2 pe-3">{{ $locale->native_name }} <span class="text-slate-400">({{ $locale->name }})</span></td>
                        <td class="py-2 pe-3">{{ $locale->direction }}</td>
                        <td class="py-2 pe-3">{{ \App\Models\Translation::where('locale', $locale->code)->count() }}</td>
                        <td class="py-2 pe-3">
                            <button wire:click="toggleActive('{{ $locale->code }}')" class="rounded-full px-2.5 py-1 font-semibold {{ $locale->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $locale->is_active ? 'On' : 'Off' }}
                            </button>
                        </td>
                        <td class="py-2">
                            @if ($locale->is_default)
                                <span class="rounded-full bg-brand/10 px-2.5 py-1 font-bold text-brand">Default</span>
                            @else
                                <span class="inline-flex gap-2">
                                    <button wire:click="makeDefault('{{ $locale->code }}')" class="font-semibold text-brand hover:underline">Make default</button>
                                    <button wire:click="remove('{{ $locale->code }}')" wire:confirm="Delete {{ $locale->code }} and its strings?" class="text-slate-400 hover:text-brand">Delete</button>
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="card">
        <b class="text-sm">Add a language · copies fallback strings automatically</b>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <label class="block"><span class="label">Code (e.g. hi)</span><input wire:model="code" class="input font-mono">@error('code') <span class="text-[11px] text-brand">{{ $message }}</span> @enderror</label>
            <label class="block"><span class="label">Name (English)</span><input wire:model="name" class="input">@error('name') <span class="text-[11px] text-brand">{{ $message }}</span> @enderror</label>
            <label class="block"><span class="label">Native name</span><input wire:model="native" class="input">@error('native') <span class="text-[11px] text-brand">{{ $message }}</span> @enderror</label>
            <label class="block"><span class="label">Direction</span>
                <select wire:model="direction" class="input"><option value="ltr">LTR</option><option value="rtl">RTL</option></select>
            </label>
            <span class="flex items-end"><button wire:click="add" class="btn w-full py-2.5 text-xs">Add language</button></span>
        </div>
    </section>
</div>
