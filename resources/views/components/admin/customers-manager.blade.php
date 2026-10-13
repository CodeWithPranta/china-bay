<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function rows()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")))
            ->orderByDesc('id')
            ->paginate(12);
    }
};
?>

<div>
    <div class="card flex gap-2">
        <input wire:model.live.debounce.300ms="search" class="input sm:max-w-xs" placeholder="🔍 Search name / phone…">
    </div>

    <div class="card mt-4 overflow-x-auto p-0">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Name</th><th>Phone</th><th>Email</th><th>Loyalty</th><th>Verified</th><th>Joined</th></tr></thead>
            <tbody>
            @forelse ($this->rows as $u)
                <tr class="border-t border-slate-100" wire:key="customer-{{ $u->id }}">
                    <td class="px-4 py-2.5 font-bold">{{ $u->name }}</td>
                    <td class="px-2 py-2.5">{{ $u->phone ?? '—' }}</td>
                    <td class="px-2 py-2.5 text-xs">{{ $u->email }}</td>
                    <td class="px-2 py-2.5"><span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-700">{{ $u->loyalty_points ?? 0 }} pts</span></td>
                    <td class="px-2 py-2.5">{{ $u->email_verified_at ? '✓' : '—' }}</td>
                    <td class="px-2 py-2.5 text-xs text-slate-500">{{ $u->created_at?->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">No customers found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $this->rows->links() }}</div>
</div>
