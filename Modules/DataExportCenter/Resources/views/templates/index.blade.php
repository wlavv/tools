@extends('data-export-center::layout')

@section('module-content')
    <h1>Templates de relatório</h1>

    <div class="panel">
        <h2>Criar/atualizar template</h2>
        <form method="post" action="{{ route('data_export_center.templates.store') }}">
            @csrf
            <p><input name="key" placeholder="key ex: webtools-manager-default" required></p>
            <p><input name="name" placeholder="Nome" required></p>
            <p><input name="profile_key" placeholder="Profile key opcional"></p>
            <p>
                <select name="scope_type">
                    <option value="global">Global</option>
                    <option value="platform">Platform</option>
                    <option value="shop">Shop</option>
                    <option value="module">Module</option>
                </select>
                <input name="scope_key" placeholder="scope key: webtools-manager, shop-123...">
                <label><input type="checkbox" name="is_default" value="1"> default</label>
            </p>
            <p><input name="title_template" placeholder="Titulo Blade: @{{ $title }}"></p>
            <p><textarea name="header_html" placeholder="Header HTML/Blade"></textarea></p>
            <p><textarea name="footer_html" placeholder="Footer HTML/Blade"></textarea></p>
            <p><textarea name="body_html" placeholder="Body HTML/Blade opcional"></textarea></p>
            <p><textarea name="css" placeholder="CSS opcional"></textarea></p>
            <button type="submit">Guardar</button>
        </form>
    </div>

    <table>
        <thead>
        <tr>
            <th>Key</th>
            <th>Name</th>
            <th>Profile</th>
            <th>Scope</th>
            <th>Default</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($templates as $template)
            <tr>
                <td>{{ $template->key }}</td>
                <td>{{ $template->name }}</td>
                <td>{{ $template->profile_key }}</td>
                <td>{{ $template->scope_type }} / {{ $template->scope_key }}</td>
                <td>{{ $template->is_default ? 'yes' : 'no' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $templates->links() }}
@endsection
