<script>

let video;
let canvasOverlay;
let isCapturing = true;

const cropWidth = 1200;
const cropHeight = 900;

let orb, keypoints, descriptors;
let isTracking = false;
let prevKeypoints, prevDescriptors;

// Configurar o vídeo
window.setup = function () {
    const canvas = createCanvas(cropWidth, cropHeight);
    canvasOverlay = canvas.elt;
    canvasOverlay.style.position = 'absolute';
    canvasOverlay.style.top = '0';
    canvasOverlay.style.left = '0';
    canvasOverlay.style.zIndex = '10';

    video = createCapture(VIDEO, () => {
        const videoContainer = document.getElementById('videoContainer');
        videoContainer.style.position = 'relative';
        video.elt.style.position = 'absolute';
        video.elt.style.top = '0';
        video.elt.style.left = '0';
        video.elt.width = cropWidth;
        video.elt.height = cropHeight;
        videoContainer.appendChild(video.elt);
        videoContainer.appendChild(canvasOverlay);
    });

    video.size(cropWidth, cropHeight);
    orb = new cv.ORB(); // Inicializa o ORB
};

// Função para processar cada frame e rastrear o objeto
window.draw = function () {
    clear();  // Limpa o canvas

    if (!isCapturing) return;

    let img = video.get();
    let canvas = document.createElement('canvas');
    canvas.width = cropWidth;
    canvas.height = cropHeight;
    let ctx = canvas.getContext('2d');
    ctx.drawImage(img.canvas, 0, 0, cropWidth, cropHeight);  // Desenha no canvas temporário
    let mat = cv.imread(canvas);  // Agora usa esse canvas com OpenCV

    let gray = new cv.Mat();
    cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);  // Converte a imagem para escala de cinza

    // Detecta keypoints e descritores usando ORB
    keypoints = new cv.KeyPointVector();
    descriptors = new cv.Mat();
    orb.detectAndCompute(gray, new cv.Mat(), keypoints, descriptors);

    // Se já temos keypoints da imagem anterior, realizamos o matching
    if (prevKeypoints && prevDescriptors) {
        let matches = new cv.DMatchVector();
        let bf = new cv.BFMatcher(cv.NORM_HAMMING, true);
        bf.match(prevDescriptors, descriptors, matches);  // Faz o match entre os descritores

        // Desenha os matches
        for (let i = 0; i < matches.size(); i++) {
            let match = matches.get(i);
            let pt1 = prevKeypoints.get(match.queryIdx).pt;
            let pt2 = keypoints.get(match.trainIdx).pt;
            cv.line(mat, pt1, pt2, [0, 255, 0, 255], 2); // Desenha uma linha verde conectando os pontos correspondentes
        }

        // Atualiza os keypoints e descritores anteriores
        prevKeypoints = keypoints;
        prevDescriptors = descriptors;
    } else {
        // Caso contrário, apenas armazene os keypoints e descritores para a próxima iteração
        prevKeypoints = keypoints;
        prevDescriptors = descriptors;
    }

    // Exibe o canvas no overlay
    cv.imshow(canvasOverlay, mat);

    // Libere os recursos do OpenCV
    mat.delete();
    gray.delete();
};


</script>
