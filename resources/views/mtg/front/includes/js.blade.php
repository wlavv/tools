
<script>
//     template = loadImage('/images/mtg/templates/land artefact.png');  // Substitua pelo caminho correto do template

let video;
let canvasOverlay;
let templateImage;  // A carta template que será usada para comparação
let isCapturing = true;
const cropWidth = 1200;
const cropHeight = 900;

// Template Matching
function matchTemplate(videoFrame, template) {
  const videoCanvas = document.createElement('canvas');
  const videoContext = videoCanvas.getContext('2d');
  videoCanvas.width = videoFrame.width;
  videoCanvas.height = videoFrame.height;
  videoContext.drawImage(videoFrame, 0, 0);

  const videoData = videoContext.getImageData(0, 0, videoFrame.width, videoFrame.height);
  const templateData = template.getImageData(0, 0, template.width, template.height);

  let bestMatch = { x: 0, y: 0, score: -Infinity };  // Guardar a melhor correspondência

  // Slide do template sobre a imagem
  for (let y = 0; y < videoFrame.height - template.height; y++) {
    for (let x = 0; x < videoFrame.width - template.width; x++) {
      let score = 0;

      // Comparar o bloco da imagem com o template
      for (let ty = 0; ty < template.height; ty++) {
        for (let tx = 0; tx < template.width; tx++) {
          const idxFrame = ((y + ty) * videoFrame.width + (x + tx)) * 4;
          const idxTemplate = (ty * template.width + tx) * 4;
          
          // Somar diferenças entre os pixels
          const rDiff = Math.abs(videoData.data[idxFrame] - templateData.data[idxTemplate]);
          const gDiff = Math.abs(videoData.data[idxFrame + 1] - templateData.data[idxTemplate + 1]);
          const bDiff = Math.abs(videoData.data[idxFrame + 2] - templateData.data[idxTemplate + 2]);

          score -= (rDiff + gDiff + bDiff); // Maior valor é a melhor correspondência
        }
      }

      if (score > bestMatch.score) {
        bestMatch = { x, y, score };
      }
    }
  }

  return bestMatch;
}

// Função para configurar o vídeo
function setup() {
  const canvas = createCanvas(cropWidth, cropHeight); // Usar a função do p5.js
  canvasOverlay = canvas.elt;
  canvasOverlay.style.position = 'absolute';
  canvasOverlay.style.top = '0';
  canvasOverlay.style.left = '0';
  canvasOverlay.style.zIndex = '10';

  // Carregar o template (a carta modelo)
  templateImage = new Image();
  templateImage.src = '/images/mtg/templates/land artefact.png'; // Caminho da imagem template
  templateImage.onload = function () {
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
  };
}

// Função para processar cada frame e detectar a carta
function draw() {
  clear(); // Limpa o canvas

  if (!isCapturing) return;

  let img = video.get();
  let match = matchTemplate(img.canvas, templateImage);

  // Desenha o retângulo ao redor do template encontrado
  if (match.score > -Infinity) {
    const { x, y } = match;
    stroke(0, 255, 0);
    noFill();
    rect(x, y, templateImage.width, templateImage.height); // Desenha o contorno da carta

    // Exibir cropped image
    let croppedImage = img.get(x, y, templateImage.width, templateImage.height);
    croppedImage.loadPixels();
    let croppedBase64 = croppedImage.canvas.toDataURL('image/jpeg');
    document.getElementById('croppedImage').innerHTML = `<img src="${croppedBase64}" />`;
  }
}

</script>
