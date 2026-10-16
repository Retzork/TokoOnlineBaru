@extends('backend.v_layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body border-top">
                <!-- Judul Halaman -->
                <h5 class="card-title">{{ $judul }}</h5>

                <!-- Pesan Selamat Datang -->
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">Selamat Datang, {{ Auth::user()->nama ?? 'Pengguna' }}</h4>
                    Aplikasi Toko Online dengan hak akses yang Anda miliki sebagai 
                    <b>
                        @if (Auth::user()->role === 1)
                            Super Admin
                        @elseif(Auth::user()->role === 0)
                            Admin
                        @else
                            Pengguna
                        @endif
                    </b>.
                    Ini adalah halaman utama dari aplikasi Web Programming: Studi Kasus Toko Online.
                    <hr>
                    <p class="mb-0">Kuliah..? BSI Aja !!!</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Akhir Konten -->
@endsection
