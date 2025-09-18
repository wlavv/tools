<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <title>Detetor de Carta MTG com OpenCV.js e ORB + BFMatcher</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #222;
      color: white;
      margin: 0;
      padding: 20px;
      text-align: center;
    }
    #main-container {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
    }
    #video-container, #url-container {
      background: #333;
      padding: 15px;
      border-radius: 8px;
      width: 640px; /* Aumentado para canvas maior */
      max-width: 100%;
    }
    video, canvas {
      max-width: 100%;
      border: 1px solid #ccc;
      background: black;
      display: block;
      margin: 0 auto 10px;
    }
    input[type="text"] {
      width: 100%;
      padding: 8px;
      border-radius: 4px;
      border: none;
      margin-bottom: 10px;
      font-size: 1em;
    }
    button {
      padding: 8px 16px;
      font-size: 1em;
      border: none;
      border-radius: 4px;
      background: #0a7;
      color: white;
      cursor: pointer;
    }
    button:disabled {
      background: #555;
      cursor: not-allowed;
    }
    .hash-output {
      font-family: monospace;
      word-break: break-word;
      margin-top: 10px;
      color: #0f0;
    }
    #status, #comparison {
      margin-top: 10px;
      font-weight: bold;
    }
    #comparison {
      font-size: 1.1em;
    }
  </style>
