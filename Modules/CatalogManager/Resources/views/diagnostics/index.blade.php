<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>CatalogManager Diagnostics</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            font-family: Arial, sans-serif;
            background: #f6f7fb;
            color: #111827;
        }
        .wrap {
            max-width: 1180px;
            margin: 0 auto;
        }
        .hero, .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            box-shadow: 0 10px 24px rgba(15,23,42,.08);
            padding: 18px;
            margin-bottom: 16px;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }
        h2 {
            margin: 0 0 12px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #eef2f7;
            font-size: 13px;
        }
        .ok {
            color: #15803d;
            font-weight: 700;
        }
        .bad {
            color: #dc2626;
            font-weight: 700;
        }
        pre {
            white-space: pre-wrap;
            background: #0f172a;
            color: #e5e7eb;
            padding: 14px;
            border-radius: 5px;
            overflow: auto;
            max-height: 420px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media(max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <h1>CatalogManager Diagnostics</h1>
        <div>Module version: <strong>{{ $moduleVersion }}</strong></div>
        <div>Laravel: <strong>{{ $laravelVersion }}</strong> · PHP: <strong>{{ $phpVersion }}</strong></div>
        <div>Log próprio: <strong>{{ $logPath }}</strong></div>
    </div>

    <div class="grid">
        <div class="card">
            <h2>Tabelas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tabela</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableStatus as $table => $exists)
                        <tr>
                            <td>{{ $table }}</td>
                            <td class="{{ $exists ? 'ok' : 'bad' }}">
                                {{ $exists ? 'OK' : 'EM FALTA' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Rotas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rota</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routeStatus as $route => $exists)
                        <tr>
                            <td>{{ $route }}</td>
                            <td class="{{ $exists ? 'ok' : 'bad' }}">
                                {{ $exists ? 'OK' : 'EM FALTA' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Últimas linhas do log próprio</h2>
        <pre>{{ $logTail }}</pre>
    </div>
</div>
</body>
</html>
