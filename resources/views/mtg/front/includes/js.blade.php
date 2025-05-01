<script>
let video;
let isCapturing = true;
let orb;
let keypoints;
let descriptors;

const cropWidth = 640;
const cropHeight = 480;

// Base de dados de descritores (exemplo de array)
const descriptorDatabase = [];  // Aqui você deve armazenar os descritores das cartas previamente processadas

window.setup = function () {
    const canvas = createCanvas(cropWidth, cropHeight);
    video = createCapture(VIDEO, () => {
        const videoContainer = document.getElementById('videoContainer');
        videoContainer.style.position = 'relative';
        video.elt.style.position = 'absolute';
        video.elt.style.top = '0';
        video.elt.style.left = '0';
        video.elt.width = cropWidth;
        video.elt.height = cropHeight;
        videoContainer.appendChild(video.elt);
    });

    video.size(cropWidth, cropHeight);

    // Inicializa o ORB
    orb = new cv.ORB();
};

window.draw = function () {
    clear();
    if (!isCapturing) return;

    let img = video.get();
    let mat;
    try {
        mat = cv.imread(img.canvas);
    } catch (err) {
        console.error('Erro ao ler a imagem: ', err);
        return;
    }

    let gray;
    try {
        gray = new cv.Mat();
        cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);
    } catch (err) {
        console.error('Erro ao converter para escala de cinza: ', err);
        return;
    }

    let keypoints;
    let descriptors;
    try {
        keypoints = new cv.KeyPointVector();
        descriptors = new cv.Mat();
        orb.detectAndCompute(gray, new cv.Mat(), keypoints, descriptors);
    } catch (err) {
        console.error('Erro ao detectar e computar ORB: ', err);
        return;
    }

    // Verificar se há pontos chave válidos antes de tentar desenhá-los
    if (keypoints.size() > 0) {
        let imgKeypoints;
        try {
            imgKeypoints = new cv.Mat();
            cv.drawKeypoints(mat, keypoints, imgKeypoints, [0, 255, 0, 255], cv.DrawMatchesFlags_DRAW_RICH_KEYPOINTS);
            cv.imshow('canvasOverlay', imgKeypoints);
        } catch (err) {
            console.error('Erro ao desenhar pontos chave: ', err);
        }
    } else {
        console.log('Nenhum ponto chave encontrado');
    }

    // Limpeza de memória
    mat.delete();
    gray.delete();
    descriptors.delete();
};



// Comparar descritores extraídos
function compareDescriptors(descriptors) {
    let bestMatch = null;
    let minDistance = Number.MAX_VALUE;

    for (let i = 0; i < descriptorDatabase.length; i++) {
        let dbDescriptor = descriptorDatabase[i];
        let distance = cv.norm(descriptors, dbDescriptor, cv.NORM_L2);

        if (distance < minDistance) {
            minDistance = distance;
            bestMatch = dbDescriptor;  // Retorna o melhor match
        }
    }
    return bestMatch;
}
</script>
