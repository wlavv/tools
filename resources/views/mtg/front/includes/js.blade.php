
<script>
        // Captura do vídeo e configuração do canvas
        var video = document.getElementById('video');
        var previewCanvas = document.getElementById('previewCanvas');
        var context = previewCanvas.getContext('2d');
        var trackingContext = document.createElement('canvas').getContext('2d');

        // Inicia o vídeo com a webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(stream) {
                video.srcObject = stream;
            })
            .catch(function(error) {
                console.error('Erro ao acessar a câmera: ', error);
            });

        // Função para converter a imagem para tons de cinza
        function convertToGrayscale(imageData) {
            var data = imageData.data;
            for (var i = 0; i < data.length; i += 4) {
                var r = data[i];
                var g = data[i + 1];
                var b = data[i + 2];

                // Convertendo para escala de cinza (fórmula luminance)
                var gray = 0.3 * r + 0.59 * g + 0.11 * b;

                // Atualizando RGB para tons de cinza
                data[i] = data[i + 1] = data[i + 2] = gray;
            }
            return imageData;
        }

        // Tracker Customizado
        var CardTracker = function() {
            this.stepSize = 2;
            this.initialScale = 4;
            this.edgesDensity = 0.1;
            this.threshold = 100; // Ajuste de limiar
            this.minCardWidth = 60;  // Tamanho mínimo da carta
            this.maxCardWidth = 120; // Tamanho máximo da carta
            this.aspectRatio = 3 / 4; // Proporção da carta
        };

        tracking.inherits(CardTracker, tracking.Tracker);

        CardTracker.prototype.track = function(pixels, width, height) {
            var rects = [];

            // Passo 1: Convertendo para escala de cinza
            var grayscalePixels = new Array(width * height);
            for (var i = 0; i < width * height; i++) {
                var r = pixels[i * 4];
                var g = pixels[i * 4 + 1];
                var b = pixels[i * 4 + 2];

                // Convertendo para escala de cinza
                grayscalePixels[i] = 0.3 * r + 0.59 * g + 0.11 * b;
            }

            // Passo 2: Aplicar um filtro de detecção de bordas (Canny ou Sobel)
            var edges = this.applyEdgeDetection(grayscalePixels, width, height, this.threshold);

            // Passo 3: Encontrar contornos nas bordas detectadas
            for (var y = 0; y < height; y++) {
                for (var x = 0; x < width; x++) {
                    if (edges[y * width + x] > this.threshold) {
                        var detectedWidth = 100;  // Ajustar dependendo do contorno
                        var detectedHeight = 150; // Ajustar dependendo do contorno
                        var ratio = detectedWidth / detectedHeight;

                        // Verificar se a proporção está entre 3:4 (aproximadamente)
                        if (Math.abs(ratio - this.aspectRatio) < 0.1) {
                            // Verificar se o tamanho da carta está dentro do intervalo permitido
                            if (detectedWidth >= this.minCardWidth && detectedWidth <= this.maxCardWidth) {
                                rects.push({
                                    x: x,
                                    y: y,
                                    width: detectedWidth,
                                    height: detectedHeight
                                });
                            }
                        }
                    }
                }
            }

            // Emitir o evento track com as deteções
            this.emit('track', {
                data: rects
            });
        };

        // Método para aplicar a detecção de bordas (filtro de Sobel ou Canny)
        CardTracker.prototype.applyEdgeDetection = function(pixels, width, height, threshold) {
            var edges = new Array(width * height).fill(0);

            // Aplica um filtro de detecção de bordas simplificado (Sobel ou Canny)
            for (var y = 1; y < height - 1; y++) {
                for (var x = 1; x < width - 1; x++) {
                    var gx = -1 * pixels[(y - 1) * width + (x - 1)] + 1 * pixels[(y - 1) * width + (x + 1)] +
                             -2 * pixels[y * width + (x - 1)] + 2 * pixels[y * width + (x + 1)] +
                             -1 * pixels[(y + 1) * width + (x - 1)] + 1 * pixels[(y + 1) * width + (x + 1)];

                    var gy = -1 * pixels[(y - 1) * width + (x - 1)] + 1 * pixels[(y - 1) * width + (x + 1)] +
                             -2 * pixels[(y - 1) * width + x] + 2 * pixels[(y + 1) * width + x] +
                             -1 * pixels[(y + 1) * width + (x - 1)] + 1 * pixels[(y + 1) * width + (x + 1)];

                    // Magnitude do gradiente
                    var magnitude = Math.sqrt(gx * gx + gy * gy);

                    // Se a magnitude for maior que o limiar, é uma borda
                    edges[y * width + x] = magnitude > threshold ? 255 : 0;
                }
            }

            return edges;
        };

        // Usando o Tracker
        window.onload = function() {
            var video = document.getElementById('video');
            var canvas = document.getElementById('canvas');
            var context = canvas.getContext('2d');
            var trackingContext = canvas.getContext('2d');

            // Instanciando o nosso tracker customizado
            var cardTracker = new CardTracker();

            // Começar o tracking com a webcam
            tracking.track('#video', cardTracker, { camera: true });

            // Evento de tracking
            cardTracker.on('track', function(event) {
                context.clearRect(0, 0, canvas.width, canvas.height); // Limpar o canvas

                event.data.forEach(function(rect) {
                    // Filtro para garantir que estamos a detectar retângulos com a proporção correta
                    var detectedRatio = rect.width / rect.height;
                    if (Math.abs(detectedRatio - cardTracker.aspectRatio) < 0.1) { // Permitimos uma pequena variação
                        // Processar apenas a região detectada e mostrar no previewCanvas em tons de cinza
                        var imageData = trackingContext.getImageData(rect.x, rect.y, rect.width, rect.height);
                        var grayscaleData = convertToGrayscale(imageData);
                        context.putImageData(grayscaleData, 0, 0);

                        // Desenhando a área do tracker em cinza
                        context.strokeStyle = '#a64ceb';
                        context.strokeRect(rect.x, rect.y, rect.width, rect.height);
                    }
                });
            });

            // Função de renderização contínua
            function render() {
                // Desenha o vídeo no canvas oculto
                trackingContext.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Continua o loop de renderização
                requestAnimationFrame(render);
            }

            // Inicia o loop de renderização quando o vídeo começa a tocar
            video.onplay = function() {
                render();
            };
        };
    </script>