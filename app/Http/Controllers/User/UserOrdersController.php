<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class UserOrdersController extends Controller
{
    /**
     * Display a listing of the authenticated user's orders.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with(['items.product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('order_code', 'like', "%{$search}%")
                        ->orWhere('resi_code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->string('status');

                if (in_array($status, Order::$validStatuses, true)) {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('landing.orders', [
            'orders' => $orders,
            'availableStatuses' => Order::$validStatuses,
        ]);
    }
}
