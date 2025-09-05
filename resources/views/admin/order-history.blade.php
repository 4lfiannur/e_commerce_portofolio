@extends('layouts.layout-admin')

@section('title', 'Orders History')

@section('header_title', 'Orders History')
@section('header_subtitle', 'Manage your orders history')

@section('content')
    <div class="container py-4">
        <div class="card">
            <div class="card-header">
                <h4>Order History</h4>
            </div>
            <div class="card-body">
                <!-- Search Form -->
                <form action="{{ route('admin.history.index') }}" method="GET" class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search order..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered
                            </option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Search</button>
                        @if (request('search') || request('status'))
                            <a href="{{ route('admin.history.index') }}" class="btn btn-secondary">Clear</a>
                        @endif
                    </div>
                </form>

                <!-- Orders Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order Code</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->order_code }}</td>
                                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : 'danger' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#orderModal{{ $order->id }}">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No orders found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-end">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Order Detail Modals -->
    @foreach ($orders as $order)
        <div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1"
            aria-labelledby="orderModalLabel{{ $order->id }}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="orderModalLabel{{ $order->id }}">
                            <i class="bi bi-box-seam me-2"></i>Order #{{ $order->order_code }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-4">
                            <!-- Order Status Card -->
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1">Order Status</p>
                                                <h4 class="mb-0">
                                                    <span
                                                        class="badge bg-{{ $order->status === 'delivered' ? 'success' : 'danger' }} fs-6">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </h4>
                                            </div>
                                            <div class="text-end">
                                                <p class="text-muted mb-1">Order Date</p>
                                                <h6 class="mb-0">{{ $order->created_at->format('d M Y H:i') }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Info -->
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bi bi-credit-card me-2"></i>Payment Information
                                        </h6>
                                        <div class="mb-2">
                                            <label class="text-muted small">Payment Method</label>
                                            <p class="mb-1 fw-medium">
                                                {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
                                        </div>
                                        @if ($order->resi_code)
                                            <div class="mb-0">
                                                <label class="text-muted small">Resi Number</label>
                                                <p class="mb-0 fw-medium">{{ $order->resi_code }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Info -->
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bi bi-truck me-2"></i>Shipping Address
                                        </h6>
                                        <div class="mb-2">
                                            <label class="text-muted small">Address</label>
                                            <p class="mb-0 fw-medium">{{ $order->shipping_address }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bi bi-box me-2"></i>Order Items
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-borderless align-middle">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Product</th>
                                                        <th class="text-end">Price</th>
                                                        <th class="text-center">Quantity</th>
                                                        <th class="text-end">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($order->items as $item)
                                                        <tr>
                                                            <td>{{ $item->product->name }}</td>
                                                            <td class="text-end">Rp
                                                                {{ number_format($item->price, 0, ',', '.') }}</td>
                                                            <td class="text-center">{{ $item->quantity }}</td>
                                                            <td class="text-end">Rp
                                                                {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="border-top">
                                                    <tr>
                                                        <td colspan="3" class="text-end fw-bold">Total Amount</td>
                                                        <td class="text-end fw-bold">Rp
                                                            {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
