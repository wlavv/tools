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
        </style>
   </head>
   <body cz-shortcut-listen="true">
      <div class="example-container">
         <div id="example-scanning-overlay" class="">
            <div class="inner">
               <img src="/images/mtg/custom_images/Magic_card_back.png">
               <div class="scanline"></div>
            </div>
         </div>
         <a-scene mindar-image="imageTargetSrc: /images/mtg/minds/cloudpost.mind; showStats: false; uiScanning: #example-scanning-overlay; persistent: true;" embedded="" color-space="sRGB" renderer="colorManagement: true, physicallyCorrectLights" vr-mode-ui="enabled: true" device-orientation-permission-ui="enabled: false">
            <a-assets>
               <img id="card" src="./assets/card-example/card.png">
               <img id="icon-web" src="./assets/card-example/icons/web.png">
               <img id="icon-location" src="./assets/card-example/icons/location.png">
               <img id="icon-profile" src="./assets/card-example/icons/profile.png">
               <img id="icon-phone" src="./assets/card-example/icons/phone.png">
               <img id="icon-email" src="./assets/card-example/icons/email.png">
               <img id="icon-play" src="./assets/card-example/icons/play.png">
               <img id="icon-left" src="./assets/card-example/icons/left.png">
               <img id="icon-right" src="./assets/card-example/icons/right.png">
               <img id="paintandquest-preview" src="./assets/card-example/portfolio/paintandquest-preview.png">
               <video id="paintandquest-video-mp4" autoplay="false" loop="true" src="./assets/card-example/portfolio/paintandquest.mp4" playsinline="" webkit-playsinline=""></video>
               <video id="paintandquest-video-webm" autoplay="false" loop="true" src="./assets/card-example/portfolio/paintandquest.webm" playsinline="" webkit-playsinline=""></video>
               <img id="coffeemachine-preview" src="./assets/card-example/portfolio/coffeemachine-preview.png">
               <img id="peak-preview" src="./assets/card-example/portfolio/peak-preview.png">
               <a-asset-item id="avatarModel" src="https://cdn.jsdelivr.net/gh/hiukim/mind-ar-js@1.2.2/examples/image-tracking/assets/card-example/softmind/scene.gltf"></a-asset-item>
            </a-assets>
            <a-camera position="0 0 0" look-controls="enabled: false" cursor="fuse: false; rayOrigin: mouse;" raycaster="far: 10000; objects: .clickable"></a-camera>
            <a-entity id="mytarget" mytarget="" mindar-image-target="targetIndex: 0">
               <a-plane src="#card" position="0 0 0" height="0.552" width="1" rotation="0 0 0"></a-plane>
               <a-plane src="#card" position="6 6 6" height="1.552" width="3" rotation="1 0 0"></a-plane>
               <a-entity id="info-panel" position="0 -0.3 0">
                  <a-plane color="black" opacity="0.8">
                     <a-text value="Cloudpost" color="white" position="0 0.3 0"></a-text>
                     <a-text value="Mana Cost: {C}" color="white" position="0 0.2 0"></a-text>
                     <a-text value="Color: Colorless" color="white" position="0 0.1 0"></a-text>
                     <a-text value="Text: Add {C} for each Cloudpost" color="white" position="0 0 0"></a-text>
                     <a-text value="Flavor Text: 'A testament to the greatness of Mirrodin.'" color="white" position="0 -0.1 0"></a-text>
                     <a-text value="Block & Set: Mirrodin" color="white" position="0 -0.2 0"></a-text>
                     <a-text value="Price: $10.50" color="white" position="0 -0.3 0"></a-text>
                     <a-text value="Legal in: Legacy, Vintage" color="white" position="0 -0.4 0"></a-text>
                     <a-text value="Suggested Decks: Ramp, Colorless Artifact" color="white" position="0 -0.5 0"></a-text>
                  </a-plane>
               </a-entity>
            </a-entity>
         </a-scene>

         <!-- Novos paineis fixos lado a lado -->
        <div id="multi-panel" class="panel-container hidden-panel">
            <div class="panel_parent">
                <div class="panel">
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
                <div class="panel">
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
            <div class="panel_parent">
                <div class="panel">
                    <h2>Imagem</h2>
                    <img src="/images/mtg/mir/cloudpost.jpg" style="max-width: 90%;margin: 0 auto;">
               </div>
            </div>
            <div class="panel_parent">
                <div class="panel">
                    <h2>Outros Dados</h2>
                    <p>Exemplo de conteúdo adicional:</p>
                    <ul>
                        <li>Estatísticas do Deck</li>
                        <li>Cartas semelhantes</li>
                        <li>Links para compra</li>
                    </ul>
                </div>
                <div class="panel">
                    <h2>Outros Dados</h2>
                    <p>Exemplo de conteúdo adicional:</p>
                    <ul>
                        <li>Estatísticas do Deck</li>
                        <li>Cartas semelhantes</li>
                        <li>Links para compra</li>
                    </ul>
                </div>
            </div>
        </div>

         <video autoplay muted playsinline style="position: absolute; top: -242.5px; left: 0px; z-index: -2; width: 1920px; height: 1440px;" width="640" height="480"></video>
      </div>

      <div class="mindar-ui-overlay mindar-ui-loading hidden">
         <div class="loader"></div>
      </div>

   </body>
</html>
