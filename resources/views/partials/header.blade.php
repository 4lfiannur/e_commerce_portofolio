@php
    $user = Auth::user();
    $displayName = $user?->name ?? 'Guest User';
@endphp

<header class="bg-white border-bottom sticky-top">
    <div class="d-flex align-items-center justify-content-between p-3 gap-3">
        <!-- Mobile Toggle Button -->
        <button id="sidebarToggle" class="btn d-lg-none">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- Header Title -->
        <h1 class="h5 mb-0 flex-grow-1">@yield('title', 'Dashboard')</h1>

        <!-- Profile Dropdown -->
        <div class="dropdown ms-auto">
            <button class="btn btn-light border d-flex align-items-center gap-2" type="button"
                id="profileDropdownToggle" aria-expanded="false" aria-haspopup="true">
                <span class="d-none d-md-inline fw-semibold text-nowrap">{{ $displayName }}</span>
                <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                    style="width: 36px; height: 36px;">
                    <i class="bi bi-person-fill"></i>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" id="profileDropdownMenu"
                aria-labelledby="profileDropdownToggle">
                <li>
                    <h6 class="dropdown-header">{{ $displayName }}</h6>
                </li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profil</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i
                                class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('profileDropdownToggle');
            const menu = document.getElementById('profileDropdownMenu');

            if (!toggle || !menu) {
                return;
            }

            const dropdown = toggle.closest('.dropdown');

            const closeMenu = () => {
                menu.classList.remove('show');
                menu.style.removeProperty('right');
                menu.style.removeProperty('left');
                toggle.setAttribute('aria-expanded', 'false');
            };

            const openMenu = () => {
                menu.classList.add('show');
                menu.style.right = '0';
                menu.style.left = 'auto';
                toggle.setAttribute('aria-expanded', 'true');
            };

            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                if (toggle.getAttribute('aria-expanded') === 'true') {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            toggle.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeMenu();
                    toggle.focus();
                    return;
                }

                if (event.key === 'ArrowDown' && toggle.getAttribute('aria-expanded') !== 'true') {
                    openMenu();
                    const firstItem = menu.querySelector('a, button');
                    if (firstItem) {
                        firstItem.focus();
                    }
                }
            });

            document.addEventListener('click', (event) => {
                if (!dropdown.contains(event.target)) {
                    closeMenu();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeMenu();
                }
            });
        });
    </script>
@endpush
