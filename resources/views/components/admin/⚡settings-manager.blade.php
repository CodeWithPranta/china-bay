<?php

use App\Models\Setting;
use Database\Seeders\SettingsSeeder;
use Livewire\Component;

new class extends Component
{
    /** @var array<string, string|null> slug => value (dots become __) */
    public array $values = [];

    public ?string $savedAt = null;

    public static function slug(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    public function mount(): void
    {
        $stored = Setting::pluck('value', 'key')->all();
        foreach (SettingsSeeder::defaults() as $pairs) {
            foreach ($pairs as $key => $def) {
                $this->values[static::slug($key)] = $stored[$key] ?? $def;
            }
        }
    }

    public function save(): void
    {
        $rules = [];
        foreach (['theme__brand', 'theme__brand_dark', 'theme__ink', 'theme__navy'] as $slug) {
            $rules["values.{$slug}"] = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];
        }
        $this->validate($rules);

        foreach (SettingsSeeder::defaults() as $group => $pairs) {
            foreach ($pairs as $key => $def) {
                Setting::set($key, (string) ($this->values[static::slug($key)] ?? $def), $group);
            }
        }

        $this->savedAt = now()->format('H:i:s');
    }
};
?>

<div class="space-y-4">
    @if ($savedAt)
        <p class="rounded-lg bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">Saved at {{ $savedAt }} · live on the storefront immediately.</p>
    @endif

    @foreach (\Database\Seeders\SettingsSeeder::defaults() as $group => $pairs)
        <section class="card">
            <b class="text-sm capitalize">{{ $group }}</b>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($pairs as $key => $def)
                    @php $slug = str_replace('.', '__', $key); @endphp
                    <label class="block">
                        <span class="label">{{ $key }}</span>
                        @if (str_starts_with($key, 'theme.'))
                            <span class="flex items-center gap-2">
                                <input type="color" wire:model="values.{{ $slug }}" class="h-10 w-12 cursor-pointer rounded border border-slate-200 bg-white p-1">
                                <input type="text" wire:model="values.{{ $slug }}" class="input font-mono" placeholder="#c8102e">
                            </span>
                            @error("values.{$slug}") <span class="text-[11px] text-brand">{{ $message }}</span> @enderror
                        @else
                            <input type="text" wire:model="values.{{ $slug }}" class="input">
                        @endif
                    </label>
                @endforeach
            </div>
        </section>
    @endforeach

    <button wire:click="save" wire:loading.attr="disabled" class="btn px-6 py-2.5 text-sm data-loading:opacity-60">Save all settings</button>
</div>
