<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class CustomerController extends Controller
{
    /**
     * Arahkan user ke halaman login Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Tangani callback dari Google.
     */
    public function callback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();

            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Login Google hanya untuk akun customer (role 2);
                // akun admin/superadmin harus login lewat halaman backend.
                if ($user->role != 2) {
                    return redirect('/')->with('msgError', 'Email ini terdaftar sebagai akun admin, silakan login melalui halaman backend.');
                }

                Auth::login($user);
            } else {
                $user = User::create([
                    'nama' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'role' => '2',
                    'status' => 1,
                    'hp' => '',
                    'password' => Hash::make(Str::random(32)),
                ]);

                Customer::create([
                    'user_id' => $user->id,
                    'google_id' => $socialUser->getId(),
                    'google_token' => $socialUser->token,
                ]);

                Auth::login($user);
            }

            return redirect()->route('beranda');
        } catch (\Exception $e) {
            return redirect('/')->with('msgError', 'Login gagal: ' . $e->getMessage());
        }
    }

    /**
     * Logout customer.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
