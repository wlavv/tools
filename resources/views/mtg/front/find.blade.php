<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>tracking.js - face with camera</title>
        @include("mtg.front.includes.css")
        <script src="/build/assets/tracking/tracking.js"></script>
        <script src="/build/assets/tracking/face-min.js"></script>
        <script src="/node_modules/dat.gui/build/dat.gui.min.js"></script>
        <script src="/build/assets/tracking/stats.min.js"></script>
    </head>
    <body>
        <div class="demo-frame">
            <div class="demo-container">
                <video id="video" preload autoplay loop muted></video>
                <canvas id="canvas"></canvas>
            </div>
        </div>
        @include("mtg.front.includes.js")
    </body>
</html>
