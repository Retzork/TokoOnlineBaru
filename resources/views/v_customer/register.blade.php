@extends('v_layouts.app')
@section('content')
<!-- template -->

<div class="row">
	<div class="col-md-12">
		<div class="billing-details">
			<div class="section-title">
				<h3 class="title">Daftar Akun Baru</h3>
			</div>

			<form action="{{ route('customer.register.proses') }}" method="post">
				@csrf

				<div class="col-md-12">
					<div class="form-group">
						<label>Nama Lengkap</label>
						<input type="text" name="nama" value="{{ old('nama') }}" class="form-control @error('nama') is-invalid @enderror" placeholder="Masukkan Nama Lengkap">
						@error('nama')
						<span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
						@enderror
					</div>

					<div class="form-group">
						<label>Email</label>
						<input type="text" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Masukkan Email">
						@error('email')
						<span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
						@enderror
					</div>

					<div class="form-group">
						<label>Password</label>
						<input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan Password">
						@error('password')
						<span class="invalid-feedback alert-danger" role="alert">{{ $message }}</span>
						@enderror
					</div>

					<div class="form-group">
						<label>Konfirmasi Password</label>
						<input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi Password">
					</div>

					<div class="pull-left">
						<button type="submit" class="primary-btn">Daftar Sekarang</button>
					</div>
				</div>
			</form>

			<div class="col-md-12">
				<br>
				<hr>
				<p>Atau daftar lebih cepat dengan Google:
					<a href="{{ route('auth.redirect') }}" class="btn btn-danger btn-sm">
						<i class="fa fa-google"></i> Daftar dengan Google
					</a>
				</p>
				<p>Sudah punya akun? 
					<a href="{{ route('customer.login') }}">Login di sini</a>
				</p>
			</div>
		</div>
	</div>
</div>
<!-- end template-->
@endsection
