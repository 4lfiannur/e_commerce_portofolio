<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = User::query()
                ->withCount('orders')
                ->when($request->filled('search'), function ($q) use ($request) {
                    $search = $request->search;
                    $q->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                });

            $users = $query->latest()->paginate(10)->withQueryString();

            return view('admin.user', compact('users'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load users: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();
            if ($user->isAdmin()) {
                throw new Exception('Cannot delete admin user.');
            }
            if ($user->id === auth()->id()) {
                throw new Exception('Cannot delete your own account.');
            }
            foreach ($user->orders as $order) {
                $order->items()->delete();
            }
            $user->orders()->delete();
            $user->delete();
            DB::commit();
            return redirect()->route('admin.users.index')
                ->with('success', 'User and all related data deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}