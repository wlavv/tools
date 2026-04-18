@extends('layouts.blank')

@section('content')
<div class="ls-login-page">
    <div class="ls-login-bg">
        <div class="ls-login-orb orb-1"></div>
        <div class="ls-login-orb orb-2"></div>
        <div class="ls-login-grid"></div>
    </div>

    <div class="ls-login-shell">
        <div class="ls-login-card">
            <div class="ls-login-top">
                <div class="ls-login-brand">
                    <img src="/admin/images/logo.png?t={{ rand() }}" alt="Webtools Manager Logo">
                </div>
                <div class="ls-login-copy">
                    <h1>Welcome back</h1>
                    <p>Sign in to access your workspace.</p>
                </div>
            </div>

            <form method="POST" id="login_form" action="{{ route('login') }}" class="ls-login-form">
                @csrf

                <div class="ls-form-group">
                    <label for="email">{{ __('Email Address') }}</label>
                    <input id="email" type="email" class="ls-form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus onchange="loginWithQRCode()">
                    @error('email')
                        <div class="ls-invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="ls-form-group">
                    <label for="password">{{ __('Password') }}</label>
                    <input id="password" type="password" class="ls-form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    @error('password')
                        <div class="ls-invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="ls-login-row">
                    <label class="ls-check">
                        <input class="ls-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>{{ __('Remember Me') }}</span>
                    </label>
                </div>

                <button type="submit" class="ls-login-btn">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>{{ __('Login') }}</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
html,body{height:100%}body{margin:0;padding:0;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:radial-gradient(circle at top left,#1e293b 0,#0f172a 45%,#020617 100%);color:#e2e8f0;overflow:hidden}.ls-login-page{position:relative;min-height:100vh;width:100%;display:flex;align-items:center;justify-content:center;padding:24px}.ls-login-bg{position:absolute;inset:0;overflow:hidden;pointer-events:none}.ls-login-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:36px 36px;mask-image:radial-gradient(circle at center,black 35%,transparent 90%);opacity:.35}.ls-login-orb{position:absolute;border-radius:999px;filter:blur(80px);opacity:.35}.ls-login-orb.orb-1{width:340px;height:340px;background:rgba(59,130,246,.35);top:-60px;left:-40px}.ls-login-orb.orb-2{width:300px;height:300px;background:rgba(250,204,21,.18);right:-40px;bottom:-40px}.ls-login-shell{position:relative;z-index:2;width:100%;max-width:460px}.ls-login-card{position:relative;background:linear-gradient(180deg,rgba(255,255,255,.11) 0,rgba(255,255,255,.06) 100%);border:1px solid rgba(255,255,255,.12);border-radius:22px;backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);box-shadow:0 24px 60px rgba(2,6,23,.45),inset 0 1px 0 rgba(255,255,255,.08);padding:30px}.ls-login-card:before{content:"";position:absolute;inset:0;border-radius:22px;padding:1px;background:linear-gradient(180deg,rgba(255,255,255,.22),rgba(255,255,255,0));-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none}.ls-login-top{display:flex;flex-direction:column;align-items:center;text-align:center;gap:14px;margin-bottom:24px}.ls-login-brand{width:100%;display:flex;justify-content:center}.ls-login-brand img{max-width:240px;width:100%;height:auto;object-fit:contain;filter:drop-shadow(0 10px 24px rgba(0,0,0,.2))}.ls-login-copy h1{margin:0;font-size:30px;line-height:1.05;font-weight:700;letter-spacing:-.03em;color:#fff}.ls-login-copy p{margin:6px 0 0;font-size:14px;line-height:1.55;color:#cbd5e1}.ls-login-form{display:flex;flex-direction:column;gap:16px}.ls-form-group{display:flex;flex-direction:column;gap:7px}.ls-form-group label{font-size:12px;font-weight:600;letter-spacing:.02em;color:#cbd5e1}.ls-form-control{max-width:100%;height:48px;padding:0 14px;border-radius:14px;border:1px solid rgba(255,255,255,.1);background:rgba(15,23,42,.55);color:#fff;font-size:14px;outline:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.03);transition:border-color .2s ease,box-shadow .2s ease,background .2s ease}.ls-form-control::placeholder{color:#94a3b8}.ls-form-control:focus{border-color:rgba(96,165,250,.8);background:rgba(15,23,42,.72);box-shadow:0 0 0 4px rgba(59,130,246,.18)}.ls-form-control.is-invalid{border-color:rgba(248,113,113,.7)}.ls-invalid-feedback{font-size:12px;font-weight:500;color:#fca5a5}.ls-login-row{display:flex;align-items:center;justify-content:space-between;gap:12px}.ls-check{display:inline-flex;align-items:center;gap:10px;font-size:13px;color:#cbd5e1;cursor:pointer;user-select:none}.ls-check-input{width:16px;height:16px;accent-color:#3b82f6}.ls-login-btn{height:50px;border:0;border-radius:14px;background:linear-gradient(135deg,#2563eb 0,#3b82f6 55%,#60a5fa 100%);color:#fff;font-size:14px;font-weight:700;letter-spacing:.02em;display:inline-flex;align-items:center;justify-content:center;gap:10px;cursor:pointer;box-shadow:0 16px 28px rgba(37,99,235,.35),inset 0 1px 0 rgba(255,255,255,.2);transition:transform .18s ease,box-shadow .18s ease,filter .18s ease}.ls-login-btn:hover{transform:translateY(-1px);box-shadow:0 20px 34px rgba(37,99,235,.42),inset 0 1px 0 rgba(255,255,255,.24);filter:brightness(1.03)}.ls-login-btn:active{transform:translateY(0)}@media (max-width:575.98px){.ls-login-page{padding:16px}.ls-login-card{padding:22px 18px;border-radius:18px}.ls-login-card:before{border-radius:18px}.ls-login-copy h1{font-size:24px}.ls-login-brand img{max-width:200px}.ls-form-control,.ls-login-btn{height:46px}}
</style>
@endsection