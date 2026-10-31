<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController; 
use App\Http\Controllers\LoginController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RajaOngkirController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () { 
    // return view('welcome'); 
    return redirect()->route('beranda'); 
}); 
Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');

Route::get('/produk/all', [ProdukController::class, 'produkAll'])->name('produk.all');
Route::get('/produk/kategori/{id}', [ProdukController::class, 'produkKategori'])->name('produk.kategori');
Route::get('/produk/detail/{id}', [ProdukController::class, 'detail'])->name('produk.detail');

// Cek ongkir RajaOngkir (Minggu 7)
Route::view('/cek-ongkir', 'ongkir')->name('ongkir.index');
Route::get('/provinces', [RajaOngkirController::class, 'getProvinces'])->name('ongkir.provinces');
Route::get('/cities', [RajaOngkirController::class, 'getCities'])->name('ongkir.cities');
Route::get('/districts', [RajaOngkirController::class, 'getDistricts'])->name('ongkir.districts');
Route::post('/cost', [RajaOngkirController::class, 'getCost'])->name('ongkir.cost');

// Route autentikasi customer via Google
Route::get('/auth/redirect', [CustomerController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/google/callback', [CustomerController::class, 'callback'])->name('auth.callback');
Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');

// Login customer dengan email & password (alternatif selama OAuth Google belum siap)
Route::get('/customer/login', [CustomerController::class, 'loginForm'])->name('customer.login');
Route::post('/customer/login', [CustomerController::class, 'loginProses'])->name('customer.login.proses');
Route::get('/customer/register', [CustomerController::class, 'registerForm'])->name('customer.register');
Route::post('/customer/register', [CustomerController::class, 'registerProses'])->name('customer.register.proses');

Route::get('backend/beranda', [BerandaController::class, 'berandaBackend'])->name('backend.beranda')->middleware('auth'); 
 
Route::get('backend/login', [LoginController::class, 'loginBackend'])->name('backend.login'); 
Route::post('backend/login', [LoginController::class, 'authenticateBackend'])->name('backend.login'); 
Route::post('backend/logout', [LoginController::class, 'logoutBackend'])->name('backend.logout'); 

// Route::resource('backend/user', UserController::class)->middleware('auth');
Route::resource('backend/user', UserController::class, ['as' => 'backend'])->middleware('auth');
Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
Route::get('backend/laporan/formuser', [UserController::class, 'formUser'])
    ->name('backend.laporan.formuser')
    ->middleware('auth');
Route::post('backend/laporan/cetakuser', [UserController::class, 'cetakUser'])
    ->name('backend.laporan.cetakuser')
    ->middleware('auth');

Route::resource('backend/kategori', KategoriController::class, ['as' => 'backend'])->middleware('auth');

Route::resource('backend/customer', CustomerController::class, ['as' => 'backend'])->middleware('auth');

Route::resource('backend/produk', ProdukController::class, ['as' => 'backend'])->middleware('auth');
Route::post('foto-produk/store', [ProdukController::class, 'storeFoto'])
    ->name('backend.foto_produk.store')
    ->middleware('auth');
    Route::delete('foto-produk/{id}', [ProdukController::class, 'destroyFoto'])
    ->name('backend.foto_produk.destroy')
    ->middleware('auth');
Route::get('produk/{id}', [ProdukController::class, 'show'])->name('backend.produk.show')->middleware('auth');
Route::get('/{id}/edit', [ProdukController::class, 'edit'])->name('edit');
Route::delete('/{id}', [ProdukController::class, 'destroy'])->name('destroy');
Route::get('backend/laporan/formproduk', [ProdukController::class, 'formProduk'])
    ->name('backend.laporan.formproduk')
    ->middleware('auth');
Route::post('backend/laporan/cetakproduk', [ProdukController::class, 'cetakProduk'])
    ->name('backend.laporan.cetakproduk')
    ->middleware('auth');

// Group route untuk customer
Route::middleware('is.customer')->group(function () {

    // Route untuk menampilkan halaman akun customer
    Route::get('/customer/akun/{id}', [CustomerController::class, 'akun'])
        ->name('customer.akun');

    // Route untuk mengupdate data akun customer
    Route::put('/customer/updateakun/{id}', [CustomerController::class, 'updateAkun'])
        ->name('customer.updateakun');

    // Route keranjang belanja
    Route::post('add-to-cart/{id}', [OrderController::class, 'addToCart'])->name('order.addToCart');
    Route::get('cart', [OrderController::class, 'viewCart'])->name('order.cart');
    Route::post('cart/update/{id}', [OrderController::class, 'updateCart'])->name('order.updateCart');
    Route::post('remove/{id}', [OrderController::class, 'removeFromCart'])->name('order.remove');

    // Ongkir
    Route::post('select-shipping', [OrderController::class, 'selectShipping'])->name('order.selectShipping');
    Route::get('provinces', [OrderController::class, 'getProvinces']);
    Route::get('cities', [OrderController::class, 'getCities']);
    Route::post('cost', [OrderController::class, 'getCost']);
    Route::post('updateongkir', [OrderController::class, 'updateongkir'])->name('order.updateongkir');

    Route::get('selectpayment', [OrderController::class, 'selectpayment'])->name('order.selectpayment');
    Route::post('checkout', [OrderController::class, 'checkoutProses'])->name('order.checkoutProses');
    Route::get('history', [OrderController::class, 'history'])->name('order.history');
});