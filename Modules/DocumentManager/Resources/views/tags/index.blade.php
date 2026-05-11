@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-card">
        <table class="dms-table document-lsg-datatable">
            <thead><tr><th>Tag</th><th>Tipo</th><th>Cor</th><th>Criada</th><th></th></tr></thead>
            <tbody>
                @forelse($tags as $tag)
                    <tr>
                        <td><strong>{{ $tag->name }}</strong><span class="dms-muted">{{ $tag->slug }}</span></td>
                        <td><span class="dms-badge">{{ $tag->type }}</span></td>
                        <td><span class="dms-color-swatch" style="background:{{ $tag->color ?: '#60a5fa' }}"></span></td>
                        <td>{{ $tag->created_at }}</td>
                        <td class="text-right">
                            <a href="{{ route('document-manager.tags.edit', $tag->id) }}" class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Sem tags.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
