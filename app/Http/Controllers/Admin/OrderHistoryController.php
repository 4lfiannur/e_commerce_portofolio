<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['items.product', 'user']) // Include user relation
            ->whereIn('status', ['delivered', 'cancelled'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_code', 'like', "%{$search}%")
                        ->orWhere('total_amount', 'like', "%{$search}%")
                        ->orWhere('shipping_address', 'like', "%{$search}%")
                        ->orWhere('resi_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, function ($query, $status) {
                if (in_array($status, ['delivered', 'cancelled'])) {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.order-history', compact('orders'));
    }
}

