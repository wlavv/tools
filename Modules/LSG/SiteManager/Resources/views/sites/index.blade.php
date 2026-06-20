@extends('site-manager::layouts.module')

@section('module-content')
    <div class="sm-card">
        <div class="sm-table-wrap">
            <table class="sm-table">
                <thead><tr><th>Site</th><th>Tipo</th><th>Ambiente</th><th>Monitorizacao</th><th>Estado</th><th>Acoes</th></tr></thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><div class="sm-site-title"><strong>{{ $item->name }}</strong><span>{{ $item->domain ?: $item->public_url ?: '-' }}</span></div></td>
                        <td>{{ config('site-manager.site_types.' . $item->site_type, $item->site_type) }}</td>
                        <td>{{ $item->environment }}</td>
                        <td>PSI: {{ $item->monitor_pagespeed ? 'Sim' : 'Nao' }} / UP: {{ $item->monitor_availability ? 'Sim' : 'Nao' }}</td>
                        <td><span class="sm-badge">{{ $item->status }}</span></td>
                        <td>
                            <div class="sm-actions-row">
                                <a class="sm-btn" href="{{ route('lsg.site_manager.sites.show', $item) }}"><i class="fa-solid fa-eye"></i></a>
                                <a class="sm-btn sm-btn-warning" href="{{ route('lsg.site_manager.sites.edit', $item) }}"><i class="fa-solid fa-pencil"></i></a>
                                <form method="POST" action="{{ route('lsg.site_manager.sites.destroy', $item) }}" data-confirm="Arquivar este site?">
                                    @csrf @method('DELETE')
                                    <button class="sm-btn sm-btn-danger" type="submit"><i class="fa-solid fa-box-archive"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Sem sites registados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($items, 'links'))<div class="mt-3">{{ $items->links() }}</div>@endif
    </div>
@endsection
