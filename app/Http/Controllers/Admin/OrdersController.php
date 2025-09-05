<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['user', 'items.product'])
            ->whereIn('status', ['paid', 'processing', 'shipped'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhere('shipping_address', 'like', "%{$search}%")
                        ->orWhere('resi_code', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                if (in_array($status, ['paid', 'processing', 'shipped'])) {
                    return $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.order', compact('orders'));
    }
    public function updateStatus(Request $request, Order $order)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:paid,processing,shipped,cancelled',
                'resi_code' => 'required_if:status,shipped|nullable|string|max:255',
            ]);

            if ($validated['status'] === 'shipped') {
                if (empty($validated['resi_code'])) {
                    throw new Exception('Resi number is required for shipped status.');
                }
                $order->resi_code = $validated['resi_code'];
                $order->save();
            }
            $order->updateStatus($validated['status']);
            return redirect()->back()->with('success', 'Order status updated');
        } catch (Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }
}
