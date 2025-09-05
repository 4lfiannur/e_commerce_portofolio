{{-- resources/views/admin/product.blade.php --}}
@extends('layouts.layout-admin')

@section('title', 'Products')

@section('header_title', 'Products')
@section('header_subtitle', 'Manage your products')

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Products List</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle me-2"></i>Add Product
            </button>
        </div>

        <!-- Products Table -->
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <!-- Search and Filter -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <form action="{{ route('admin.products.index') }}" method="GET"
                            class="d-flex align-items-center gap-2">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search products..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if (request('search') || request('category'))
                                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Products Table -->
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td>
                                        @if ($product->image)
                                            <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                                class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <img src="https://via.placeholder.com/50" alt="No Image" class="img-thumbnail">
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $product->name }}</div>
                                        <div class="small text-muted">{{ Str::limit($product->description, 50) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $product->category->name }}</span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $product->stock }}</td>
                                    <td>
                                        <div class="d-flex gap-1 align-items-center">
                                            <span class="badge {{ $product->stock > 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                            </span>
                                            @if (!$product->is_active)
                                                <span class="badge bg-warning">Inactive</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#editProductModal{{ $product->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox display-4 text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No products found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }}
                            of {{ $products->total() }} products
                        </div>
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Category -->
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" name="category_id"
                                    required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Product Name -->
                            <div class="mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Price -->
                            <div class="mb-3">
                                <label class="form-label">Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control @error('price') is-invalid @enderror"
                                        name="price" value="{{ old('price') }}" required>
                                </div>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Stock -->
                            <div class="mb-3">
                                <label class="form-label">Stock *</label>
                                <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                    name="stock" value="{{ old('stock') }}" required>
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Image -->
                            <div class="mb-3">
                                <label class="form-label">Product Image *</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    name="image" accept="image/*" required>
                                <div id="imagePreview" class="mt-2"></div>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Active Status -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                        {{ old('is_active') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i>Save Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Product Modals -->
        @foreach ($products as $product)
            <div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.products.update', $product) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-pencil me-2"></i>Edit Product: {{ $product->name }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Category -->
                                <div class="mb-3">
                                    <label class="form-label">Category *</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror"
                                        name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Product Name -->
                                <div class="mb-3">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Price -->
                                <div class="mb-3">
                                    <label class="form-label">Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control @error('price') is-invalid @enderror"
                                            name="price" value="{{ old('price', $product->price) }}" required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Stock -->
                                <div class="mb-3">
                                    <label class="form-label">Stock *</label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                        name="stock" value="{{ old('stock', $product->stock) }}" required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Image -->
                                <div class="mb-3">
                                    <label class="form-label">Current Image</label>
                                    <div class="mb-2">
                                        @if ($product->image)
                                            <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                                class="img-thumbnail"
                                                style="width: 100px; height: 100px; object-fit: cover;">
                                        @else
                                            <img src="https://via.placeholder.com/100" alt="No Image"
                                                class="img-thumbnail">
                                        @endif
                                    </div>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                                        name="image" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Active Status -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="is_active"
                                            id="is_active{{ $product->id }}"
                                            {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active{{ $product->id }}">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2 me-1"></i>Update Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('styles')
    <style>
        .img-thumbnail {
            object-fit: cover;
        }

        .table> :not(caption)>*>* {
            vertical-align: middle;
        }

        .badge {
            font-weight: 500;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: none;
            border-color: #86b7fe;
        }

        .modal-body {
            max-height: calc(100vh - 210px);
            overflow-y: auto;
        }

        .btn-group {
            gap: 0.25rem;
        }

        .delete-form {
            display: inline;
        }

        #imagePreview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-top: 10px;
        }

        .price-input::-webkit-inner-spin-button,
        .price-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .price-input {
            -moz-appearance: textfield;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{{ session('error') }}",
                showConfirmButton: true
            });
        @endif

        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
        @if ($errors->any())
            var modalId = '{{ old('_modal_id', '#addProductModal') }}';
            var modal = new bootstrap.Modal(document.querySelector(modalId));
            modal.show();
        @endif

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const preview = this.parentElement.querySelector('#imagePreview') ||
                    document.createElement('div');
                preview.id = 'imagePreview';
                preview.innerHTML = '';

                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail';
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(this.files[0]);
                }

                if (!this.parentElement.querySelector('#imagePreview')) {
                    this.insertAdjacentElement('afterend', preview);
                }
            });
        });


        document.querySelectorAll('input[name="price"]').forEach(input => {

            input.classList.add('price-input');
            if (input.value) {
                input.value = new Intl.NumberFormat('id-ID').format(input.value);
            }

            input.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                this.value = new Intl.NumberFormat('id-ID').format(value);
            });
            input.form.addEventListener('submit', function(e) {
                input.value = input.value.replace(/\D/g, '');
            });
        });

        document.querySelectorAll('.price-input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush
