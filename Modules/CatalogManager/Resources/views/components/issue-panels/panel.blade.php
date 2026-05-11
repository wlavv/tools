@php
    $count = (int)($panel['count'] ?? 0);
@endphp

<div class="catalog-lsg-panel-card">
    <div class="catalog-lsg-panel-top">
        <div class="catalog-lsg-panel-icon">
            <i class="{{ $panel['icon'] ?? 'fa-solid fa-circle-info' }}"></i>
        </div>

        <div class="catalog-lsg-panel-title">
            <strong>{{ $panel['title'] ?? 'Painel' }}</strong>
            <span>{{ $panel['description'] ?? '' }}</span>
        </div>

        <div class="catalog-lsg-panel-count">{{ $count }}</div>
    </div>

    <div class="catalog-lsg-panel-items">
        @forelse(($panel['items'] ?? []) as $item)
            <a href="{{ $item['url'] ?? '#' }}" class="catalog-lsg-panel-item">
                <span>
                    <strong>{{ $item['title'] ?? 'Item' }}</strong>
                    <small>{{ $item['subtitle'] ?? '' }}</small>
                </span>
                <em>{{ $item['badge'] ?? '' }}</em>
            </a>
        @empty
            <div style="padding:.75rem;color:#15803d;font-size:.82rem;">
                <i class="fa-solid fa-check"></i>
                Sem ocorrências.
            </div>
        @endforelse
    </div>
</div>
