<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class UserLandingController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();
        $query = Product::query()
            ->active()
            ->inStock();
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        if ($request->sort) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('price');
                    break;
                case 'latest':
                    $query->latest();
                    break;
                case 'default':
                    $query->oldest();
                    break;
            }
        } else {
            $query->latest();
        }
        $products = $query->paginate(8);
        $products->appends($request->all());

        return view('landing.landing-page', compact('products', 'categories'));
    }
}
