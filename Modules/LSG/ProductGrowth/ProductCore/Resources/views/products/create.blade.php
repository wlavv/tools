@extends('product-core::layouts.module')

@section('module-content')
<section class="product-core-card pc-panel">
    <div class="pc-panel-head">
        <div>
            <h2 class="pc-panel-title">Dados base do produto</h2>
            <p class="pc-panel-subtitle">Primeiro passo do workflow. A ficha pode seguir depois para lojas, conteudo, compliance e sync.</p>
        </div>
    </div>

    <form id="product-core-form" method="POST" action="{{ route('product_growth.product_core.products.store') }}" class="pc-form">
        @csrf
        @include('product-core::products._form')
    </form>
</section>
@endsection
