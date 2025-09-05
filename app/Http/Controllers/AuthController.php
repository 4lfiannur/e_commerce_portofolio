<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'max:255', 'email', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'phone' => ['required', 'string', 'max:20'],
                'address' => ['required', 'string']
            ], [
                'email.unique' => 'Email already registered',
                'password.min' => 'Password must be at least 8 characters',
                'password.confirmed' => 'Password confirmation does not match'
            ]);

            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'password' => Hash::make($validated['password']),
                'role' => 'user'
            ]);

            event(new Registered($user));
            Auth::login($user);

            DB::commit();

            return redirect()->route('home')
                ->with('success', 'Account created successfully ' . $user->name);
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors());
        } catch (QueryException $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Database error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Registration failed');
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Error during registration', $e->getMessage());
            return redirect()->back()
                ->with('error', 'Registration failed')
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ], [
                'email.required' => 'Email is required',
                'email.email' => 'Please enter a valid email address',
                'password.required' => 'Password is required',
            ]);

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                $user = Auth::user();
                $redirectTo = $user->isAdmin() ? route('admin.dashboard') : route('home');

                return redirect()->intended($redirectTo)
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }

            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput($request->except('password'));

        } catch (Exception $e) {
            Log::error('Error during login: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An unexpected error occurred. Please try again.')
                ->withInput($request->except('password'));
        }
    }
    public function logout(Request $request)
    {
        try {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')
                ->with('success', 'You have been logged out successfully');

        } catch (Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to logout. Please try again.');
        }
    }
}
