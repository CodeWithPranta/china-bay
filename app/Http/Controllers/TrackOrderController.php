<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackOrderController extends Controller
{
    /**
     * Public order lookup by code + phone, plus history for members.
     */
    public function show(Request $request): View
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'regex:/^01[0-9]{9}$/'],
        ]);

        $tracked = null;

        if (! empty($data['code']) && ! empty($data['phone'])) {
            $tracked = Order::with('items')
                ->where('code', $data['code'])
                ->where('phone', $data['phone'])
                ->first();
        }

        $history = $request->user()
            ? Order::where('user_id', $request->user()->id)->orderByDesc('id')->limit(5)->get()
            : collect();

        return view('pages.track-order', [
            'tracked' => $tracked,
            'lookedUp' => ! empty($data['code']),
            'history' => $history,
            'code' => $data['code'] ?? '',
            'phone' => $data['phone'] ?? '',
        ]);
    }
}
