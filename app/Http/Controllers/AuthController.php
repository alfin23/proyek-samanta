<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;


class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    // Proses data login
    public function login(LoginRequest $request): RedirectResponse
    {
        // Ambil data yang sudah lolos validasi dari form request
        $credentials = $request->validated();

        // Coba login dengan fitur remember me jika dicentang
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Best practice keamanan: regenerasi session ID untuk mencegah session fixation
            $request->session()->regenerate();

            // Alihkan ke halaman yang dituju sebelumnya, atau ke dashboard sebagai fallback
            return redirect()->intended(route('dashboard'));
        }

        // Jika gagal kembalikan pesan error generic untuk keamanan
        return back()->withErrors([
            'email' => 'Email atau password yang dimasukan salah.',
        ])->onlyInput('email');
    }

    // Proses logout akun
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Hancurkan session yang aktif saat ini
        $request->session()->invalidate();

        // Regenerasi token CSRF baru demi keamanan setelah logout
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Menampilkan halaman registrasi
    public function showRegister(): View
    {
        return view('auth.register');
    }

    // Proses pendaftara user baru
    public function register(RegisterRequest $request): RedirectResponse
    {
        // Ambil data yang sudah lolos validasi ketat dari form request
        $validated = $request->validated();

        // Best practice: Buat user baru dengan password yang di-hash aman
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Enkripsi password
        ]);

        // Pengalaman pengguna yang baik: Otomatis loginkan user setelah registrasi
        Auth::login($user);

        // Regenerasi session untuk keamanan
        $request->session()->regenerate();

        // Alihkan langsung ke dashboard
        return redirect()->route('dashboard');
    }
}
