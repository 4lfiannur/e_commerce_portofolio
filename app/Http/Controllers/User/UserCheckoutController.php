<?php

namespace App\Http\Controllers\User;

use Exception;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Attribute;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserCheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        try {
            $serverKey = config('midtrans.server_key');
            if (empty($serverKey)) {
                throw new Exception('Midtrans server key is not set.');
            }
            Config::$serverKey = $serverKey;
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = config('midtrans.sanitize');
            Config::$is3ds = config('midtrans.enable_3ds');

            Log::info('Midtrans Configuration Loaded', [
                'isProduction' => Config::$isProduction,
                'isSanitized' => Config::$isSanitized,
                'is3ds' => Config::$is3ds,
            ]);
        } catch (Exception $e) {
            Log::error('Midtrans Configuration Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function process(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'shipping_address' => 'required|string|max:255',
                'notes' => 'nullable|string|max:500',
                'cart' => 'required|array',
            ]);

            if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User must be logged in'
            ], 401);
        }
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => auth()->id(),
                'shipping_address' => $request->shipping_address,
                'total_amount' => 0,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            $totalAmount = 0;
            $items = [];

            foreach ($request->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $totalAmount += $item['price'] * $item['quantity'];

                $items[] = [
                    'id' => $item['id'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'name' => $item['name'],
                ];
            }

            $shippingCost = 20000;
            $totalAmount += $shippingCost;

            $order->update(['total_amount' => $totalAmount]);

            $params = [
                'transaction_details' => [
                    'order_id' => $order->id,
                    'gross_amount' => $totalAmount,
                ],
                'item_details' => array_merge($items, [[
                    'id' => 'shipping',
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => 'Shipping Cost',
                ]]),
                'customer_details' => [
                    'first_name' => $request->name,
                    'email' => auth()->user()->email,
                    'phone' => $request->phone,
                    'billing_address' => [
                        'address' => $request->shipping_address,
                    ],
                    'shipping_address' => [
                        'address' => $request->shipping_address,
                    ]
                ]
            ];

            $snapToken = Snap::getSnapToken($params);

            if (empty($snapToken)) {
                throw new Exception('Failed to generate Snap token.');
            }

            $order->update(['snap_token' => $snapToken]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'snap_token' => $snapToken,
                'order_id' => $order->id,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Checkout process error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], status: 422);
        }
    }

    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'status' => 'required|string|in:pending,paid,failed,expired,cancelled,shipped,delivered',
            ]);

            $order = Order::find($request->order_id);
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
            }

            $order->update(['status' => $request->status]);

            return response()->json(['status' => 'success', 'message' => 'Order status updated.']);
        } catch (Exception $e) {
            Log::error('Update order status error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating order status: ' . $e->getMessage()
            ], status: 422);
        }
    }
}
