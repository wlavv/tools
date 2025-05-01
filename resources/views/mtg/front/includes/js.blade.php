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
    let mat = cv.imread(img.canvas);
    let gray = new cv.Mat();
    cv.cvtColor(mat, gray, cv.COLOR_RGBA2GRAY);

    // Detectar pontos-chave e descritores com ORB
    keypoints = new cv.KeyPointVector();
    descriptors = new cv.Mat();
    orb.detectAndCompute(gray, new cv.Mat(), keypoints, descriptors);

    // Exibir pontos chave na imagem
    let imgKeypoints = new cv.Mat();
    cv.drawKeypoints(mat, keypoints, imgKeypoints, [0, 255, 0, 255], cv.DrawMatchesFlags_DRAW_RICH_KEYPOINTS);

    // Comparar descritores com a base de dados
    let bestMatch = compareDescriptors(descriptors);

    if (bestMatch) {
        console.log('Carta encontrada:', bestMatch);
        // Enviar a imagem para o servidor ou mostrar informações
        $.ajax({
            url: "{{ route('mtg.processImage') }}",
            type: 'POST',
            data: JSON.stringify({
                image: img.canvas.toDataURL('image/jpeg'),
                cardId: bestMatch.id
            }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#info').html("Informações: " + response.info);
            },
            error: function () {
                $('#info').html("❌ Erro ao enviar imagem");
            }
        });
    }

    // Exibir a imagem com pontos chave
    cv.imshow('canvasOverlay', imgKeypoints);

    // Limpeza de memória
    mat.delete();
    gray.delete();
    descriptors.delete();
    imgKeypoints.delete();
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
