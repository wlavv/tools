<html>
   <head>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="apple-mobile-web-app-capable" content="yes">
      <meta aframe-injected="" name="mobile-web-app-capable" content="yes">
      <meta aframe-injected="" name="theme-color" content="black">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
      @include("mtg.front.includes.css")

      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

      @include("mtg.front.includes.js")
        <style>

            body{ background-color: #000; }

            @font-face {
                font-family: 'magicTheGathering';
                src: url('/fonts/planewalker/Planewalker.otf');
            }

            @font-face {
                font-family: 'magicTheGatheringItalic';
                src: url('/fonts/planewalker/Planewalker Italic.otf');
            }

            @font-face {
                font-family: 'magicTheGatheringBold';
                src: url('/fonts/planewalker/Planewalker Bold.otf');
            }

            @font-face {
                font-family: 'magicTheGatheringBoldItalic';
                src: url('/fonts/planewalker/Planewalker Bold Italic.otf');
            }

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

            .panel_title{
                font-family: 'magicTheGathering';
                letter-spacing: 5px;
                text-align: left;                
            }

            .card_detail_table{
                list-style: none;
                text-align: left;
                margin-left: 0;padding-left: 0;line-height: 1.8;font-weight: bold;
                display: grid;
                
            }

            .card_detail_tag{
              color: white;
              font-weight: bolder;
              font-size: 18px;
              width: 150px;
              text-align: right;
              padding-right: 8px;
              text-transform: uppercase;
              vertical-align: baseline;
            }

            .card_detail_value{
              font-size: 18px;
            }

            #cardImageContainer{ display: block; }
            #cardPriceContainer{ display: none; }
            #cardBanningContainer{ display: none; }

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

    @include("mtg.front.includes.AR_content_clean")
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
      loadCardDetails('LGN', 99);
      glassPanel.classList.remove('hidden-panel');
    });

    mytarget.addEventListener('targetLost', () => {
      glassPanel.classList.add('hidden-panel');
    });



    function loadCardDetails(edition, collectorNumber) {
        $.ajax({
            url: "{{ route('mtg.postCardDetail') }}",
            method: 'POST',
            data: {
                edition: edition,
                collector_number: collectorNumber,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('.cardContainer').html(response);
            },
            error: function(xhr) {
                console.error("Erro ao carregar os detalhes da carta:", xhr);
                $('.cardContainer').html('<p>Erro ao carregar os dados.</p>');
            }
        });
    }

  </script>
</body>

</html>
