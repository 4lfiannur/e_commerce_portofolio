<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::query()
                ->with('category')
                ->when($request->filled('search'), function ($q) use ($request) {
                    $search = $request->search;
                    $q->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($request->filled('category'), function ($q) use ($request) {
                    $q->where('category_id', $request->category);
                });

            $products = $query->latest()->paginate(10)->withQueryString();
            $categories = Category::all();

            return view('admin.product', compact('products', 'categories'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load products: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            $image = [];
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $content = file_get_contents($file->getRealPath());
                $extension = $file->getClientOriginalExtension();
                $base64 = base64_encode($content);
                $image[] = "data:image/{$extension};base64,{$base64}";
            }

            Product::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'price' => $this->cleanPrice($validated['price']),
                'stock' => $validated['stock'],
                'image' => $image,
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();
            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create product: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            $data = [
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'price' => $this->cleanPrice($validated['price']),
                'stock' => $validated['stock'],
                'is_active' => $request->has('is_active'),
            ];

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $content = file_get_contents($file->getRealPath());
                $extension = $file->getClientOriginalExtension();
                $base64 = base64_encode($content);
                $data['image'] = array_merge($product->image ?? [], ["data:image/{$extension};base64,{$base64}"]);
            }

            $product->update($data);

            DB::commit();
            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update product: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            if ($product->orderItems()->exists()) {
                throw new Exception('Cannot delete product with associated orders.');
            }

            $product->delete();

            DB::commit();
            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    private function cleanPrice($price)
    {
        return (float) preg_replace('/[^0-9.]/', '', $price);
    }
}