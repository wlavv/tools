<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>MTG Card Tracker with pHash</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        @include("mtg.front.includes.css")
    </head>
    <body>
        <div class="container">
            <h1>Tracking de Objetos com Webcam</h1>
            <video id="videoInput" width="640" height="480" autoplay muted></video>
            <canvas id="canvasOutput" width="640" height="480"></canvas>

            <div class="controls">
                <div class="control-group">
                    <label for="posX">Posição X:</label>
                    <input type="number" id="posX" disabled>
                </div>
                <div class="control-group">
                    <label for="posY">Posição Y:</label>
                    <input type="number" id="posY" disabled>
                </div>
                <div class="control-group">
                    <label for="width">Largura:</label>
                    <input type="number" id="width" disabled>
                </div>
                <div class="control-group">
                    <label for="height">Altura:</label>
                    <input type="number" id="height" disabled>
                </div>
            </div>
        </div>
    </body>
    @include("mtg.front.includes.js")
</html>