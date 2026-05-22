@php
    $aiConsensusNavItems = [
        [
            'route' => 'ai_consensus.index',
            'label' => 'Overview',
            'icon' => 'fa-solid fa-table-columns',
            'active' => request()->routeIs('ai_consensus.index'),
        ],
        [
            'route' => 'ai_consensus.runs.index',
            'label' => 'Runs',
            'icon' => 'fa-solid fa-stream',
            'active' => request()->routeIs('ai_consensus.runs.*'),
        ],
        [
            'route' => 'ai_consensus.runs.create',
            'label' => 'New Run',
            'icon' => 'fa-solid fa-plus',
            'active' => request()->routeIs('ai_consensus.runs.create'),
            'variant' => 'primary',
        ],
        [
            'route' => 'ai_consensus.templates.index',
            'label' => 'Templates',
            'icon' => 'fa-solid fa-layer-group',
            'active' => request()->routeIs('ai_consensus.templates.*'),
        ],
        [
            'route' => 'ai_consensus.providers.index',
            'label' => 'Providers',
            'icon' => 'fa-solid fa-plug',
            'active' => request()->routeIs('ai_consensus.providers.*'),
        ],
        [
            'route' => 'ai_consensus.logs.index',
            'label' => 'Logs',
            'icon' => 'fa-solid fa-clipboard-list',
            'active' => request()->routeIs('ai_consensus.logs.*'),
        ],
    ];
@endphp

<aside class="ai-side-nav" aria-label="AI Consensus navigation">
    <div class="ai-side-nav__header">
        <span class="ai-side-nav__icon"><i class="fa-solid fa-brain"></i></span>
        <div>
            <strong>AI Consensus</strong>
            <span>Control plane</span>
        </div>
    </div>

    <nav class="ai-side-nav__links">
        @foreach($aiConsensusNavItems as $item)
            <a
                href="{{ route($item['route']) }}"
                class="ai-side-nav__link {{ !empty($item['active']) ? 'is-active' : '' }} {{ ($item['variant'] ?? null) === 'primary' ? 'is-primary' : '' }}"
            >
                <i class="{{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    @isset($providerSettings)
        @if($providerSettings->count())
            <div class="ai-side-nav__section">
                <span class="ai-side-nav__section-title">Providers</span>
                @foreach($providerSettings as $provider)
                    @php
                        $providerKey = $provider->provider ?? $provider->provider_key ?? null;
                        $providerDriver = $provider->driver ?? $providerKey;
                        $providerLabel = $provider->label ?? $provider->name ?? ucfirst((string) $providerKey);
                        $canConfigureCredentials = in_array($providerDriver, ['anthropic', 'gemini', 'openai'], true);
                    @endphp

                    @if($canConfigureCredentials)
                        <button
                            type="button"
                            class="ai-side-nav__provider"
                            data-bs-toggle="modal"
                            data-bs-target="#providerCredentialModal"
                            data-ai-open-provider="{{ $providerDriver }}"
                        >
                            <i class="fa-solid fa-key"></i>
                            <span>{{ $providerLabel }}</span>
                            <em>{{ $provider->is_active ? 'active' : 'off' }}</em>
                        </button>
                    @else
                        <div class="ai-side-nav__provider" aria-disabled="true">
                            <i class="fa-solid fa-microchip"></i>
                            <span>{{ $providerLabel }}</span>
                            <em>{{ $provider->is_active ? 'active' : 'off' }}</em>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    @endisset
</aside>
