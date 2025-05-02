<script src="/build/assets/opencv.min.js"></script>
<script>
    
    let video = document.getElementById('video');
    let initialized = false;
    let roi = null;  // Região de Interesse (onde o tracker começa)

    let cap;
    let tracker;
    let bbox;
    let videoStreamInitialized = false;  // Flag para verificar se a captura foi inicializada

    startTracking();

function startTracking() {
    console.log("Iniciando captura da webcam...");

    const video = document.getElementById("video");

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

                    // Inicializa a captura de vídeo com OpenCV.js usando o elemento <video>
                    cap = new cv.VideoCapture(video);
                    console.log("Webcam acessada com sucesso!");

                    // Inicializar o tracker corretamente com a função create
                    tracker = new cv.TrackerKCF();  // Usando a função correta para o tracker KCF
                    console.log("Inicializando tracking...");

                    // Definir o bounding box manualmente ou a partir de uma interface
                    bbox = new cv.Rect(100, 100, 100, 100);  // Exemplo de bbox inicial
                    tracker.init(cap.read(), bbox);  // Inicializa o tracker com o primeiro frame

                    // Marcar que a webcam foi inicializada com sucesso
                    videoStreamInitialized = true;
                    console.log("A captura de vídeo foi inicializada com sucesso.");

                    // Iniciar o loop de captura e tracking
                    if (videoStreamInitialized) {
                        console.log("Iniciando o loop de tracking.");
                        setInterval(updateTracking, 1000 / 30);  // Atualiza a cada 33ms (aproximadamente 30fps)
                    }
                };
            })
            .catch(function (err) {
                console.error("Erro ao acessar a webcam: ", err);
            });
    } else {
        console.log("A webcam já está acessada.");
    }
}

    function onOpenCVLoaded() { console.log("OpenCV.js carregado com sucesso!"); }
        if (typeof cv === 'undefined') console.log("cv não está definido ainda...");
        else console.log("cv está definido.");

        window.cv = window.cv || {};  // Garantir que o cv esteja disponível
        window.cv.onRuntimeInitialized = onOpenCVLoaded;

        function updateTracking() {
    if (!videoStreamInitialized) {
        console.error("Cap não está inicializada. A captura de vídeo não foi iniciada.");
        return;  // Não faz nada se cap não estiver inicializado
    }

    // Criar a matriz Mat corretamente usando cv.Mat.zeros() ou cv.Mat()
    let frame = new cv.Mat(video.height, video.width, cv.CV_8UC4);
    console.log("Capturando o frame da webcam...");

    // Captura o próximo frame da webcam
    cap.read(frame);

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

    setInterval(updateTracking, 1000 / 30);

    // Função para inicializar o tracking
    function initTracking() {
        console.log("Inicializando tracking...");
        
        video.addEventListener('play', () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            console.log("Play...");

            // Espere até que a OpenCV esteja completamente carregada
            cv.onRuntimeInitialized = () => {
                console.log("OpenCV.js carregado com sucesso!");
                
                tracker = new cv.TrackerKCF();  // Criar o tracker KCF
                console.log("Tracker KCF inicializado!");

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
                            console.log("Tracking iniciado com a ROI:", roi);
                        }
                    } else {
                        // Atualizar o tracker a cada quadro
                        let ok = tracker.update(src, roi);
                        if (ok) {
                            // Atualizar a posição e dimensões
                            console.log("Tracking bem-sucedido!");
                            console.log("Posição X:", roi.x, "Posição Y:", roi.y, "Largura:", roi.width, "Altura:", roi.height);

                            document.getElementById('posX').value = roi.x;
                            document.getElementById('posY').value = roi.y;
                            document.getElementById('width').value = roi.width;
                            document.getElementById('height').value = roi.height;
                        } else {
                            console.log("Falha no tracking...");
                        }
                    }
                    src.delete();
                }, 1000 / 30);  // Atualiza a cada 33ms (~30fps)
            };
        });
    }


</script>