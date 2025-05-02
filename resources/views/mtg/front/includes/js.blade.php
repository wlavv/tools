<script src="/build/assets/opencv.min.js" onload="openCvReady()"></script>

<script>
    let video = document.getElementById('video');
    let initialized = false;
    let roi = null;  // Região de Interesse (onde o tracker começa)
    let tracker;
    let bbox;
    let videoStreamInitialized = false;

    // Função chamada após o OpenCV.js ser carregado
    function openCvReady() {
        if (cv && cv['onRuntimeInitialized']) {
            cv['onRuntimeInitialized'] = () => {
                console.log("OpenCV.js está pronto para uso!");
                startTracking();
            };
        } else {
            console.error("Falha ao inicializar o OpenCV.js");
        }
    }

    function startTracking() {
        console.log("Iniciando captura da webcam...");

        // Se o stream da webcam estiver vazio, inicia a captura
        if (!video.srcObject) {
            console.log("Acessando a webcam...");
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    console.log("Stream da webcam obtido com sucesso.");
                    video.srcObject = stream;

                    // Verifique se a webcam foi corretamente associada ao vídeo
                    video.onloadedmetadata = function () {
                        console.log("O vídeo carregou com sucesso!");

                        // Marcar que a webcam foi inicializada com sucesso
                        videoStreamInitialized = true;
                        console.log("A captura de vídeo foi inicializada com sucesso.");

                        // Inicializar o tracker com a função create
                        tracker = cv.TrackerKCF.create();  // Criar o tracker KCF

                        console.log("Inicializando tracking...");

                        // Definir o bounding box manualmente ou a partir de uma interface
                        bbox = new cv.Rect(100, 100, 100, 100);  // Exemplo de bbox inicial
                        tracker.init(video, bbox);  // Inicializa o tracker com o vídeo

                        // Iniciar o loop de captura e tracking
                        setInterval(updateTracking, 1000 / 30);  // Atualiza a cada 33ms (aproximadamente 30fps)
                    };
                })
                .catch(function (err) {
                    console.error("Erro ao acessar a webcam: ", err);
                });
        } else {
            console.log("A webcam já está acessada.");
        }
    }

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