@extends('layouts.layout-admin')

@section('title', 'Users')

@section('header_title', 'Users')
@section('header_subtitle', 'Manage your users')

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Users List</h1>
        </div>

        <!-- Users Table -->
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <!-- Search and Filter -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex align-items-center gap-2">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search users..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            @if (request('search'))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Users Table -->
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>
                                        <span class="badge {{ $user->isAdmin() ? 'bg-danger' : 'bg-info' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if (!$user->isAdmin() && $user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox display-4 text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No users found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }}
                            of {{ $users->total() }} users
                        </div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

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

        // Konfirmasi delete
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
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
    </script>
@endpush
