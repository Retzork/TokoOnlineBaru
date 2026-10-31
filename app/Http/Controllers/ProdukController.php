<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\FotoProduk; // Assuming FotoProduk model exists
use App\Helpers\ImageHelper;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::orderBy('updated_at', 'desc')->get();
        return view('backend.v_produk.index', [
            'judul' => 'Data Produk',
            'index' => $produk
        ]);
    }

    public function detail($id)
    {
        $fotoProdukTambahan = FotoProduk::where('produk_id', $id)->get();
        $row = Produk::where('status', 1)->findOrFail($id);
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('v_produk.detail', [
            'judul' => 'Detail Produk',
            'kategori' => $kategori,
            'row' => $row,
            'fotoProdukTambahan' => $fotoProdukTambahan
        ]);
    }

    public function produkKategori($id)
    {
        $produk = Produk::where('status', 1)
            ->where('kategori_id', $id)
            ->orderBy('updated_at', 'desc')
            ->paginate(6);
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('v_produk.produkkategori', [
            'judul' => 'Filter Kategori',
            'kategori' => $kategori,
            'produk' => $produk
        ]);
    }

    public function produkAll()
    {
        $produk = Produk::where('status', 1)->orderBy('updated_at', 'desc')->paginate(6);
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('v_produk.index', [
            'judul' => 'Semua Produk',
            'kategori' => $kategori,
            'produk' => $produk
        ]);
    }

    public function create()
    {
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();
        return view('backend.v_produk.create', [
            'judul' => 'Tambah Produk',
            'kategori' => $kategori
        ]);
    }

    public function store(Request $request)
    {
        // Validasi data
        $validatedData = $request->validate([
            'kategori_id' => 'required',
            'nama_produk' => 'required|max:255|unique:produk',
            'detail' => 'required',
            'harga' => 'required|numeric',
            'berat' => 'required|numeric',
            'stok' => 'required|integer',
            'foto' => 'required|image|mimes:jpeg,jpg,png,gif|file|max:1024',
        ], [
            'foto.image' => 'Format gambar gunakan file dengan ekstensi jpeg, jpg, png, atau gif.',
            'foto.max' => 'Ukuran file gambar maksimal adalah 1024 KB.',
        ]);

        // Tambahkan status default
        $validatedData['status'] = 0;

        // Proses upload file foto
        if ($request->file('foto')) {
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $originalFileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
            $directory = 'storage/img-produk/';

            // Simpan gambar asli
            $fileName = ImageHelper::uploadAndResize($file, $directory, $originalFileName);
            $validatedData['foto'] = $originalFileName;

            // Buat thumbnail ukuran besar (lg)
            $thumbnailLg = 'thumb_lg_' . $originalFileName;
            ImageHelper::uploadAndResize($file, $directory, $thumbnailLg, 800, null);

            // Buat thumbnail ukuran sedang (md)
            $thumbnailMd = 'thumb_md_' . $originalFileName;
            ImageHelper::uploadAndResize($file, $directory, $thumbnailMd, 500, null);

            // Buat thumbnail ukuran kecil (sm)
            $thumbnailSm = 'thumb_sm_' . $originalFileName;
            ImageHelper::uploadAndResize($file, $directory, $thumbnailSm, 100, null);
        }

        // Simpan data ke tabel produk
        $validatedData['user_id'] = auth()->id();
        $produk = Produk::create($validatedData); 

        // Redirect ke halaman index produk dengan pesan sukses
        return redirect()->route('backend.produk.index')->with('success', 'Data berhasil tersimpan');
    }

    public function show(string $id)
    {
        // Menggunakan relasi untuk mengambil foto produk
        $produk = Produk::with('fotos')->findOrFail($id); // Assuming the relation is defined as 'fotos' in Produk model
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('backend.v_produk.show', [
            'judul' => 'Detail Produk',
            'show' => $produk,
            'kategori' => $kategori
        ]);
    }

    public function storeFoto(Request $request)
    {
        // Validasi input
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'foto_produk.*' => 'image|mimes:jpeg,jpg,png,gif|file|max:1024',
        ]);

        // Jika ada file yang diunggah
        if ($request->hasFile('foto_produk')) {
            foreach ($request->file('foto_produk') as $file) {
                // Buat nama file yang unik
                $extension = $file->getClientOriginalExtension();
                $filename = date('YmdHis') . '_' . uniqid() . '.' . $extension;
                $directory = 'storage/img-produk/';

                // Simpan dan resize gambar menggunakan ImageHelper
                ImageHelper::uploadAndResize($file, $directory, $filename, 800, null);

                // Simpan data ke database
                FotoProduk::create([
                    'produk_id' => $request->produk_id,
                    'foto' => $filename,
                ]);
            }
        }

        // Redirect ke halaman detail produk
        return redirect()->route('backend.produk.show', $request->produk_id)
            ->with('success', 'Foto berhasil ditambahkan.');
    }

    public function destroyFoto($id)
    {
        $foto = FotoProduk::findOrFail($id);
        $produkId = $foto->produk_id;

        // Hapus file gambar
        $imagePath = public_path('storage/img-produk/') . $foto->foto;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Hapus dari database
        $foto->delete();

        return redirect()->route('backend.produk.show', $produkId)
                         ->with('success', 'Foto berhasil dihapus.');
    }
    
    public function edit(string $id)
    {
        $produk = Produk::findOrFail($id);
        $kategori = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('backend.v_produk.edit', [
            'judul' => 'Ubah Produk',
            'edit' => $produk,
            'kategori' => $kategori,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $produk = Produk::findOrFail($id);

        $rules = [
            'nama_produk' => 'required|max:255|unique:produk,nama_produk,' . $id,
            'kategori_id' => 'required',
            'status' => 'required',
            'detail' => 'required',
            'harga' => 'required',
            'berat' => 'required',
            'stok' => 'required',
            'foto' => 'image|mimes:jpeg,jpg,png,gif|file|max:1024',
        ];

        $messages = [
            'foto.image' => 'Format gambar harus berupa jpeg, jpg, png, atau gif.',
            'foto.max' => 'Ukuran gambar maksimal adalah 1024 KB.',
        ];

        $validatedData = $request->validate($rules, $messages);
        $validatedData['user_id'] = auth()->id();

        if ($request->file('foto')) {
            // Hapus gambar lama
            if ($produk->foto) {
                $imagePaths = [
                    'storage/img-produk/' . $produk->foto,
                    'storage/img-produk/thumb_lg_' . $produk->foto,
                    'storage/img-produk/thumb_md_' . $produk->foto,
                    'storage/img-produk/thumb_sm_' . $produk->foto,
                ];

                foreach ($imagePaths as $path) {
                    if (file_exists(public_path($path))) {
                        unlink(public_path($path));
                    }
                }
            }

            // Upload gambar baru
            $file = $request->file('foto');
            $originalFileName = date('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $directory = 'storage/img-produk/';

            // Simpan gambar asli dan thumbnail
            $validatedData['foto'] = ImageHelper::uploadAndResize($file, $directory, $originalFileName);
            ImageHelper::uploadAndResize($file, $directory, 'thumb_lg_' . $originalFileName, 800, null);
            ImageHelper::uploadAndResize($file, $directory, 'thumb_md_' . $originalFileName, 500, null);
            ImageHelper::uploadAndResize($file, $directory, 'thumb_sm_' . $originalFileName, 100, null);
        }

        $produk->update($validatedData);

        return redirect()->route('backend.produk.index')->with('success', 'Data berhasil diperbaharui');
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $directory = public_path('storage/img-produk/');
        
        if ($produk->foto) {
            // Hapus gambar asli
            $oldImagePath = $directory . $produk->foto;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }

            // Hapus thumbnail lg
            $thumbnailLg = $directory . 'thumb_lg_' . $produk->foto;
            if (file_exists($thumbnailLg)) {
                unlink($thumbnailLg);
            }

            // Hapus thumbnail md
            $thumbnailMd = $directory . 'thumb_md_' . $produk->foto;
            if (file_exists($thumbnailMd)) {
                unlink($thumbnailMd);
            }

            // Hapus thumbnail sm
            $thumbnailSm = $directory . 'thumb_sm_' . $produk->foto;
            if (file_exists($thumbnailSm)) {
                unlink($thumbnailSm);
            }
        }

        // Hapus data produk dari database
        $produk->delete();

        // Redirect dengan pesan sukses
        return redirect()->route('backend.produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    // Method untuk Form Laporan Produk
    public function formProduk()
    {
        return view('backend.v_produk.form', [
            'judul' => 'Laporan Data Produk',
        ]);
    }

    // Method untuk Cetak Laporan Produk
    public function cetakProduk(Request $request)
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

        $query = Produk::whereBetween('updated_at', [$tanggalAwal, $tanggalAkhir])
            ->orderBy('id', 'desc');

        $produk = $query->get();

        return view('backend.v_produk.cetak', [
            'judul' => 'Laporan Produk',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'cetak' => $produk
        ]);
    }

}
