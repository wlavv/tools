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

<form method="post" action="{{ route('permission_role_manager.users.update', $userRecord->id) }}">
    @csrf
    @method('PUT')
    <div class="prm-card">
        <h4>Dados do User</h4>
        <p class="prm-muted">Deixa a password em branco para manter a atual.</p>
        <div class="prm-form-grid">
            <div>
                <label>Nome</label>
                <input class="prm-input" type="text" name="name" value="{{ old('name', $userRecord->name) }}" required>
            </div>
            <div>
                <label>Email</label>
                <input class="prm-input" type="email" name="email" value="{{ old('email', $userRecord->email) }}" required>
            </div>
            <div>
                <label>Nova password</label>
                <input class="prm-input" type="password" name="password" autocomplete="new-password">
            </div>
            <div>
                <label>Confirmar nova password</label>
                <input class="prm-input" type="password" name="password_confirmation" autocomplete="new-password">
            </div>
        </div>
        <br>
        <button class="prm-btn"><i class="fa-solid fa-floppy-disk"></i> Guardar dados</button>
    </div>
</form>

<form method="post" action="{{ route('permission_role_manager.users.roles.sync',$userRecord->id) }}">
    @csrf
    <div class="prm-card">
        <h4>Perfis</h4>
        <p class="prm-muted">{{ $userRecord->name }} - {{ $userRecord->email }}</p>
        <div class="prm-check-list">
            @foreach($roles as $role)
                <label class="prm-check">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id,$selectedRoles))>
                    <strong>{{ $role->name }}</strong><br>
                    <span class="prm-muted">{{ $role->slug }}</span>
                </label>
            @endforeach
        </div>
        <br>
        <button class="prm-btn"><i class="fa-solid fa-floppy-disk"></i> Guardar Perfis</button>
    </div>
</form>

@endsection
