@if(session('success'))
    <div class="investments-alert">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="investments-error">{{ $errors->first() }}</div>
@endif
