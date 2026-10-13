<?php

use App\Models\Order;
use App\Models\Product;
use App\Support\OrderStatus;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public ?int $expanded = null;

    public string $toast = '';

    public function mount(?string $preset = null): void
    {
        if ($preset) {
            $this->search = $preset;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function rows()
    {
        return Order::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('code', 'like', "%{$this->search}%")
                ->orWhere('name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->with(['items', 'user'])
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function toggle(int $id): void
    {
        $this->expanded = $this->expanded === $id ? null : $id;
    }

    public function advance(int $id, string $to): void
    {
        abort_unless(auth()->user()?->can('orders.manage'), 403);

        $order = Order::findOrFail($id);

        abort_unless(in_array($to, OrderStatus::next($order->status), true), 422);

        $order->update(['status' => $to]);
        unset($this->rows);
        $this->toast = "{$order->code} → ".OrderStatus::label($to).' ✓';
    }

    public function cancel(int $id): void
    {
        abort_unless(auth()->user()?->can('orders.manage'), 403);

        $order = Order::with('items')->findOrFail($id);

        abort_unless($order->isCancellable(), 422);

        DB::transaction(function () use ($order) {
            $order->update(['status' => OrderStatus::CANCELLED]);

            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)->increment('stock_qty', $item->qty);
            }
        });

        unset($this->rows);
        $this->toast = "{$order->code} cancelled; stock restored ✓";
    }
};
?>

<div>
    <div class="card flex flex-col gap-2 sm:flex-row">
        <input wire:model.live.debounce.300ms="search" class="input sm:max-w-xs" placeholder="🔍 Search code / name / phone…">
        <select wire:model.live="statusFilter" class="input sm:max-w-44">
            <option value="">All statuses</option>
            @foreach (array_merge(\App\Support\OrderStatus::steps(), [['key' => 'cancelled', 'label' => 'Cancelled']]) as $s)
                <option value="{{ $s['key'] }}">{{ $s['label'] }}</option>
            @endforeach
        </select>
    </div>

    @if ($toast)
        <p class="mt-3 rounded-lg bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">{{ $toast }}</p>
    @endif

    <div class="card mt-4 overflow-x-auto p-0">
        <table class="w-full min-w-[820px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Order</th><th>Customer</th><th>District</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @forelse ($this->rows as $o)
                <tr class="border-t border-slate-100 hover:bg-slate-50/60" wire:key="order-{{ $o->id }}">
                    <td class="px-4 py-3 font-mono font-bold">{{ $o->code }}<span class="block font-sans text-[11px] font-normal text-slate-500">{{ $o->created_at->format('d M Y, h:i A') }}</span></td>
                    <td class="px-2 py-3">{{ $o->name }}<span class="block text-[11px] text-slate-500">{{ $o->phone }}</span></td>
                    <td class="px-2 py-3">{{ $o->district }}</td>
                    <td class="px-2 py-3">{{ $o->items->sum('qty') }}</td>
                    <td class="px-2 py-3 font-bold">৳{{ number_format((float) $o->total) }}</td>
                    <td class="px-2 py-3"><span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold">{{ ucfirst($o->payment_method) }}</span></td>
                    <td class="px-2 py-3"><span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ \App\Support\OrderStatus::badge($o->status) }}">{{ $o->statusLabel() }}</span></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-1">
                            <button wire:click="toggle({{ $o->id }})" class="btn-outline px-2.5 py-1.5 text-xs">{{ $expanded === $o->id ? 'Hide' : 'View' }}</button>
                            @can('orders.manage')
                                @foreach (\App\Support\OrderStatus::next($o->status) as $next)
                                    @if ($next === \App\Support\OrderStatus::CANCELLED)
                                        <button wire:click="cancel({{ $o->id }})" wire:confirm="Cancel {{ $o->code }} and restore stock?" class="rounded-md bg-brand px-2.5 py-1.5 text-xs font-bold text-white">✕</button>
                                    @else
                                        <button wire:click="advance({{ $o->id }}, '{{ $next }}')" class="rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white" title="Mark {{ \App\Support\OrderStatus::label($next) }}">✓</button>
                                    @endif
                                @endforeach
                            @endcan
                        </div>
                    </td>
                </tr>
                @if ($expanded === $o->id)
                    <tr class="bg-slate-50/60" wire:key="order-detail-{{ $o->id }}">
                        <td colspan="8" class="px-6 py-3 text-xs">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div><b>Items</b>@foreach ($o->items as $item)<p class="mt-1">{{ $item->name }} × {{ $item->qty }} — ৳{{ number_format((float) $item->unit_price * $item->qty) }}</p>@endforeach</div>
                                <div><b>Address</b><p class="mt-1 text-slate-600">{{ $o->address }}<br>{{ $o->district }}{{ $o->thana ? ' · '.$o->thana : '' }}</p><p class="mt-1">Subtotal ৳{{ number_format((float) $o->subtotal) }} · Discount ৳{{ number_format((float) $o->discount) }} · Delivery ৳{{ number_format((float) $o->delivery_charge) }}</p></div>
                                <div><b>Payment</b><p class="mt-1 text-slate-600">{{ ucfirst($o->payment_method) }} · {{ ucfirst($o->payment_status) }}{{ $o->trx_id ? ' · '.$o->trx_id : '' }}</p>@if ($o->note)<p class="mt-1 text-slate-500">“{{ $o->note }}”</p>@endif</div>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-xs text-slate-400">No orders found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $this->rows->links() }}</div>
</div>
