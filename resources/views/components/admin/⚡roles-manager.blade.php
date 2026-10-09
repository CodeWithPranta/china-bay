<?php

use Database\Seeders\RolesPermissionsSeeder;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

new class extends Component
{
    public string $newRole = '';

    #[Computed]
    public function roles()
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    #[Computed]
    public function grouped()
    {
        return RolesPermissionsSeeder::grouped();
    }

    public function togglePermission(int $roleId, string $perm): void
    {
        $role = Role::findOrFail($roleId);
        $role->hasPermissionTo($perm) ? $role->revokePermissionTo($perm) : $role->givePermissionTo($perm);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        unset($this->roles);
    }

    public function createRole(): void
    {
        $this->validate(['newRole' => ['required', 'alpha_dash', 'max:30', 'unique:roles,name']]);
        Role::create(['name' => $this->newRole, 'guard_name' => 'web']);
        $this->reset('newRole');
        unset($this->roles);
    }

    public function deleteRole(int $id): void
    {
        $role = Role::findOrFail($id);
        if (in_array($role->name, ['super-admin', 'admin'], true)) {
            return;
        }
        $role->delete();
        unset($this->roles);
    }
};
?>

<div class="space-y-4">
    <section class="card">
        <b class="text-sm">Create role</b>
        <div class="mt-2 flex max-w-md gap-2">
            <input wire:model="newRole" wire:keydown.enter="createRole" class="input" placeholder="e.g. warehouse">
            <button wire:click="createRole" class="btn shrink-0 px-4 py-2 text-xs">Add</button>
        </div>
        @error('newRole') <p class="mt-1 text-[11px] text-brand">{{ $message }}</p> @enderror
    </section>

    @foreach ($this->roles as $role)
        <section class="card" wire:key="role-{{ $role->id }}">
            <div class="flex flex-wrap items-center gap-2">
                <b class="text-sm">{{ $role->name }}</b>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">{{ $role->permissions->count() }} permissions · {{ $role->users_count ?? \App\Models\User::role($role->name)->count() }} users</span>
                @if (! in_array($role->name, ['super-admin', 'admin'], true))
                    <button wire:click="deleteRole({{ $role->id }})" wire:confirm="Delete role {{ $role->name }}?" class="ms-auto text-[11px] text-slate-400 hover:text-brand">Delete</button>
                @endif
            </div>
            @foreach ($this->grouped as $group => $perms)
                <p class="mb-1 mt-3 text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $group }}</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($perms as $perm)
                        @php $on = $role->permissions->contains('name', $perm); @endphp
                        <button wire:click="togglePermission({{ $role->id }}, '{{ $perm }}')"
                            class="rounded-full border px-2.5 py-1 text-[11px] font-medium transition {{ $on ? 'border-brand bg-brand text-white' : 'border-slate-200 bg-white text-slate-500 hover:border-brand hover:text-brand' }}">
                            {{ $perm }}
                        </button>
                    @endforeach
                </div>
            @endforeach
        </section>
    @endforeach
</div>
