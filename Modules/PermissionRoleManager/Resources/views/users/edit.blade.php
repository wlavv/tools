@extends('permission-role-manager::layouts.module')
@section('module-content')
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
