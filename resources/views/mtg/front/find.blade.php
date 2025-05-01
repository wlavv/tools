<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>MTG Card Tracker with pHash</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
        @include("mtg.front.includes.css")
        @include("mtg.front.includes.js")
    </head>
    <body>
        <div id="info">⌛ A iniciar...</div>

        <div class="video-crop-container">
            <video id="videoElement" autoplay muted></video>
            <div id="cropZone"></div>
        </div>

        <div id="croppedImage"></div>
    </body>
</html>



    <script>
        /**
        const video = document.getElementById('videoElement');

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function (stream) {
                video.srcObject = stream;
                document.getElementById("info").innerText = "📷 Vídeo iniciado";
            })
            .catch(function (err) {
                console.error("Erro ao aceder à webcam:", err);
                document.getElementById("info").innerText = "❌ Erro ao aceder à webcam";
            });
        **/
    </script>
