<section class="investments-hero">
    <div class="investments-hero__main">
        <div class="investments-hero__icon">
            <i class="{{ $icon ?? 'fa-solid fa-chart-line' }}" aria-hidden="true"></i>
        </div>
        <div>
            <p class="investments-eyebrow">Investments</p>
            <h1 class="investments-title">{{ $title }}</h1>
            @if(!empty($subtitle))
                <p class="investments-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @include('investments::Includes._components.nav')
</section>
