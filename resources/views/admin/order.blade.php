@extends('layouts.layout-admin')

@section('title', 'Orders')

@section('header_title', 'Orders')
@section('header_subtitle', 'Kelola pesanan pelanggan dengan tampilan ringkas')

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show small" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 flex-wrap">
                    <div>
                        <h5 class="mb-1">Daftar Pesanan</h5>
                        <p class="text-muted mb-0 small">Pantau dan perbarui status pesanan pelanggan dengan mudah.</p>
                    </div>
                    <form action="{{ route('admin.orders.index') }}" method="GET"
                        class="d-flex flex-wrap align-items-center gap-2">
                        <div class="input-group input-group-sm" style="min-width: 240px;">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                placeholder="Cari kode order / pelanggan" value="{{ request('search') }}">
                        </div>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua status</option>
                            @foreach (['paid' => 'Paid', 'processing' => 'Processing', 'shipped' => 'Shipped'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                Terapkan
                            </button>
                            @if (request('search') || request('status'))
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Pelanggan</th>
                            <th scope="col" class="text-end">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $order->order_code }}
                                    <div class="small text-muted">#{{ $order->id }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $order->user->name ?? 'Tidak diketahui' }}</div>
                                    <div class="small text-muted">{{ $order->user->email ?? '-' }}</div>
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    <div class="small text-muted">{{ $order->items->count() }} item</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->status_color }}">{{ ucfirst($order->status) }}</span>
                                    @if ($order->resi_code)
                                        <div class="small text-muted mt-1">Resi: {{ $order->resi_code }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $order->created_at->format('d M Y') }}</div>
                                    <div class="small text-muted">{{ $order->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    @if (!empty($order->getNextPossibleStatuses()))
                                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST"
                                            class="d-flex flex-column flex-lg-row gap-2 justify-content-end align-items-stretch">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" class="form-select form-select-sm status-select"
                                                data-resi="#resiInput{{ $order->id }}" required>
                                                <option value="" selected disabled>Pilih status</option>
                                                @foreach ($order->getNextPossibleStatuses() as $status)
                                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                                @endforeach
                                            </select>

                                            <input type="text" name="resi_code" id="resiInput{{ $order->id }}"
                                                class="form-control form-control-sm d-none" placeholder="Nomor resi">

                                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                        </form>
                                    @else
                                        <div class="text-muted text-end small">Tidak ada aksi</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox display-6 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0">Belum ada pesanan yang dapat ditampilkan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->count())
                <div class="card-footer bg-white border-0">
                    <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-2">
                        <p class="small text-muted mb-0">
                            Menampilkan {{ $orders->firstItem() }} sampai {{ $orders->lastItem() }} dari
                            {{ $orders->total() }} pesanan.
                        </p>
                        {{ $orders->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', () => {
                const target = document.querySelector(select.dataset.resi);

                if (!target) {
                    return;
                }

                if (select.value === 'shipped') {
                    target.classList.remove('d-none');
                    target.required = true;
                } else {
                    target.classList.add('d-none');
                    target.required = false;
                    target.value = '';
                }
            });
        });
    </script>
@endpush
