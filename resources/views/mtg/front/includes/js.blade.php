<script src="/build/assets/opencv.min.js"></script>
<script>
    
    let video = document.getElementById('video');
    let cap;
    let tracker;
    let bbox;
    let initialized = false;
    let roi = null;  // Região de Interesse (onde o tracker começa)

    // Função para iniciar a captura da webcam
    function startTracking() {
        console.log("Iniciando captura da webcam...");

        // Acessar a webcam
        const video = document.getElementById("video");
        const stream = video.srcObject;

        // Se o stream da webcam estiver vazio, inicia a captura
        if (!stream) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    video.srcObject = stream;

                    // Criar o VideoCapture para acessar o vídeo da webcam
                    cap = new cv.VideoCapture(video);
                    console.log("Webcam acessada com sucesso!");

                    // Inicializar o tracker (usando KCF ou outro)
                    tracker = new cv.TrackerKCF();
                    console.log("Inicializando tracking...");

                    // Definir o bounding box manualmente ou a partir de uma interface
                    bbox = new cv.Rect(100, 100, 100, 100);  // Exemplo de bbox inicial
                    tracker.init(cap.read(), bbox);  // Inicializa o tracker com o primeiro frame

                    // Iniciar o loop de captura e tracking
                    setInterval(updateTracking, 1000 / 30);  // Atualiza a cada 33ms (aproximadamente 30fps)
                })
                .catch(function (err) {
                    console.error("Erro ao acessar a webcam: ", err);
                });
        }
    }


    // Definir a função que será chamada quando o OpenCV estiver pronto
    function onOpenCVLoaded() {
        console.log("OpenCV.js carregado com sucesso!");
        // Qualquer código adicional que dependa do OpenCV.js
    }

    // Registar o evento onRuntimeInitialized antes de carregar o OpenCV.js
    if (typeof cv === 'undefined') {
        console.log("cv não está definido ainda...");
    } else {
        console.log("cv está definido.");
    }
    window.cv = window.cv || {};  // Garantir que o cv esteja disponível
    window.cv.onRuntimeInitialized = onOpenCVLoaded;

    function updateTracking() {
        // Criar a matriz Mat corretamente usando cv.Mat.zeros() ou cv.Mat()
        let frame = new cv.Mat(video.height, video.width, cv.CV_8UC4);  // Ou usar .zeros() se necessário

        cap.read(frame);  // Captura o próximo frame da webcam

        // Verifica se o frame está vazio
        if (frame.empty()) {
            console.error("Falha ao capturar frame");
            frame.delete();  // Libera a memória do frame
            return;
        }

        // Verifica se o bounding box está dentro dos limites da imagem
        if (bbox.x < 0 || bbox.y < 0 || bbox.x + bbox.width > video.width || bbox.y + bbox.height > video.height) {
            console.error("Bounding box está fora dos limites da imagem");
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