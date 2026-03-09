<div class="al-grid al-panel">
    @if($assets->count() === 0)
        <div class="al-empty">Ainda não existem assets registados.</div>
    @else
        <div class="al-grid-list">
            @foreach($assets as $asset)
                @include('asset-library::Includes._components.asset-card', ['asset' => $asset])
            @endforeach
        </div>

        <div style="margin-top: 1rem;">
            {{ $assets->links() }}
        </div>
    @endif
</div>
