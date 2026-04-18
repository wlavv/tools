@extends('layouts.blank')
@section('content')
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header"> <img src="/admin/images/logo.png?t={{ rand() }}" alt="Logo"> </div>
            <form method="POST" id="login_form" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label>{{ __('Email Address') }}</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required >
                    @error('email') <div class="invalid-feedback"> {{ $message }} </div> @enderror
                </div>

                <div class="form-group">
                    <label>{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                    @error('password') <div class="invalid-feedback"> {{ $message }} </div> @enderror
                </div>
                <div class="form-group remember">
                    <label> <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> {{ __('Remember Me') }} </label>
                </div>
                <button type="submit" class="btn-login" > {{ __('Login') }} </button>
            </form>
        </div>
    </div>
@endsection

@section('styles')
<style>
    body{background:linear-gradient(135deg,#1f2937,#111827);height:100vh;margin:0;display:flex;align-items:center;justify-content:center;font-family:system-ui,-apple-system,sans-serif}.login-wrapper{width:100%;display:flex;justify-content:center}.login-card{width:420px;background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border-radius:10px;padding:30px;box-shadow:0 10px 40px rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1)}.login-header{text-align:center;margin-bottom:25px}.login-header img{width:220px}.form-group{margin-bottom:18px}.form-group label{display:block;margin-bottom:5px;color:#cbd5e1;font-size:14px}.form-control{width:100%;padding:10px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#fff}.form-control:focus{outline:0;border-color:#3b82f6}.remember{font-size:13px;color:#9ca3af}.btn-login{width:100%;padding:10px;border-radius:6px;border:none;background:#3b82f6;color:#fff;font-weight:500;cursor:pointer;transition:.2s}.btn-login:hover{background:#2563eb}.invalid-feedback{color:#f87171;font-size:12px;margin-top:5px}
</style>
@endsection