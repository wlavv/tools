<div class="pm-card pm-filters">
    <div class="row g-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Filtro visual (placeholder)">
        </div>
        <div class="col-md-4">
            <select class="form-control">
                <option>Todos os estados</option>
                @foreach($statuses as $status)
                    <option>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Equipa / contacto / domínio">
        </div>
    </div>
</div>
