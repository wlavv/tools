<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magic: The Gathering AR Interface</title>
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
        .clickable {
            cursor: pointer;
        }
        /* Adicionando um estilo específico para os botões */
        .info-button {
            position: absolute;
            bottom: 20px;
            left: 20px;
            width: 100px;
            height: 100px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            text-align: center;
            line-height: 100px;
            border-radius: 50%;
            font-size: 16px;
            font-weight: bold;
        }
        .info-button:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        /* Efeito visual para animação */
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
        <a-entity mindar-image-target="targetIndex: 0">
            <a-plane src="#card" position="0 0 0" height="0.552" width="1" rotation="0 0 0"></a-plane>

            <!-- Texto dinâmico (Nome da carta) -->
            <a-text value="Cloudpost" color="black" align="center" position="0 0.4 0" scale="2 2 2"></a-text>

            <!-- Botões interativos -->
            <a-image id="info-button" class="clickable scale-animation" src="#icon-info" position="0 0 -0.5" scale="0.1 0.1 0">
            </a-image>
        </a-entity>
    </a-scene>
</div>

<!-- Botão de Informações Estilo Magic -->
<div class="info-button" onclick="alert('Aqui você pode exibir mais informações sobre a carta Cloudpost.');">
    INFO
</div>

</body>
</html>
