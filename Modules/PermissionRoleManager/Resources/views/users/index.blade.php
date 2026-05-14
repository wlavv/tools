@extends('permission-role-manager::layouts.module')
@section('module-content')
<div class="prm-card">
    <form>
        <input class="prm-input" name="q" value="{{ request('q') }}" placeholder="Pesquisar user">
    </form>
</div>
<div class="prm-card">
    <table class="prm-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $roleCounts[$user->id] ?? 0 }}</td>
                    <td class="prm-actions">
                        <a
                            class="prm-btn"
                            href="{{ route('permission_role_manager.users.edit',$user->id) }}"
                            title="Gerir acesso"
                            aria-label="Gerir acesso"
                        >
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $users->links() }}
</div>
@endsection
