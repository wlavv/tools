<html>
   <head>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="apple-mobile-web-app-capable" content="yes">
      <meta aframe-injected="" name="mobile-web-app-capable" content="yes">
      <meta aframe-injected="" name="theme-color" content="black">
      @include("mtg.front.includes.css")
      @include("mtg.front.includes.js")
        <style>
            .panel-container {
                display: flex;
                justify-content: space-between;
                align-items: stretch;
                width: 100%;
                height: 100vh;
                padding: 20px;
                box-sizing: border-box;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 9999;
                pointer-events: none; /* opcional para não interferir no AR */
                gap: 20px;
            }
            
            .panel_parent {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                gap: 20px;
            }

            .panel_parent .panel {
                flex: 1 1 50%;
                height: 50%;
            }


            .panel {
                padding: 20px;
                border-radius: 16px;
                background: rgba(100, 149, 237, 0.2); /* Azul suave (tipo cornflowerblue) */
                backdrop-filter: blur(15px) saturate(160%);
                -webkit-backdrop-filter: blur(15px) saturate(160%);
                border: 1px solid rgba(255, 255, 255, 0.2);
                box-shadow:
                    0 4px 12px rgba(0, 0, 0, 0.4),  /* sombra inferior leve */
                    0 8px 24px rgba(0, 0, 0, 0.25); /* sombra mais profunda para relevo */
                color: #fff;
                pointer-events: auto;
                text-align: center;
                overflow-y: auto;
                margin: 10px;
            }

            .panel-container > .panel {
                flex: 1;
            }


            .panel h2 {
                margin-top: 0;
                font-size: 1.5em;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
                padding-bottom: 10px;
            }

            .hidden-panel {
                display: none;
            }

            #multi-panel {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 100vw;
  display: flex;
  justify-content: space-evenly;
  align-items: center;
  z-index: 9999;
  pointer-events: none;
  gap: 30px;
  padding: 40px;
  box-sizing: border-box;
  perspective: 1200px; /* Fundamental para 3D */
}

#multi-panel .glass-panel {
  flex: 1;
  max-width: 45%;
  height: 80%;
  background: linear-gradient(to bottom right, rgba(255,255,255,0.15), rgba(255,255,255,0.05));
  border-radius: 20px;
  padding: 20px;
  color: white;
  text-align: center;
  backdrop-filter: blur(15px) saturate(150%);
  -webkit-backdrop-filter: blur(15px) saturate(150%);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow-y: auto;
  pointer-events: auto;

  /* 3D effect */
  transform: rotateY(5deg) translateZ(20px);
  box-shadow:
    0 10px 30px rgba(0, 0, 0, 0.4),
    0 20px 60px rgba(0, 0, 0, 0.2),
    inset 0 0 20px rgba(255, 255, 255, 0.05);
  transition: transform 0.5s ease, box-shadow 0.5s ease;
}

#multi-panel .glass-panel.right {
  transform: rotateY(-5deg) translateZ(20px);
}

#multi-panel .glass-panel:hover {
  transform: rotateY(0deg) scale(1.02);
  box-shadow:
    0 15px 40px rgba(0, 0, 0, 0.6),
    0 30px 80px rgba(0, 0, 0, 0.25),
    inset 0 0 30px rgba(255, 255, 255, 0.1);
}

#multi-panel .glass-panel h2 {
  font-size: 1.6rem;
  border-bottom: 1px solid rgba(255,255,255,0.2);
  padding-bottom: 10px;
  margin-top: 0;
}

.hidden-panel {
  display: none !important;
}

@media (max-width: 768px) {
  #multi-panel {
    flex-direction: column;
    align-items: stretch;
  }

  #multi-panel .glass-panel {
    max-width: 90%;
    height: 45%;
    transform: none;
  }
}


</style>
   </head>
   <body cz-shortcut-listen="true">
  <div class="example-container">
    <div id="example-scanning-overlay">
      <div class="inner">
        <img src="/images/mtg/custom_images/Magic_card_back.png">
        <div class="scanline"></div>
      </div>
    </div>

    <a-scene mindar-image="imageTargetSrc: /images/mtg/minds/cloudpost.mind; showStats: false; uiScanning: #example-scanning-overlay; persistent: true;" embedded color-space="sRGB" renderer="colorManagement: true, physicallyCorrectLights" vr-mode-ui="enabled: true" device-orientation-permission-ui="enabled: false">
      <a-camera position="0 0 0" look-controls="enabled: false" cursor="fuse: false; rayOrigin: mouse;" raycaster="far: 10000; objects: .clickable"></a-camera>
      <a-entity id="mytarget" mindar-image-target="targetIndex: 0"></a-entity>
    </a-scene>

    @include("mtg.front.includes.AR_content")
    <video autoplay muted playsinline style="position: absolute; top: -242.5px; left: 0px; z-index: -2; width: 1920px; height: 1440px;" width="640" height="480"></video>
  </div>

  <!-- NOVO PAINEL -->
  <div id="multi-panel" class="hidden-panel">
    <div class="glass-panel left">
      <h2>Informação Esquerda</h2>
      <p>Conteúdo detalhado do painel esquerdo.</p>
    </div>
    <div class="glass-panel right">
      <h2>Informação Direita</h2>
      <p>Conteúdo detalhado do painel direito.</p>
    </div>
  </div>

  <div class="mindar-ui-overlay mindar-ui-loading hidden">
    <div class="loader"></div>
  </div>

  <script>
    const glassPanel = document.getElementById('multi-panel');
    const mytarget = document.getElementById('mytarget');

    mytarget.addEventListener('targetFound', () => {
      glassPanel.classList.remove('hidden-panel');
    });

    mytarget.addEventListener('targetLost', () => {
      glassPanel.classList.add('hidden-panel');
    });
  </script>
</body>

</html>
