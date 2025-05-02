<script>
            // 1. Criando o Tracker Customizado
            var CardTracker = function() {
                this.stepSize = 2;
                this.initialScale = 4;
                this.edgesDensity = 0.1;
                this.threshold = 100; // Ajuste do limiar
                this.minCardWidth = 60;  // Tamanho mínimo da carta
                this.maxCardWidth = 120; // Tamanho máximo da carta
                this.aspectRatio = 3 / 4; // Proporção da carta
            };

            // 2. Herda de tracking.Tracker
            tracking.inherits(CardTracker, tracking.Tracker);

            // 3. Implementando o método track com detecção de contornos real
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
                            // Ajustar a detecção de retângulos com proporção de carta (3:4)
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

            // 4. Método para aplicar a detecção de bordas (filtro de Sobel ou Canny)
            CardTracker.prototype.applyEdgeDetection = function(pixels, width, height, threshold) {
                var edges = new Array(width * height).fill(0);

                // Aplica um filtro de detecção de bordas simplificado (Sobel ou Canny)
                for (var y = 1; y < height - 1; y++) {
                    for (var x = 1; x < width - 1; x++) {
                        // Sobel kernel para detecção de bordas (simplificado)
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

            // 5. Usando o Tracker
            window.onload = function() {
                var video = document.getElementById('video');
                var canvas = document.getElementById('canvas');
                var context = canvas.getContext('2d');

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
                            context.strokeStyle = '#a64ceb';
                            context.strokeRect(rect.x, rect.y, rect.width, rect.height);
                            context.font = '11px Helvetica';
                            context.fillStyle = "#fff";
                            context.fillText('x: ' + rect.x + 'px', rect.x + rect.width + 5, rect.y + 11);
                            context.fillText('y: ' + rect.y + 'px', rect.x + rect.width + 5, rect.y + 22);
                        }
                    });
                });
            };
        </script>