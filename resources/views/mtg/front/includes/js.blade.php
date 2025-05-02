<script>
            // 1. Criando o Tracker Customizado
            var CardTracker = function() {
                // Propriedade de configuração para controle manual
                this.stepSize = 2;  // Sensibilidade do rastreamento
                this.initialScale = 4;  // Tamanho inicial do rastreamento
                this.edgesDensity = 0.1;  // Densidade de bordas

                // Não há necessidade de setStepSize ou setInitialScale aqui
            };

            // 2. Herda de tracking.Tracker
            tracking.inherits(CardTracker, tracking.Tracker);

            // 3. Implementando o método track
            CardTracker.prototype.track = function(pixels, width, height) {
                var rects = [];

                // A lógica da detecção seria aqui, com base nos pixels recebidos
                for (var y = 0; y < height; y++) {
                    for (var x = 0; x < width; x++) {
                        var pixel = pixels[(y * width + x) * 4];
                        if (pixel) {
                            // Detectar um retângulo de exemplo para ilustrar
                            rects.push({
                                x: 100, y: 100, width: 200, height: 300
                            });
                        }
                    }
                }

                // Emitir o evento track com as deteções
                this.emit('track', {
                    data: rects
                });
            };

            // 4. Usando o Tracker
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
                        if (Math.abs(detectedRatio - 3 / 4) < 0.1) { // Permitimos uma pequena variação
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