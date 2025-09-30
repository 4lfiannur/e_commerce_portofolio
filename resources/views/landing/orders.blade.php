@extends('layouts.layouts-landing')

@section('title', 'My Orders')

@section('content')
    <div class="py-10 sm:py-14 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Orders</h1>
                <p class="mt-2 text-sm text-gray-500">Track your recent purchases and keep an eye on their delivery
                    progress.</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                        <div class="md:col-span-2">
                            <label for="search"
                                class="block text-xs font-semibold tracking-wide text-gray-500 uppercase mb-2">Search</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Search by order code or tracking number"
                                    class="w-full rounded-xl border border-gray-200 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-emerald-200 focus:border-emerald-500 transition" />
                            </div>
                        </div>

                        <div>
                            <label for="status"
                                class="block text-xs font-semibold tracking-wide text-gray-500 uppercase mb-2">Status</label>
                            <select name="status" id="status"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-200 focus:border-emerald-500 transition">
                                <option value="">All statuses</option>
                                @foreach ($availableStatuses as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-3 flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition">
                                Apply Filters
                            </button>
                            @if (request('search') || request('status'))
                                <a href="{{ route('orders.index') }}"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:border-emerald-200 transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                @php
                    $statusStyles = [
                        'pending' => 'bg-amber-100 text-amber-800',
                        'paid' => 'bg-blue-100 text-blue-800',
                        'processing' => 'bg-indigo-100 text-indigo-800',
                        'shipped' => 'bg-cyan-100 text-cyan-800',
                        'delivered' => 'bg-emerald-100 text-emerald-800',
                        'cancelled' => 'bg-rose-100 text-rose-800',
                    ];
                @endphp

                @if ($orders->count())
                    <div class="divide-y divide-gray-100">
                        @foreach ($orders as $order)
                            <div class="p-4 sm:p-6">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Order Code</p>
                                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $order->order_code }}</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Placed on {{ $order->created_at->format('d M Y, H:i') }}
                                        </p>
                                        @if ($order->shipping_address)
                                            <p class="mt-3 text-sm text-gray-500">
                                                <span class="font-medium text-gray-700">Shipping to:</span>
                                                {{ $order->shipping_address }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 md:text-right">
                                        @if ($order->resi_code)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                                                Resi: {{ $order->resi_code }}
                                            </span>
                                        @endif
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusStyles[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-6 grid gap-6 lg:grid-cols-3">
                                    <div class="lg:col-span-2 space-y-4">
                                        @forelse ($order->items as $item)
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">
                                                        {{ $item->product->name ?? 'Product removed' }}</p>
                                                    <p class="text-xs text-gray-500">Qty: {{ $item->quantity }} ×
                                                        Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                                </div>
                                                <p class="text-sm font-semibold text-gray-900">
                                                    Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</p>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500">Order items are unavailable.</p>
                                        @endforelse
                                    </div>

                                    <div class="bg-emerald-50 rounded-2xl p-4 sm:p-5">
                                        <p class="text-sm font-semibold text-emerald-900">Order Summary</p>
                                        <dl class="mt-3 space-y-2 text-sm text-emerald-800">
                                            <div class="flex justify-between">
                                                <dt>Items</dt>
                                                <dd>{{ $order->items->sum('quantity') }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt>Total Amount</dt>
                                                <dd class="font-semibold text-emerald-900">
                                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt>Payment Status</dt>
                                                <dd>{{ ucfirst($order->status) }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($orders->hasPages())
                        <div class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-8 sm:p-12 text-center">
                        <div class="mx-auto w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h2 class="mt-6 text-lg font-semibold text-gray-900">You have no orders yet</h2>
                        <p class="mt-2 text-sm text-gray-500">Start exploring our catalog and treat yourself to something
                            nice.</p>
                        <div class="mt-6">
                            <a href="/"
                                class="inline-flex items-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition">
                                Browse Products
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
