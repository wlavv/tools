<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>MTG Card Tracker with pHash</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
        @include("mtg.front.includes.css")
    </head>
    <body>
        <div id="info">⌛ A iniciar...</div>
        <div class="video-crop-container">
            <div id="videoContainer"></div>
            <div id="cropZone"></div>
        </div>
        <div id="croppedImage"></div>
    </body>
    @include("mtg.front.includes.js")
</html>