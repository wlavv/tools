<script>
window.onload = function() {
                var video = document.getElementById('video');
                var canvas = document.getElementById('canvas');
                var context = canvas.getContext('2d');

                // Proporção fixa para cartas, por exemplo 3:4 (largura:altura)
                const ratio = 3 / 4;

                // Criar o tracker customizado para detectar retângulos com a proporção correta
                var cardTracker = new tracking.ObjectTracker('rectangle');

                // Configurar o tracker
                cardTracker.setStepSize(2); // Aumenta a sensibilidade do rastreamento
                cardTracker.setInitialScale(4); // Tamanho inicial do rastreamento
                cardTracker.setEdgesDensity(0.1); // Densidade das bordas para detectar contornos mais nítidos

                // Começar o tracking com o vídeo
                tracking.track('#video', cardTracker, { camera: true });

                cardTracker.on('track', function(event) {
                    context.clearRect(0, 0, canvas.width, canvas.height); // Limpa a tela

                    event.data.forEach(function(rect) {
                        // Filtro para garantir que estamos a detectar apenas retângulos com a proporção correta
                        var detectedRatio = rect.width / rect.height;
                        if (Math.abs(detectedRatio - ratio) < 0.1) { // Permitimos uma pequena variação
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