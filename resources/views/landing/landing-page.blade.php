@extends('layouts.layouts-landing')

@section('title', 'Home')

@section('content')
    <!-- Hero Section -->
    <div id="hero-section" class="relative overflow-hidden bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:pb-28 lg:w-full">
                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8">
                    <div class="text-center">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block">Welcome to Our</span>
                            <span class="block text-emerald-600">Online Store</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl">
                            Discover our amazing products at great prices. Quality products for every need.
                        </p>
                        <div class="mt-5 sm:mt-8 flex justify-center">
                            <div class="rounded-md shadow">
                                <a href="#featured-products"
                                    class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 md:py-4 md:text-lg md:px-10">
                                    Shop Now
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div id="featured-products" class="py-8 sm:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Featured Products</h2>

                <!-- Search and Filter -->
                <form action="{{ route('home') }}" method="GET" class="flex flex-col sm:flex-row gap-4 mb-6">
                    <!-- Search Input -->
                    <div class="flex-1 min-w-0">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search products..." autocomplete="off"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                    </div>

                    <!-- Category Filter -->
                    <div class="w-full sm:w-auto">
                        <select name="category"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort Filter -->
                    <div class="w-full sm:w-auto">
                        <select name="sort"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>
                                Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                                Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Apply Filter Button -->
                    <div class="w-full sm:w-auto">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </form>

                <!-- Products Grid -->
                <div class="mt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($products as $product)
                            <div class="product-card bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-shadow"
                                data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                data-price="{{ $product->price }}" data-image="{{ $product->getPrimaryImage() }}"
                                data-category="{{ $product->category->name }}">
                                <div class="aspect-w-1 aspect-h-1">
                                    <img src="{{ $product->getPrimaryImage() }}" alt="{{ $product->name }}"
                                        class="w-full h-48 sm:h-72 object-cover">
                                </div>
                                <div class="p-3 sm:p-4">
                                    <h3 class="product-name text-base sm:text-lg font-medium text-gray-900">
                                        {{ $product->name }}</h3>
                                    <p class="product-description mt-1 text-xs sm:text-sm text-gray-500">
                                        {{ Str::limit($product->description, 100) }}</p>
                                    <div class="mt-4 flex items-center justify-between">
                                        <p class="text-base sm:text-lg font-semibold text-gray-600">
                                            ${{ $product->formatted_price }}</p>
                                        <button
                                            class="add-to-cart px-3 py-1 sm:px-4 sm:py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8 sm:py-12">
                                <h3 class="text-lg font-semibold text-gray-900">No products found.</h3>
                                <p class="mt-2 text-sm text-gray-500">Try adjusting your search or filter to find
                                    what you're looking for.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if ($products->hasPages())
                        <div class="mt-6">
                            <nav class="flex items-center justify-center flex-wrap gap-2">
                                {{ $products->links() }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Newsletter Section -->
            <div id="newsletter-section" class="bg-emerald-50 py-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Subscribe to Our Newsletter</h2>
                        <p class="text-gray-600 mb-6">Get the latest updates about our products and offers.</p>
                        <form id="newsletter-form" class="max-w-md mx-auto">
                            @csrf
                            <div class="flex gap-4">
                                <input type="email" name="email" placeholder="Enter your email"
                                    class="flex-1 px-4 py-2 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                                <button type="submit"
                                    class="px-6 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors">
                                    Subscribe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
