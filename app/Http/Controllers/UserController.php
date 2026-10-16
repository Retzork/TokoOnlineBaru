<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ImageHelper;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::orderBy('email')->get();
        return view('backend.v_user.index', [
        'judul' => 'Data User',
        'index' => $user
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.v_user.create', [
            'judul' => 'Tambah User',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:user,email',
            'role' => 'required|in:0,1',
            'hp' => 'required|digits_between:10,13',
            'password' => [
                'required',
                'min:4', // Minimal 4 karakter
                'confirmed', // Harus cocok dengan password_confirmation
            ],
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:1024',
        ]);

    // Hitung nomor urut berikutnya untuk `kode_user`
    $lastUser = User::orderBy('id', 'desc')->first();

    // Tentukan nomor berikutnya
    if ($lastUser && preg_match('/USR(\d{4})/', $lastUser->kode_user, $matches)) {
        $nextNumber = (int)$matches[1] + 1;
    } else {
        $nextNumber = 1; // Jika tidak ada data atau format salah, mulai dari 1
    }
    
    // Format `kode_user` dengan padding nol
    $validatedData['kode_user'] = 'USR' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    
    // Set status default
        $validatedData['status'] = 1;

    // Proses unggah foto (jika ada)
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/img-user'), $fileName);
            $validatedData['foto'] = 'img-user/' . $fileName;
        }

    // Hash password
        $validatedData['password'] = Hash::make($validatedData['password']);

    // Simpan ke database
        User::create($validatedData);
        
        return redirect()->route('backend.beranda')->with('success', 'Data berhasil disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('backend.v_user.edit', [ 
            'judul' => 'Ubah User',
            'edit' => $user 
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Find the user by ID, or throw a 404 error if not found
        $user = User::findOrFail($id);

    // Validation rules
        $rules = [
            'nama' => 'required|max:255',
            'role' => 'required',
            'status' => 'required',
            'hp' => 'required|min:10|max:13',
            'foto' => 'image|mimes:jpeg,jpg,png,gif|file|max:1024',
        ];

    // Custom validation messages
        $messages = [
            'foto.image' => 'Format gambar gunakan file dengan ekstensi jpeg, jpg, png, atau gif.',
            'foto.max' => 'Ukuran file gambar Maksimal adalah 1024 KB.',
        ];

    // If email is changed, validate it
        if ($request->email != $user->email) {
            $rules['email'] = 'required|max:255|email|unique:users';
        }

    // Validate request data
        $validatedData = $request->validate($rules, $messages);

    // If a new photo is uploaded
        if ($request->file('foto')) {
        // Delete old image if exists
            if ($user->foto) {
                $oldImagePath = public_path('storage/img-user/') . $user->foto;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
                }
            }

        // Get the new uploaded image
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $originalFileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
            $directory = 'storage/img-user/';

        // Resize and save the new image
        ImageHelper::uploadAndResize($file, $directory, $originalFileName, 385, 400);

        // Save the new file name in validated data
        $validatedData['foto'] = $originalFileName;
        }

        // Update the user data in the database
        $user->update($validatedData);

        // Redirect with success message
        return redirect()->route('backend.user.index')->with('success', 'Data berhasil diperbaharui');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id); // Perbaiki nama model dengan kapital
        if ($user->foto) {
            $oldImagePath = public_path('storage/img-user/') . $user->foto;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        $user->delete();
        return redirect()->route('backend.user.index')->with('success', 'Data berhasil dihapus');
    }

    public function formUser()
    {
        return view('backend.v_user.form', [
            'judul' => 'Laporan Data User',
        ]);
    }

    public function cetakUser(Request $request)
    {
        // Menambahkan aturan validasi
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required' => 'Tanggal Awal harus diisi.',
            'tanggal_akhir.required' => 'Tanggal Akhir harus diisi.',
            'tanggal_akhir.after_or_equal' => 'Tanggal Akhir harus lebih besar atau sama dengan Tanggal Awal.',
        ]);

        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $query = User::whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('id', 'desc');
        $user = $query->get();

        return view('backend.v_user.cetak', [
            'judul' => 'Laporan User',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'cetak' => $user
        ]);
    }

}
