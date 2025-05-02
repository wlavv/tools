<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>tracking.js - face with camera</title>
        @include("mtg.front.includes.css")
        <script src="/build/assets/tracking/dat.js"></script>
        <script src="/build/assets/tracking/tracking.js"></script>
        <script src="/build/assets/tracking/face-min.js"></script>
        <!--<script src="/build/assets/tracking/stats.min.js"></script>-->
    </head>
    <body>
        <div class="demo-frame">
            <div class="demo-container">

                <!-- Video com a webcam -->
                <video id="video" width="320" height="240" preload autoplay loop muted></video>

                <!-- Canvas onde será feito o tracking e a conversão para cinza -->
                <canvas id="previewCanvas" width="320" height="240"></canvas>

            </div>
        </div>
        @include("mtg.front.includes.js")
    </body>
</html>
