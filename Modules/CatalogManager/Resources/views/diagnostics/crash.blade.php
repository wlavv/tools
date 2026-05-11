<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>CatalogManager Crash</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f6f7fb; color:#111827; padding:24px; }
        .box { max-width: 1000px; margin:0 auto; background:#fff; border:1px solid #e5e7eb; border-radius:5px; padding:18px; box-shadow:0 10px 24px rgba(15,23,42,.08); }
        h1 { margin-top:0; color:#b91c1c; }
        pre { white-space:pre-wrap; background:#0f172a; color:#e5e7eb; padding:14px; border-radius:5px; overflow:auto; }
        a { color:#2563eb; font-weight:700; }
    </style>
</head>
<body>
<div class="box">
    <h1>CatalogManager encontrou um erro</h1>
    <p>Foi escrito um log próprio em <strong>storage/logs/catalog-manager.log</strong>.</p>
    <p><a href="{{ $diagnosticsUrl }}">Abrir diagnóstico do módulo</a></p>
    <h3>Erro</h3>
    <pre>{{ get_class($exception) }}: {{ $exception->getMessage() }}
{{ $exception->getFile() }}:{{ $exception->getLine() }}</pre>
</div>
</body>
</html>
