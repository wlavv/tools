<script src=" https://cdn.jsdelivr.net/npm/@techstark/opencv-js@4.10.0-release.1/dist/opencv.min.js "></script>
<script>
    // Acessar a webcam do utilizador
    const video = document.getElementById('video');
    let tracker = null;

    // Função para iniciar a captura da webcam
    function startTracking() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
                document.getElementById('startTracking').disabled = true; // Desabilitar o botão após começar
                initTracking();
            })
            .catch(err => {
                console.log("Erro ao acessar a câmera: ", err);
            });
    }

    // Função para inicializar o tracking (usando uma simples detecção de cor ou qualquer método)
    function initTracking() {
        tracker = new cv.TrackerKCF();  // Escolher um tracker do OpenCV.js (supondo que OpenCV.js está carregado)

        video.addEventListener('play', () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            setInterval(() => {
                // Desenhar o vídeo no canvas
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Detectar o objeto no vídeo (aqui pode ser uma detecção simples de cor ou outro método)
                const frame = ctx.getImageData(0, 0, canvas.width, canvas.height);

                // Supondo que estamos detectando um objeto baseado em cor
                let posX = 0, posY = 0, width = 0, height = 0;

                // Detecção fictícia de objeto: colocar valores fictícios para a posição e tamanho
                // (Aqui, pode-se substituir isso por um algoritmo real de tracking como detecção de objetos ou características)
                posX = Math.random() * canvas.width;
                posY = Math.random() * canvas.height;
                width = 50 + Math.random() * 100;
                height = 50 + Math.random() * 100;

                // Atualizar os inputs com a posição e dimensões do objeto
                document.getElementById('posX').value = posX.toFixed(2);
                document.getElementById('posY').value = posY.toFixed(2);
                document.getElementById('width').value = width.toFixed(2);
                document.getElementById('height').value = height.toFixed(2);
            }, 1000 / 30);  // Atualiza a cada 33ms (~30fps)
        });
    }
</script>