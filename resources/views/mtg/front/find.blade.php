<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>tracking.js - face with camera</title>
        @include("mtg.front.includes.css")
        <script src="https://aframe.io/releases/1.5.0/aframe.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/mind-ar@1.2.5/dist/mindar-image-aframe.prod.js"></script>
    </head>
    <body>
        <a-scene mindar-image="imageTargetSrc: images/mtg/minds/seedbornmuse.mind;" vr-mode-ui="enabled: false" device-orientation-permission-ui="enabled: false">
            <!-- Definição da câmera -->
            <a-camera position="0 0 0" look-controls="enabled: false"></a-camera>
            
            <!-- Entidade de AR ligada à imagem alvo -->
            <a-entity mindar-image-target="targetIndex: 0">
                <!-- Plane que será exibido quando a imagem alvo for detectada -->
                <a-plane color="blue" opacity="0.5" position="0 0 0" height="0.552" width="1" rotation="0 0 0"></a-plane>
            </a-entity>
        </a-scene>
        
        @include("mtg.front.includes.js")
    </body>
</html>
