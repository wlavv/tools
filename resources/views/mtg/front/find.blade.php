<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magic: The Gathering AR - Cloudpost</title>
    <script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mind-ar@1.2.5/dist/mindar-image-aframe.prod.js"></script>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            background-color: #1e1e1e;
        }
        .example-container {
            position: relative;
            width: 100%;
            height: 100vh;
        }
        #example-scanning-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.6);
        }
        #example-scanning-overlay img {
            max-width: 100%;
        }
        .scanline {
            position: absolute;
            width: 100%;
            height: 3px;
            background: rgba(255, 255, 255, 0.7);
            animation: scan 1s linear infinite;
            top: 50%;
        }
        @keyframes scan {
            0% { top: -10%; }
            100% { top: 110%; }
        }

        /* Painéis de Glass UI */
        .glass-panel {
            position: absolute;
            width: 30%;
            height: 40%;
            background: rgba(255, 255, 255, 0.2); /* Transparência */
            backdrop-filter: blur(10px); /* Efeito de desfoque */
            color: white;
            padding: 20px;
            border-radius: 10px;
            display: none; /* Inicialmente invisível */
        }

        /* Posicionamento dos painéis em duas linhas de 3 colunas */
        .panel-1 { top: 10%; left: 5%; }
        .panel-2 { top: 10%; left: 35%; }
        .panel-3 { top: 10%; left: 65%; }
        .panel-4 { top: 60%; left: 5%; }
        .panel-5 { top: 60%; left: 35%; }
        .panel-6 { top: 60%; left: 65%; }

        /* Texto dos painéis */
        .glass-panel h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .glass-panel p {
            font-size: 1.2rem;
        }

        .clickable {
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="example-container">
    <div id="example-scanning-overlay">
        <!-- A linha de escaneamento (animação) -->
        <div class="scanline"></div>
        <div class="inner">
            <img src="/images/mtg/custom_images/Magic_card_back.png" alt="Card Back">
        </div>
    </div>

    <!-- MindAR Scene -->
    <a-scene mindar-image="imageTargetSrc: /images/mtg/minds/cloudpost.mind; uiScanning: #example-scanning-overlay;" embedded="" color-space="sRGB">
        <!-- Assets -->
        <a-assets>
            <img id="card" src="/images/mtg/cards/cloudpost.png">
            <img id="icon-info" src="/images/icons/info.png">
        </a-assets>

        <!-- Camera -->
        <a-camera position="0 0 0" look-controls="enabled: false"></a-camera>

        <!-- Carta -->
        <a-entity mindar-image-target="targetIndex: 0" id="cloudpost-target">
            <a-plane src="#card" position="0 0 0" height="0.552" width="1" rotation="0 0 0"></a-plane>

            <!-- Texto dinâmico (Nome da carta) -->
            <a-text value="Cloudpost" color="black" align="center" position="0 0.4 0" scale="2 2 2"></a-text>
        </a-entity>

        <!-- Painéis Glass UI (Estáticos) -->
        <div id="panel-1" class="glass-panel panel-1">
            <h2>Cloudpost</h2>
            <p>Mana Cost: {C}</p>
            <p>Color: Colorless</p>
        </div>
        <div id="panel-2" class="glass-panel panel-2">
            <h2>Text</h2>
            <p>Add {C} for each Cloudpost you control.</p>
        </div>
        <div id="panel-3" class="glass-panel panel-3">
            <h2>Flavor Text</h2>
            <p>"A testament to the greatness of Mirrodin."</p>
        </div>
        <div id="panel-4" class="glass-panel panel-4">
            <h2>Price</h2>
            <p>$10.50</p>
        </div>
        <div id="panel-5" class="glass-panel panel-5">
            <h2>Legal in</h2>
            <p>Legacy, Vintage</p>
        </div>
        <div id="panel-6" class="glass-panel panel-6">
            <h2>Suggested Decks</h2>
            <p>Ramp, Colorless Artifact</p>
        </div>
    </a-scene>
</div>

<script>
    // Mostrar os painéis quando a carta for detectada
    const targetEntity = document.getElementById('cloudpost-target');
    const panels = document.querySelectorAll('.glass-panel');

    targetEntity.addEventListener('mindar-image-target-detected', function() {
        // Exibir os painéis quando a carta for detectada
        panels.forEach(panel => panel.style.display = 'block');
    });

    targetEntity.addEventListener('mindar-image-target-lost', function() {
        // Ocultar os painéis quando a carta for perdida
        panels.forEach(panel => panel.style.display = 'none');
    });
</script>

</body>
</html>
