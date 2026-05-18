@if(session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $errors->first() }}</div>
@endif