</head>
<body>
  <h1>Detetor de Carta MTG + ORB + BFMatcher (Grayscale)</h1>
  <div id="main-container">

    <!-- Câmara -->
    <div id="video-container">
      <h3>Detetor pela câmara</h3>
      <video id="video" width="640" height="480" autoplay muted playsinline></video>
      <canvas id="canvas" width="640" height="480" style="position: relative;"></canvas>
      <div id="status">A iniciar...</div>
      <div><strong>Matches bons:</strong></div>
      <div id="matches-count" class="hash-output">-</div>
    </div>

    <!-- URL da Imagem -->
    <div id="url-container">
      <h3>Carregar imagem via URL</h3>
      <input type="text" id="image-url" placeholder="Coloca aqui o URL da imagem" />
      <button id="btn-load-image">Carregar Imagem</button>
      <canvas id="url-canvas" width="280" height="392" style="border:2px solid #0a7; margin-top: 10px;"></canvas>
    </div>

  </div>

  <div id="comparison">Aguardando dados para comparação...</div>

  <script async src="https://docs.opencv.org/4.x/opencv.js" onload="onOpenCvReady();" ></script>

  <script>
    // --- Declaração global das variáveis ---
    let video = null;
    let canvas = null;
    let ctx = null;
    let streaming = false;
    let srcMat = null;
    let grayMat = null;
    let refImageMat = null;
    let refImageGray = null;
    let orbDetector = null;
    let bfMatcher = null;
    let refKeypoints = null;
    let refDescriptors = null;
    let refImageLoaded = false;
    let detectionHold = false;

    let frameCounter = 0;
    const frameStep = 3; // Processar 1 frame a cada 3

    const matchesCountElem = document.getElementById('matches-count');
    const statusElem = document.getElementById('status');
    const comparisonElem = document.getElementById('comparison');

    // Criar kernel para filtro nitidez só 1 vez
    let kernel = null;

    // --- Função chamada quando o OpenCV.js termina de carregar ---
    function onOpenCvReady() {
      cv['onRuntimeInitialized'] = () => {
        console.log("OpenCV.js carregado e pronto");
        statusElem.innerText = "OpenCV.js pronto.";
        startApp();
      };
    }

    function startApp() {
      video = document.getElementById('video');
      canvas = document.getElementById('canvas');
      ctx = canvas.getContext('2d');

      orbDetector = new cv.ORB(1500); // Aumentado para 1500 pontos
      bfMatcher = new cv.BFMatcher();

      // Kernel para nitidez
      kernel = cv.Mat.ones(3, 3, cv.CV_32F);
      kernel.data32F.set([
        0, -1, 0,
        -1, 5, -1,
        0, -1, 0
      ]);

      startCamera();

      const loadImageBtn = document.getElementById('btn-load-image');
      loadImageBtn.addEventListener('click', () => {
        const url = document.getElementById('image-url').value.trim();
        if (url) loadReferenceImage(url);
      });
    }

    function startCamera() {
      navigator.mediaDevices.getUserMedia({
        video: {
          width: { ideal: 1280 },
          height: { ideal: 720 },
          facingMode: { ideal: "environment" },
          focusMode: "continuous" // Nem todos suportam
        },
        audio: false
      })
      .then((stream) => {
        video.srcObject = stream;
        video.play();
      })
      .catch((err) => {
        console.error("Erro ao aceder à câmara: ", err);
        statusElem.innerText = "Erro ao aceder à câmara.";
      });

      video.addEventListener('canplay', () => {
        if (!streaming) {
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          streaming = true;

          srcMat = new cv.Mat(video.videoHeight, video.videoWidth, cv.CV_8UC4);
          grayMat = new cv.Mat(video.videoHeight, video.videoWidth, cv.CV_8UC1);

          // Esperar 2 segundos para autofocus estabilizar
          setTimeout(() => {
            requestAnimationFrame(processVideo);
          }, 2000);
        }
      });
    }

    function loadReferenceImage(url) {
      let img = new Image();
      img.crossOrigin = "anonymous";
      img.onload = () => {
        let urlCanvas = document.getElementById('url-canvas');
        let urlCtx = urlCanvas.getContext('2d');
        urlCanvas.width = img.width;
        urlCanvas.height = img.height;
        urlCtx.clearRect(0, 0, urlCanvas.width, urlCanvas.height);
        urlCtx.drawImage(img, 0, 0);

        if (refImageMat) refImageMat.delete();
        if (refImageGray) refImageGray.delete();
        if (refKeypoints) refKeypoints.delete();
        if (refDescriptors) refDescriptors.delete();

        refImageMat = cv.imread(urlCanvas);
        refImageGray = new cv.Mat();
        cv.cvtColor(refImageMat, refImageGray, cv.COLOR_RGBA2GRAY);

        refKeypoints = new cv.KeyPointVector();
        refDescriptors = new cv.Mat();

        orbDetector.detectAndCompute(refImageGray, new cv.Mat(), refKeypoints, refDescriptors);

        refImageLoaded = true;
        statusElem.innerText = "Imagem de referência carregada com sucesso!";

      };
      img.onerror = () => {
        alert("Erro ao carregar a imagem. Verifica a URL.");
      };
      img.src = url;
    }

    function processVideo() {
      if (!streaming) {
        cleanup();
        return;
      }

      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      let imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      srcMat.data.set(imageData.data);
      cv.cvtColor(srcMat, grayMat, cv.COLOR_RGBA2GRAY);

      frameCounter++;
      if (frameCounter % frameStep === 0 && refImageLoaded && !detectionHold) {
        let frameKeypoints = new cv.KeyPointVector();
        let frameDescriptors = new cv.Mat();


        // Aplicar filtro de nitidez
        let sharpenedMat = new cv.Mat();
        cv.filter2D(grayMat, sharpenedMat, cv.CV_8U, kernel);

        orbDetector.detectAndCompute(sharpenedMat, new cv.Mat(), frameKeypoints, frameDescriptors);

        let matches = new cv.DMatchVectorVector();
        bfMatcher.knnMatch(refDescriptors, frameDescriptors, matches, 2);

        let goodMatches = [];
        for (let i = 0; i < matches.size(); i++) {
          let m = matches.get(i).get(0);
          let n = matches.get(i).get(1);
          if (m.distance < 0.82 * n.distance) { // Limiar aumentado para 0.8
            goodMatches.push(m);
          }
        }

        matchesCountElem.innerText = goodMatches.length;

        // Limiar para considerar que imagem está reconhecida
        if (goodMatches.length >= 70 && !detectionHold) { 
          detectionHold = true;
          comparisonElem.innerText = "✅ Carta detectada!";
          statusElem.innerText = "Detecção confirmada, pausa por 2 segundos.";
          setTimeout(() => {
            detectionHold = false;
            comparisonElem.innerText = "Aguardando próxima detecção...";
            statusElem.innerText = "";
          }, 2000);
        } else {
          comparisonElem.innerText = "❌ Carta não detectada.";
        }

        // Limpar memória
        frameKeypoints.delete();
        frameDescriptors.delete();
        matches.delete();
        sharpenedMat.delete();

      } else {
        if (!detectionHold) comparisonElem.innerText = "⏳ A aguardar próximo frame para processar...";
      }

      requestAnimationFrame(processVideo);
    }

    function cleanup() {
      if (srcMat) { srcMat.delete(); srcMat = null; }
      if (grayMat) { grayMat.delete(); grayMat = null; }
      if (refImageMat) { refImageMat.delete(); refImageMat = null; }
      if (refImageGray) { refImageGray.delete(); refImageGray = null; }
      if (refKeypoints) { refKeypoints.delete(); refKeypoints = null; }
      if (refDescriptors) { refDescriptors.delete(); refDescriptors = null; }
      if (orbDetector) { orbDetector.delete(); orbDetector = null; }
      if (bfMatcher) { bfMatcher.delete(); bfMatcher = null; }
      if (kernel) { kernel.delete(); kernel = null; }
      streaming = false;
    }
  </script>
</body>
</html>
