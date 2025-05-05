<html>
   <head>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="apple-mobile-web-app-capable" content="yes">
      <meta aframe-injected="" name="mobile-web-app-capable" content="yes">
      <meta aframe-injected="" name="theme-color" content="black">
      @include("mtg.front.includes.css")
      @include("mtg.front.includes.js")
    </head>
    <body cz-shortcut-listen="true">
        <div class="example-container">
            <div id="example-scanning-overlay" class="">
                <div class="inner">
                    <img src="/images/mtg/custom_images/Magic_card_back.png">
                    <div class="scanline"></div>
                </div>
            </div>

            <a-scene mindar-image="imageTargetSrc: /images/mtg/minds/cloudpost.mind; showStats: false; uiScanning: #example-scanning-overlay; persistent: true;" embedded="" color-space="sRGB" renderer="colorManagement: true, physicallyCorrectLights" vr-mode-ui="enabled: true" device-orientation-permission-ui="enabled: false" inspector="" keyboard-shortcuts="" screenshot="">
                <a-entity id="info-panel" position="0 -0.3 0">
                    <a-plane color="black" opacity="0.8">
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
                <canvas class="a-canvas" data-aframe-canvas="true" data-engine="three.js r147" width="1920" height="955"></canvas>
                <div class="a-loader-title" style="display: none;"></div>
                <a-entity light="" data-aframe-default-light="" aframe-injected=""></a-entity>
                <a-entity light="" position="" data-aframe-default-light="" aframe-injected=""></a-entity>
            </a-scene>

<!-- Painel Glass UI fixo no centro -->


    <!-- Painel fixo com informações, visível após a carta ser detectada -->
    <div id="glass-panel" class="fixed-panel">
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

            <video autoplay="" muted="" playsinline="" style="position: absolute; top: -242.5px; left: 0px; z-index: -2; width: 1920px; height: 1440px;" width="640" height="480"></video>
        </div>
        <div class="mindar-ui-overlay mindar-ui-loading hidden">
            <div class="loader"></div>
        </div>

        <script>
    // Mostrar o painel fixo quando a carta for detectada
    const targetEntity = document.getElementById('cloudpost-target');
    const glassPanel = document.getElementById('glass-panel');

    targetEntity.addEventListener('mindar-image-target-detected', function() {
        // Exibir o painel Glass UI fixo quando a carta for detectada
        glassPanel.style.display = 'block';
    });

</script>

    </body>
</html>
