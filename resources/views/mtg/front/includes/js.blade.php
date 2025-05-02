<script>
            // 1. Criando o Tracker Customizado
            var CardTracker = function() {
                this.stepSize = 1;
                this.initialScale = 1;
                this.edgesDensity = 0.3;
            };

            // 2. Herda de tracking.Tracker
            tracking.inherits(CardTracker, tracking.Tracker);

            // 3. Implementando o método track com detecção de contornos real
            CardTracker.prototype.track = function(pixels, width, height) {
                var rects = [];
                var threshold = 100; // Limiar para detectar bordas (ajustável)

                // Passo 1: Convertendo para escala de cinza
                var grayscalePixels = new Array(width * height);
                for (var i = 0; i < width * height; i++) {
                    var r = pixels[i * 4];
                    var g = pixels[i * 4 + 1];
                    var b = pixels[i * 4 + 2];

                    // Convertendo para escala de cinza
                    grayscalePixels[i] = 0.3 * r + 0.59 * g + 0.11 * b;
                }

                // Passo 2: Aplicar um filtro de detecção de bordas (simplificado de Canny)
                var edges = this.applyEdgeDetection(grayscalePixels, width, height, threshold);

                // Passo 3: Encontrar contornos nas bordas detectadas
                for (var y = 0; y < height; y++) {
                    for (var x = 0; x < width; x++) {
                        if (edges[y * width + x] > threshold) {

                            // Encontrar a largura e a altura dinamicamente com base no contorno
                            var minX = x, minY = y, maxX = x, maxY = y;

                            // Explorar os pixels ao redor do ponto atual para encontrar o contorno completo
                            for (var dy = -5; dy <= 5; dy++) {
                                for (var dx = -5; dx <= 5; dx++) {
                                    var nx = x + dx;
                                    var ny = y + dy;
                                    if (nx >= 0 && nx < width && ny >= 0 && ny < height) {
                                        if (edges[ny * width + nx] > threshold) {
                                            minX = Math.min(minX, nx);
                                            minY = Math.min(minY, ny);
                                            maxX = Math.max(maxX, nx);
                                            maxY = Math.max(maxY, ny);
                                        }
                                    }
                                }
                            }

                            // Cálculo das dimensões do retângulo
                            var detectedWidth = maxX - minX;
                            var detectedHeight = maxY - minY;

                            var angle = Math.atan2(detectedHeight, detectedWidth); // Aqui pode-se calcular a rotação

                            // Calcular a proporção da área
                            var detectedRatio = detectedWidth / detectedHeight;

                            // Se a proporção for aproximadamente 3:4 (como uma carta)
                            if (Math.abs(detectedRatio - 3 / 4) < 0.1) {
                                // Adicionando o retângulo detectado
                                rects.push({
                                    x: minX,
                                    y: minY,
                                    width: detectedWidth,
                                    height: detectedHeight,
                                    angle: angle
                                });
                            }
                        }
                    }
                }

                // Emitir o evento track com as deteções
                this.emit('track', {
                    data: rects
                });
            };

            // 4. Método para aplicar a detecção de bordas (simplificado de Canny)
            CardTracker.prototype.applyEdgeDetection = function(pixels, width, height, threshold) {
                var edges = new Array(width * height).fill(0);

                // Aplica uma detecção de bordas simples (simulando Canny)
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
                var cropCanvas = document.getElementById('cropCanvas');
                var cropContext = cropCanvas.getContext('2d');

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

                        console.log( 'DETECTED: ' + detectedRatio );
                        console.log( 'DIFF: ' + ( detectedRatio - 0.666666667 ) );
                        console.log( 'COMPARE: ' + Math.abs(detectedRatio - 0.666666667) );

                        if (Math.abs(detectedRatio - 0.666666667) < 0.1) { // Permitimos uma pequena variação
                            context.strokeStyle = '#a64ceb';
                            context.strokeRect(rect.x, rect.y, rect.width, rect.height);
                            context.font = '11px Helvetica';
                            context.fillStyle = "#fff";
                            context.fillText('x: ' + rect.x + 'px', rect.x + rect.width + 5, rect.y + 11);
                            context.fillText('y: ' + rect.y + 'px', rect.x + rect.width + 5, rect.y + 22);

                            console.log( 'X: ' + rect.x );
                            console.log( 'Y: ' + rect.y );
                            console.log( 'width: ' + rect.width );
                            console.log( 'height: ' + rect.height );
                            
                            alert('X: ' +  rect.x  + ' | Y: ' +  rect.y + ' | width: ' +  rect.width  + ' | height: ' +  rect.height  + ' | Angle: ' +  rect.angle );
                            
                            // Agora, fazermos o "crop" da imagem detectada
                            var imageData = context.getImageData(rect.x, rect.y, rect.width, rect.height);
                            
                            // Exibir o crop no canvas de preview em tons de cinza
                            var grayscaleData = convertToGrayscale(imageData);
                            context.putImageData(grayscaleData, rect.x, rect.y);

                            // Agora faz o "crop" para o cropCanvas
                            var croppedImage = context.getImageData(rect.x, rect.y, rect.width, rect.height);
                            cropContext.putImageData(croppedImage, 0, 0); // Exibir no canvas de crop

                        }
                    });
                });
            };

            function convertToGrayscale(imageData) {
                var data = imageData.data;
                for (var i = 0; i < data.length; i += 4) {
                    var r = data[i];
                    var g = data[i + 1];
                    var b = data[i + 2];

                    // Convertendo para escala de cinza
                    var gray = 0.3 * r + 0.59 * g + 0.11 * b;

                    // Atualizando RGB para tons de cinza
                    data[i] = data[i + 1] = data[i + 2] = gray;
                }
                return imageData;


            }
        </script>