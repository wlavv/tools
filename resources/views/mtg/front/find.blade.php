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
            display: none;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.6);
        }
        #example-scanning-overlay img {
            max-width: 100%;
        }
        .info-panel {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 50%;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            color: white;
            overflow-y: scroll;
        }
        .info-panel h2, .info-panel p {
            margin: 10px 0;
        }
        .clickable {
            cursor: pointer;
        }
        .scale-animation {
            animation: scaleAnim 1s infinite alternate;
        }
        @keyframes scaleAnim {
            0% { transform: scale(1); }
            100% { transform: scale(1.2); }
        }
    </style>
</head>
<body>

<div class="example-container">
    <div id="example-scanning-overlay">
        <div class="inner">
            <img src="/images/mtg/custom_images/Magic_card_back.png" alt="Card Back">
            <div class="scanline"></div>
        </div>
    </div>

    <a-scene mindar-image="imageTargetSrc: /images/mtg/minds/cloudpost.mind; uiScanning: #example-scanning-overlay;" embedded="" color-space="sRGB">
        <!-- Assets -->
        <a-assets>
            <img id="card" src="/images/mtg/cards/cloudpost.png">
            <img id="icon-info" src="/images/icons/info.png">
        </a-assets>

        <!-- Camera -->
        <a-camera position="0 0 0" look-controls="enabled: false"></a-camera>

        <!-- Carta -->
        <a-entity mindar-image-target="targetIndex: 0">
            <a-plane src="#card" position="0 0 0" height="0.552" width="1" rotation="0 0 0"></a-plane>

            <!-- Texto dinâmico (Nome da carta) -->
            <a-text value="Cloudpost" color="black" align="center" position="0 0.4 0" scale="2 2 2"></a-text>
        </a-entity>

        <!-- Painel de Informações -->
        <a-entity id="info-panel" position="0 -0.3 0">
            <a-plane width="2" height="1" color="black" opacity="0.8">
                <a-text value="Cloudpost" color="white" position="0 0.3 0" scale="1 1 1"></a-text>
                <a-text value="Mana Cost: {C}" color="white" position="0 0.2 0" scale="1 1 1"></a-text>
                <a-text value="Color: Colorless" color="white" position="0 0.1 0" scale="1 1 1"></a-text>
                <a-text value="Text: Add {C} for each Cloudpost" color="white" position="0 0 0" scale="1 1 1"></a-text>
                <a-text value="Flavor Text: 'A testament to the greatness of Mirrodin.'" color="white" position="0 -0.1 0" scale="1 1 1"></a-text>
                <a-text value="Block & Set: Mirrodin" color="white" position="0 -0.2 0" scale="1 1 1"></a-text>
                <a-text value="Price: $10.50" color="white" position="0 -0.3 0" scale="1 1 1"></a-text>
                <a-text value="Legal in: Legacy, Vintage" color="white" position="0 -0.4 0" scale="1 1 1"></a-text>
                <a-text value="Suggested Decks: Ramp, Colorless Artifact" color="white" position="0 -0.5 0" scale="1 1 1"></a-text>
            </a-plane>
        </a-entity>
    </a-scene>
</div>

<!-- Botão de Informações Estilo Magic -->
<div class="info-panel">
    <h2>Magic: The Gathering Card Information</h2>
    <p><strong>Name:</strong> Cloudpost</p>
    <p><strong>Mana Cost:</strong> {C}</p>
    <p><strong>Color:</strong> Colorless</p>
    <p><strong>Text:</strong> Add {C} for each Cloudpost you control.</p>
    <p><strong>Flavor Text:</strong> "A testament to the greatness of Mirrodin."</p>
    <p><strong>Block & Set:</strong> Mirrodin</p>
    <p><strong>Price:</strong> $10.50</p>
    <p><strong>Legal in:</strong> Legacy, Vintage</p>
    <p><strong>Suggested Decks:</strong> Ramp, Colorless Artifact</p>
</div>

</body>
</html>
