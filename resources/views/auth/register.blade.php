@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gradient-custom">
        <div class="w-full max-w-md mx-auto p-6">
            <!-- Alert Messages -->
            @if (session('success'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-12 text-center">
                <div
                    class="w-16 h-16 mx-auto mb-6 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-8-6h16"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-primary">Create Account</h2>
                <p class="text-secondary mt-2">Join us to start shopping</p>
            </div>

            <div
                class="bg-card p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-sm border border-gray-100">
                <form class="space-y-6" action="{{ route('auth.register') }}" method="POST">
                    @csrf

                    <div>
                        <input id="name" name="name" type="text" placeholder="Full Name"
                            class="input-field @error('name') border-red-500 @enderror" value="{{ old('name') }}"
                            required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <input id="email" name="email" type="email" placeholder="Email Address"
                            class="input-field @error('email') border-red-500 @enderror" value="{{ old('email') }}"
                            required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <input id="phone" name="phone" type="tel" placeholder="Phone Number"
                            class="input-field @error('phone') border-red-500 @enderror" value="{{ old('phone') }}"
                            required>
                        @error('phone')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <textarea id="address" name="address" placeholder="Address"
                            class="input-field @error('address') border-red-500 @enderror" rows="3" required>{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative">
                        <input id="password" name="password" type="password" placeholder="Password"
                            class="input-field pr-10 @error('password') border-red-500 @enderror" required>
                        <button type="button" onclick="togglePassword('password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="bi bi-eye-slash text-gray-400" id="password-icon"></i>
                        </button>
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            placeholder="Confirm Password" class="input-field pr-10" required>
                        <button type="button" onclick="togglePassword('password_confirmation')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <i class="bi bi-eye-slash text-gray-400" id="password_confirmation-icon"></i>
                        </button>
                    </div>

                    <button type="submit" class="w-full py-4 px-6 rounded-xl btn-primary font-medium">
                        Create Account
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center text-secondary">
                Already have an account?
                <a href="{{ route('login') }}" class="text-accent font-medium hover:text-emerald-700">Sign in</a>
            </p>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }
    </script>
@endsection
