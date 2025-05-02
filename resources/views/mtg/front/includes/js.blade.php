<script>
            // 1. Criando o Tracker Customizado
            var CardTracker = function() {
                // Propriedade de configuração
                this.stepSize = 2;  // Sensibilidade do rastreamento
                this.initialScale = 4;  // Tamanho inicial do rastreamento
                this.edgesDensity = 0.1;  // Densidade de bordas
            };

            // 2. Herda de tracking.Tracker
            tracking.inherits(CardTracker, tracking.Tracker);

            // 3. Implementando o método track com detecção de bordas simplificada
            CardTracker.prototype.track = function(pixels, width, height) {
                var rects = [];

                // Vamos tentar detectar bordas de objetos (ex: retângulos)
                for (var y = 0; y < height; y++) {
                    for (var x = 0; x < width; x++) {
                        // Verificar se o pixel está na borda (simples detecção de borda)
                        var pixel = pixels[(y * width + x) * 4];

                        // Detecta se o pixel tem uma cor forte (só como exemplo)
                        if (pixel < 100) { // Ajusta o limiar conforme necessário
                            // Aqui, estamos apenas desenhando um retângulo fictício para teste
                            rects.push({
                                x: 50, y: 50, width: 200, height: 300
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