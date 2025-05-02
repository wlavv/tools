<script>
    // 1. Criando o Tracker Customizado
    var CardTracker = function() {
        CardTracker.prototype.track = function(pixels, width, height) {
            var rects = [];
            // Aqui implementamos a lógica de detecção de retângulos (cartas)

            // Exemplo: Detecção de retângulos com base na proporção 3:4
            // (Este é um exemplo simples e seria necessário um algoritmo de detecção de bordas ou contornos aqui)
            for (var y = 0; y < height; y++) {
                for (var x = 0; x < width; x++) {
                    // Lógica para identificar a borda de um retângulo
                    var pixel = pixels[(y * width + x) * 4];
                    if (pixel) {
                        // Adicionar lógica de detecção e comparação de proporção aqui
                        // Vamos assumir que encontramos um retângulo de exemplo
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
        }
    };

    // 2. Herdando de tracking.Tracker
    tracking.inherits(CardTracker, tracking.Tracker);

    // 3. Usando o Tracker
    window.onload = function() {
        var video = document.getElementById('video');
        var canvas = document.getElementById('canvas');
        var context = canvas.getContext('2d');

        // Instanciando o nosso tracker customizado
        var cardTracker = new CardTracker();

        // Configuração do tracker
        cardTracker.setStepSize(2); // Aumenta a sensibilidade do rastreamento
        cardTracker.setInitialScale(4); // Tamanho inicial do rastreamento
        cardTracker.setEdgesDensity(0.1); // Densidade de bordas

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