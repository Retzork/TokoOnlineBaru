<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class CustomerController extends Controller
{
     public function index()
    {
        $customer = Customer::orderBy('id', 'desc')->get();
        return view('backend.v_customer.index', [
            'judul' => 'Customer',
            'sub' => 'Halaman Customer',
            'index' => $customer
        ]);
    }
    /**
     * Arahkan user ke halaman login Google.
     */
    public function redirect()
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $provider */
        $provider = Socialite::driver('google');
        return $provider->with(['prompt' => 'select_account'])->redirect();
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

            return redirect('/');
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

    /**
     * Halaman akun customer (frontend).
     */
    public function akun($id)
    {
        $loggedInCustomerId = Auth::user()->id;

        // Cek apakah ID yang diberikan sama dengan ID customer yang sedang login
        if ($id != $loggedInCustomerId) {
            return redirect()->route('customer.akun', ['id' => $loggedInCustomerId])
                ->with('msgError', 'Anda tidak berhak mengakses akun ini.');
        }

        $customer = Customer::where('user_id', $id)->firstOrFail();

        return view('v_customer.edit', [
            'judul' => 'Customer',
            'subJudul' => 'Akun Customer',
            'edit' => $customer
        ]);
    }

    /**
     * Simpan perubahan akun customer (frontend).
     */
    public function updateAkun(Request $request, $id)
    {
        $customer = Customer::where('user_id', $id)->firstOrFail();

        $rules = [
            'nama' => 'required|max:255',
            'hp' => 'required|min:10|max:13',
            'foto' => 'image|mimes:jpeg,jpg,png,gif|file|max:1024',
        ];
        $messages = [
            'foto.image' => 'Format gambar gunakan file dengan ekstensi jpeg, jpg, png, atau gif.',
            'foto.max' => 'Ukuran file gambar Maksimal adalah 1024 KB.'
        ];

        if ($request->email != $customer->user->email) {
            $rules['email'] = 'required|max:255|email|unique:user';
        }
        if ($request->alamat != $customer->alamat) {
            $rules['alamat'] = 'required';
        }
        if ($request->pos != $customer->pos) {
            $rules['pos'] = 'required';
        }

        $validatedData = $request->validate($rules, $messages);

        // menggunakan ImageHelper
        if ($request->file('foto')) {
            // hapus gambar lama
            if ($customer->user->foto) {
                $oldImagePath = public_path('storage/img-customer/') . $customer->user->foto;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $originalFileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
            $directory = 'storage/img-customer/';
            // Simpan gambar dengan ukuran yang ditentukan
            ImageHelper::uploadAndResize($file, $directory, $originalFileName, 385, 400);
            // Simpan nama file asli di database
            $validatedData['foto'] = $originalFileName;
        }

        $customer->user->update($validatedData);
        $customer->update([
            'alamat' => $request->input('alamat'),
            'pos' => $request->input('pos'),
        ]);

        return redirect()->route('customer.akun', $id)->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Form pendaftaran customer.
     */
    public function registerForm()
    {
        return view('v_customer.register', [
            'judul' => 'Daftar Akun',
        ]);
    }

    /**
     * Proses pendaftaran customer baru secara manual.
     */
    public function registerProses(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|max:255',
            'email' => 'required|email|unique:user',
            'password' => 'required|min:6|confirmed',
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        // 1. Buat User
        $user = User::create([
            'nama' => $validatedData['nama'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'hp' => '', // Menambahkan nilai kosong untuk kolom hp
            'role' => '2', // Customer
            'status' => 1, // Aktif
        ]);

        // 2. Buat Customer terkait
        Customer::create([
            'user_id' => $user->id,
            'google_id' => null,
            'google_token' => null,
        ]);

        // 3. Login otomatis setelah daftar
        Auth::login($user);

        return redirect()->route('beranda')->with('success', 'Pendaftaran berhasil! Selamat datang, ' . $user->nama);
    }

    /**
     * Form login customer dengan email & password.
     * Dipakai sebagai alternatif selama OAuth Google belum dikonfigurasi.
     */
    public function loginForm()
    {
        return view('v_customer.login');
    }

    /**
     * Proses login customer dengan email & password.
     */
    public function loginProses(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->with('msgError', 'Email atau password salah')->withInput();
        }

    $user = Auth::user();

        if ($user->role != 2) {
            Auth::logout();
            return back()->with('msgError', 'Akun ini bukan akun customer');
        }

        // Pastikan data customer tersedia
        Customer::firstOrCreate(
            ['user_id' => $user->id],
            ['google_id' => null, 'google_token' => null]
        );

        $request->session()->regenerate();

        return redirect()->route('beranda')->with('success', 'Selamat datang, ' . $user->nama);
    }
}
