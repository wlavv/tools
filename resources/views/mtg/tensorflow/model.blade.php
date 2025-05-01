<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Treinamento de Modelo</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Envia o pedido para o backend para iniciar o treinamento
            $('#startTraining').on('click', function() {
                $.ajax({
                    url: '/mtg/tensorflow/create_model',  // A URL que você criou para o backend
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')  // CSRF token
                    },
                    success: function(response) {
                        alert('Modelo treinado com sucesso!');
                        console.log(response);
                    },
                    error: function(err) {
                        alert('Erro ao treinar o modelo.');
                        console.log(err);
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div>
        <h1>Treinar Modelo de Detecção de Cartas</h1>
        <button id="startTraining">Iniciar Treinamento</button>
    </div>
</body>
</html>