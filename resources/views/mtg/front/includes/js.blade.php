<script src=" https://cdn.jsdelivr.net/npm/@techstark/opencv-js@4.10.0-release.1/dist/opencv.min.js "></script>
<script>
    
    let video = document.getElementById('video');
let tracker = null;
let initialized = false;
let roi = null;  // Região de Interesse (onde o tracker começa)

// Função para iniciar a captura da webcam
function startTracking() {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
            document.getElementById('startTracking').disabled = true;
            initTracking();
        })
        .catch(err => {
            console.log("Erro ao acessar a câmera: ", err);
        });
}

// Função para inicializar o tracking
function initTracking() {
    video.addEventListener('play', () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        // Espere até que a OpenCV esteja completamente carregada
        cv.onRuntimeInitialized = () => {
            tracker = new cv.TrackerKCF();  // Criar o tracker KCF

            // Chamar função de rastreamento
            setInterval(() => {
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                let frame = ctx.getImageData(0, 0, canvas.width, canvas.height);
                let src = cv.matFromImageData(frame);

                if (!initialized) {
                    // Detectar região de interesse (ROI) com clique no vídeo
                    if (roi == null) {
                        roi = new cv.Rect(100, 100, 100, 100);  // Exemplo de área de rastreamento
                        tracker.init(src, roi);
                        initialized = true;
                    }
                } else {
                    // Atualizar o tracker a cada quadro
                    let ok = tracker.update(src, roi);
                    if (ok) {
                        // Atualizar a posição e dimensões
                        document.getElementById('posX').value = roi.x;
                        document.getElementById('posY').value = roi.y;
                        document.getElementById('width').value = roi.width;
                        document.getElementById('height').value = roi.height;
                    }
                }
                src.delete();
            }, 1000 / 30);  // Atualiza a cada 33ms (~30fps)
        };
    });
}


</script>