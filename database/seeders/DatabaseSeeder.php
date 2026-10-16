<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\FotoProduk;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'nama' => 'Fauzi',
            'email' => 'fauzi@gmail.com',
            'role' => '1',
            'status' => 1,
            'hp' => '0812345678901',
            'password' => bcrypt('P@55w0rd'),
        ]);
        User::create([
            'nama' => 'Sopian Aji',
            'email' => 'sopian4ji@gmail.com',
            'role' => '0',
            'status' => 1,
            'hp' => '081234567892',
            'password' => bcrypt('P@55word'),
        ]);
        User::create([
            'nama' => 'Ilham',
            'email' => 'ilham@gmail.com',
            'role' => '0',
            'status' => 1,
            'hp' => '081234567893',
            'password' => bcrypt('P@55word'),
        ]);

        $minuman = Kategori::create(['nama_kategori' => 'Minuman Tradisional']);
        $camilan = Kategori::create(['nama_kategori' => 'Camilan Tradisional']);
        $kueBasah = Kategori::create(['nama_kategori' => 'Kue Basah']);
        $jajananPasar = Kategori::create(['nama_kategori' => 'Jajanan Pasar']);
        $gorengan = Kategori::create(['nama_kategori' => 'Gorengan']);

        $produk = [
            [
                'kategori_id' => $kueBasah->id,
                'nama_produk' => 'Kue Lapis Tradisional',
                'detail' => '<p>Kue lapis tradisional dengan lapisan warna-warni yang lembut dan legit. Dibuat dari bahan pilihan seperti tepung beras, santan, dan gula pasir, memberi cita rasa manis yang khas.</p><p>Cocok disajikan sebagai camilan keluarga saat kumpul bersama.</p>',
                'harga' => 25000,
                'berat' => 250,
                'stok' => 40,
                'foto' => '20261015090509_seed01.jpg',
                'status' => 1,
            ],
            [
                'kategori_id' => $jajananPasar->id,
                'nama_produk' => 'Onde-Onde Spesial',
                'detail' => '<p>Onde-onde spesial dengan isian kacang hijau manis dan balutan wijen yang renyah di luar, lembut di dalam.</p><p>Digoreng segar setiap hari sehingga selalu nikmat disantap hangat.</p>',
                'harga' => 20000,
                'berat' => 200,
                'stok' => 35,
                'foto' => '20261015090509_seed02.jpg',
                'status' => 1,
            ],
            [
                'kategori_id' => $kueBasah->id,
                'nama_produk' => 'Klepon Gula Aren',
                'detail' => '<p>Klepon gula aren dengan tekstur kenyal dari tepung ketan dan isian gula aren yang meleleh manis. Dilapisi kelapa parut segar.</p><p>Salah satu jajanan pasar favorit yang wajib dicoba.</p>',
                'harga' => 15000,
                'berat' => 150,
                'stok' => 50,
                'foto' => '20261015090509_seed03.jpg',
                'status' => 1,
            ],
            [
                'kategori_id' => $gorengan->id,
                'nama_produk' => 'Pastel Basah',
                'detail' => '<p>Pastel basah dengan kulit lembut berisi tumis sayuran, bihun, dan telur. Gurih dan mengenyangkan.</p><p>Pilihan tepat untuk camilan sore maupun bekal.</p>',
                'harga' => 18000,
                'berat' => 200,
                'stok' => 30,
                'foto' => '20261015090509_seed04.jpg',
                'status' => 1,
            ],
            [
                'kategori_id' => $gorengan->id,
                'nama_produk' => 'Risoles Mayo',
                'detail' => '<p>Risoles mayo berisi smoked beef, telur, dan mayones, dibalut tepung panir yang crispy.</p><p>Disajikan dengan saus sambal untuk cita rasa yang lebih nikmat.</p>',
                'harga' => 22000,
                'berat' => 250,
                'stok' => 25,
                'foto' => '20261015090509_seed05.jpg',
                'status' => 1,
            ],
            [
                'kategori_id' => $minuman->id,
                'nama_produk' => 'Es Cendol Dawet',
                'detail' => '<p>Es cendol dawet segar dengan santan, gula aren asli, dan cendol kenyal yang dibuat sendiri.</p><p>Minuman pelepas dahaga yang cocok dinikmati siang hari.</p>',
                'harga' => 12000,
                'berat' => 350,
                'stok' => 60,
                'foto' => '20261015090509_seed06.jpg',
                'status' => 1,
            ],
            [
                'kategori_id' => $minuman->id,
                'nama_produk' => 'Wedang Jahe',
                'detail' => '<p>Wedang jahe hangat dari jahe pilihan, gula aren, dan serai. Menghangatkan tubuh dan melegakan tenggorokan.</p><p>Minuman tradisional yang cocok dinikmati malam hari.</p>',
                'harga' => 10000,
                'berat' => 300,
                'stok' => 45,
                'foto' => '20261015090509_seed07.jpg',
                'status' => 1,
            ],
            [
                'kategori_id' => $camilan->id,
                'nama_produk' => 'Kerupuk Melinjo',
                'detail' => '<p>Kerupuk melinjo gurih dan renyah, digoreng dengan minyak bersih.</p><p>Stok sedang tidak tersedia untuk dipesan.</p>',
                'harga' => 9000,
                'berat' => 100,
                'stok' => 20,
                'foto' => '20261015090509_seed08.jpg',
                'status' => 0,
            ],
            [
                'kategori_id' => $camilan->id,
                'nama_produk' => 'Cireng',
                'detail' => '<p>Cireng kenyal berbahan aci yang digoreng garing di luar dan lembut di dalam.</p><p>Nikmat disantap hangat bersama bumbu rujak pedas manis.</p>',
                'harga' => 8000,
                'berat' => 150,
                'stok' => 50,
                'foto' => '20261015090509_seed09.jpg',
                'status' => 1,
            ],
            [
                'kategori_id' => $camilan->id,
                'nama_produk' => 'Pisang Goreng',
                'detail' => '<p>Pisang goreng dengan lapisan garing keemasan dan rasa manis alami dari pisang pilihan.</p><p>Cocok dinikmati hangat bersama teh atau kopi.</p>',
                'harga' => 10000,
                'berat' => 200,
                'stok' => 40,
                'foto' => '20261015090509_seed10.jpg',
                'status' => 1,
            ],
            [
                'kategori_id' => $jajananPasar->id,
                'nama_produk' => 'Kue Mochi',
                'detail' => '<p>Kue mochi lembut dengan isian manis dan taburan tepung halus di permukaannya.</p><p>Teksturnya kenyal dengan rasa manis yang pas, cocok sebagai camilan atau oleh-oleh.</p>',
                'harga' => 15000,
                'berat' => 200,
                'stok' => 40,
                'foto' => '20261015090509_seed11.jpg',
                'status' => 1,
            ],
        ];

        foreach ($produk as $item) {
            $item['user_id'] = 1;
            Produk::create($item);
        }

        FotoProduk::create([
            'produk_id' => 1,
            'foto' => '20241128030648_6747de488e65a.jpg',
        ]);
        FotoProduk::create([
            'produk_id' => 1,
            'foto' => '20241218165842_6762ff4224831.jpg',
        ]);

        $this->copyFotoProduk();
    }

    /**
     * Salin foto produk dari folder seeders ke storage.
     */
    private function copyFotoProduk(): void
    {
        $source = database_path('seeders/img-produk');
        $destinations = [
            public_path('storage/img-produk'),
            storage_path('app/public/img-produk'),
        ];

        foreach ($destinations as $destination) {
            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            foreach (glob($source . '/*.jpg') as $file) {
                $fileName = basename($file);
                if (!file_exists($destination . '/' . $fileName)) {
                    copy($file, $destination . '/' . $fileName);
                }
            }
        }
    }
}
