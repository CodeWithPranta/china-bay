<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function roles()
    {
        return Role::orderBy('name')->pluck('name');
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")))
            ->with('roles')
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function toggleRole(int $userId, string $role): void
    {
        $user = User::findOrFail($userId);
        $user->hasRole($role) ? $user->removeRole($role) : $user->assignRole($role);
        unset($this->users);
    }
};
?>

<div class="card">
    <div class="flex flex-wrap items-center gap-3">
        <b class="text-sm">Team & customers</b>
        <input wire:model.live.debounce.300ms="search" class="input ms-auto max-w-64" placeholder="Search name, email, phone…">
    </div>

    <div class="mt-3 space-y-3">
        @forelse ($this->users as $user)
            <div class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-100 p-3" wire:key="user-{{ $user->id }}">
                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand/10 font-bold text-brand">{{ strtoupper(mb_substr($user->name ?? '?', 0, 1)) }}</span>
                <span class="min-w-0">
                    <b class="block truncate text-[13px]">{{ $user->name }}</b>
                    <span class="block truncate text-[11px] text-slate-500">{{ $user->email }} {{ $user->phone ? '· ' . $user->phone : '' }}</span>
                    <span class="text-[10px] {{ $user->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $user->email_verified_at ? 'Email verified' : 'Email unverified' }} · {{ $user->loyalty_points ?? 0 }} pts</span>
                </span>
                <span class="ms-auto flex max-w-full flex-wrap gap-1.5">
                    @foreach ($this->roles as $role)
                        @php $on = $user->roles->contains('name', $role); @endphp
                        <button wire:click="toggleRole({{ $user->id }}, '{{ $role }}')"
                            class="rounded-full border px-2.5 py-1 text-[11px] font-medium transition {{ $on ? 'border-brand bg-brand text-white' : 'border-slate-200 text-slate-500 hover:border-brand hover:text-brand' }}">{{ $role }}</button>
                    @endforeach
                </span>
            </div>
        @empty
            <p class="py-8 text-center text-xs text-slate-400">No users found.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $this->users->links() }}</div>
</div>
