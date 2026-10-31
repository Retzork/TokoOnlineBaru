@extends('v_layouts.app')
@section('content')
<!-- template -->

<div class="row">
	<div class="col-md-12">
		<div class="billing-details">
			<div class="section-title">
				<h3 class="title">Login Customer</h3>
			</div>

			<form action="{{ route('customer.login.proses') }}" method="post">
				@csrf

				<div class="col-md-12">
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

					<div class="pull-left">
						<button type="submit" class="primary-btn">Masuk</button>
					</div>
				</div>
			</form>

			<div class="col-md-12">
				<br>
				<p>Atau masuk menggunakan akun Google:
					<a href="{{ route('auth.redirect') }}">Login dengan Google</a>
				</p>
			</div>
		</div>
	</div>
</div>
<!-- end template-->
@endsection
