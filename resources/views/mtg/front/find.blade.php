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

        /* Animação de scan */
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

        /* Painel de Glass UI fixo no centro */
        .glass-panel {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Centraliza o painel */
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.2); /* Transparência */
            backdrop-filter: blur(10px); /* Efeito de desfoque */
            color: white;
            padding: 20px;
            border-radius: 10px;
            display: none; /* Inicialmente invisível */
        }

        .glass-panel h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .glass-panel p {
            font-size: 1.2rem;
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
    </a-scene>

    <!-- Painel Glass UI fixo no centro -->
    <div id="glass-panel" class="glass-panel">
        <h2>Cloudpost</h2>
        <p>Mana Cost: {C}</p>
        <p>Color: Colorless</p>
        <p>Text: Add {C} for each Cloudpost you control.</p>
        <p>Flavor Text: "A testament to the greatness of Mirrodin."</p>
        <p>Block & Set: Mirrodin</p>
        <p>Price: $10.50</p>
        <p>Legal in: Legacy, Vintage</p>
        <p>Suggested Decks: Ramp, Colorless Artifact</p>
    </div>
</div>

<script>
    // Mostrar o painel fixo quando a carta for detectada
    const targetEntity = document.getElementById('cloudpost-target');
    const glassPanel = document.getElementById('glass-panel');

    targetEntity.addEventListener('mindar-image-target-detected', function() {
        // Exibir o painel Glass UI fixo quando a carta for detectada
        glassPanel.style.display = 'block';
    });

    targetEntity.addEventListener('mindar-image-target-lost', function() {
        // Ocultar o painel quando a carta for perdida
        glassPanel.style.display = 'none';
    });
</script>

</body>
</html>
