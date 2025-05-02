<script>
        // Acessar a webcam do utilizador
        const video = document.getElementById('video');

        // Função para iniciar a captura da webcam
        function startTracking() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                    document.getElementById('startTracking').disabled = true; // Desabilitar o botão após começar
                })
                .catch(err => {
                    console.log("Erro ao acessar a câmera: ", err);
                });
        }

        // Função para capturar e enviar imagens para o backend
        function captureAndSend() {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Converte a imagem para base64
            const imgData = canvas.toDataURL('image/png');

            // Envia a imagem via AJAX para o Laravel
            fetch('/process-image', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', // Token CSRF do Laravel
                },
                body: JSON.stringify({ image: imgData })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Resultado do servidor:', data);
            })
            .catch(error => console.error('Erro ao enviar a imagem:', error));
        }

        // Chama a função de captura em intervalos regulares (a cada 1 segundo)
        setInterval(captureAndSend, 1000);  // Envia uma imagem a cada segundo
    </script>