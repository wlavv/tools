@extends('permission-role-manager::layouts.module')
@section('module-content')
@if($errors->any())
    <div class="prm-alert error">
        <strong>Corrige os campos assinalados.</strong>
        <ul style="margin:8px 0 0 18px">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="lsg-form" method="post" action="{{ route('permission_role_manager.users.store') }}">
    @csrf
    <div class="prm-card">
        <h4>Dados do User</h4>
        <div class="prm-form-grid">
            <div>
                <label>Nome</label>
                <input class="prm-input" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label>Email</label>
                <input class="prm-input" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div>
                <label>Password</label>
                <input class="prm-input" type="password" name="password" required>
            </div>
            <div>
                <label>Confirmar Password</label>
                <input class="prm-input" type="password" name="password_confirmation" required>
            </div>
        </div>
    </div>

    <div class="prm-card">
        <h4>Roles iniciais</h4>
        <div class="prm-check-list">
            @forelse($roles as $role)
                <label class="prm-check">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, old('roles', [])))>
                    <strong>{{ $role->name }}</strong><br>
                    <span class="prm-muted">{{ $role->slug }}</span>
                </label>
            @empty
                <p class="prm-muted">Ainda nao existem roles ativas.</p>
            @endforelse
        </div>
    </div>
</form>
@endsection
