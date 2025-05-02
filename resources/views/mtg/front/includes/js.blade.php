<script async src="/build/assets/opencv.min.js?t={{rand()}}" onload="openCvReady()"></script>

<script>
    let video = document.getElementById('videoInput');
    let canvas = document.getElementById('canvasOutput');
    let ctx = canvas.getContext('2d');

    let streaming = true;  // Flag para controlar o fluxo da captura
    let src, dst, cap;

    function openCvReady() {
        cv['onRuntimeInitialized'] = () => {
            console.log("OpenCV.js está pronto para uso!");

            // Inicializar a captura de vídeo
            cap = new cv.VideoCapture(video);

            // Inicializar as matrizes para processamento
            src = new cv.Mat(video.height, video.width, cv.CV_8UC4);
            dst = new cv.Mat(video.height, video.width, cv.CV_8UC1);

            // Iniciar o processamento do vídeo
            startProcessing();
        };
    }

    function startProcessing() {
        console.log("Iniciando captura da webcam...");

        // Definindo FPS (frames por segundo)
        const FPS = 30;

        function processVideo() {
            try {
                if (!streaming) {
                    // Limpar e parar
                    src.delete();
                    dst.delete();
                    return;
                }

                let begin = Date.now();

                // Captura o próximo frame
                cap.read(src);

                // Converte o frame de RGBA para escala de cinza
                cv.cvtColor(src, dst, cv.COLOR_RGBA2GRAY);

                // Exibe a imagem convertida no canvas
                cv.imshow('canvasOutput', dst);

                // Calcula o delay necessário para manter o FPS
                let delay = 1000 / FPS - (Date.now() - begin);
                setTimeout(processVideo, delay);

            } catch (err) {
                console.error("Erro ao processar vídeo: ", err);
            }
        }

        // Iniciar o loop de processamento
        setTimeout(processVideo, 0);
    }

    // Acessar a webcam e iniciar o vídeo
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function (stream) {
            video.srcObject = stream;
            video.onloadedmetadata = function () {
                console.log("Stream da webcam obtido com sucesso.");
            };
        })
        .catch(function (err) {
            console.error("Erro ao acessar a webcam: ", err);
        });


        
    function updateTracking() {
        if (!videoStreamInitialized) {
            console.error("A captura de vídeo não foi iniciada.");
            return;  // Não faz nada se o vídeo não estiver inicializado
        }

        // Criar a matriz Mat corretamente
        let frame = new cv.Mat(video.height, video.width, cv.CV_8UC4); // Se não houver imagem inicial
        
        console.log("Capturando o frame da webcam...");

        // Captura o próximo frame da webcam usando cv.imread
        cv.imread(video, frame);  // Captura o frame do vídeo HTML e armazena em 'frame'

        // Verifica se o frame está vazio
        if (frame.empty()) {
            console.error("Falha ao capturar frame");
            frame.delete();  // Libera a memória do frame
            return;
        }

        // Atualiza o tracker com o novo frame
        let ok = tracker.update(frame, bbox);  

        if (ok) {
            console.log("Tracking atualizado com sucesso");
            // Atualiza a posição e as dimensões nos inputs
            document.getElementById("posX").value = bbox.x;
            document.getElementById("posY").value = bbox.y;
            document.getElementById("width").value = bbox.width;
            document.getElementById("height").value = bbox.height;
        } else {
            console.error("Falha ao atualizar o tracker. O objeto não foi encontrado.");
        }

        // Libera a memória do frame após o uso
        frame.delete();
    }

</script>