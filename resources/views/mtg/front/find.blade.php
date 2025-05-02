<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detecção de Cartas</title>
    <script async src="https://docs.opencv.org/master/opencv.js"></script>
    <style>
        #canvas {
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <h1>Detecção de Cartas de Magic: The Gathering</h1>
    <input type="file" id="imageInput" accept="image/*">
    <canvas id="canvas"></canvas>

    <script>
        // Espera até que o OpenCV.js esteja carregado
        function onOpenCvReady() {
            console.log('OpenCV.js carregado!');
        }

        // Função para converter a imagem em escala de cinza
        function convertToGrayScale(srcMat) {
            let grayMat = new cv.Mat();
            cv.cvtColor(srcMat, grayMat, cv.COLOR_RGBA2GRAY);
            return grayMat;
        }

        // Função para detectar bordas usando Canny
        function detectEdges(grayMat) {
            let edges = new cv.Mat();
            cv.Canny(grayMat, edges, 100, 200);
            return edges;
        }

        // Função para detectar contornos na imagem
        function findContours(edgesMat) {
            let contours = new cv.MatVector();
            let hierarchy = new cv.Mat();
            cv.findContours(edgesMat, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);
            return contours;
        }

        // Função para filtrar contornos com base nas proporções
        function filterCardsByAspectRatio(contours) {
            let validContours = [];
            for (let i = 0; i < contours.size(); i++) {
                let contour = contours.get(i);
                let epsilon = 0.02 * cv.arcLength(contour, true);
                let approx = new cv.Mat();
                cv.approxPolyDP(contour, approx, epsilon, true);

                // Verifica se o contorno tem 4 pontos (como uma carta)
                if (approx.rows === 4) {
                    let rect = cv.boundingRect(approx);
                    let width = rect.width;
                    let height = rect.height;

                    // Verifica se a proporção está próxima de 2.5:3 (proporção típica das cartas)
                    if (width / height >= 0.8 && width / height <= 1.2) {
                        validContours.push(approx);
                    }
                }
            }
            return validContours;
        }

        // Função para aplicar a transformação de perspectiva na carta
        function perspectiveTransform(srcMat, approx) {
            // Pegar os 4 pontos do contorno
            let points = [];
            for (let i = 0; i < approx.rows; i++) {
                points.push([approx.data32S[i * 2], approx.data32S[i * 2 + 1]]);
            }

            // Definir os pontos de destino para a transformação
            let dstPoints = [
                [0, 0],
                [srcMat.cols, 0],
                [srcMat.cols, srcMat.rows],
                [0, srcMat.rows]
            ];

            // Realizar a transformação de perspectiva
            let srcTriangulation = cv.matFromArray(points.length, 1, cv.CV_32FC2, points.flat());
            let dstTriangulation = cv.matFromArray(dstPoints.length, 1, cv.CV_32FC2, dstPoints.flat());
            let M = cv.getPerspectiveTransform(srcTriangulation, dstTriangulation);

            let dstMat = new cv.Mat();
            cv.warpPerspective(srcMat, dstMat, M, new cv.Size(srcMat.cols, srcMat.rows));
            return dstMat;
        }

        // Função principal que executa a detecção
        function detectCards(imageData) {
            let srcMat = cv.matFromImageData(imageData);
            let grayMat = convertToGrayScale(srcMat);
            let edgesMat = detectEdges(grayMat);
            let contours = findContours(edgesMat);
            let validContours = filterCardsByAspectRatio(contours);

            // Desenhando as cartas detectadas no canvas
            let canvas = document.getElementById('canvas');
            let ctx = canvas.getContext('2d');
            ctx.drawImage(document.getElementById('imageInput'), 0, 0);

            validContours.forEach(contour => {
                let rect = cv.boundingRect(contour);
                ctx.beginPath();
                ctx.rect(rect.x, rect.y, rect.width, rect.height);
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#FF0000';
                ctx.stroke();

                // Exibindo as dimensões da carta
                console.log('Carta detectada! Dimensões:');
                console.log('Largura: ' + rect.width + ', Altura: ' + rect.height);
            });
        }

        // Função para carregar a imagem do input
        function onImageUpload(event) {
            let img = new Image();
            img.onload = function() {
                // Criar uma nova imagem e desenhá-la no canvas
                let canvas = document.getElementById('canvas');
                let ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);

                // Detectar as cartas na imagem
                detectCards(canvas.toDataURL());
            };
            img.src = URL.createObjectURL(event.target.files[0]);
        }

        document.getElementById('imageInput').addEventListener('change', onImageUpload);
    </script>
</body>
</html>
