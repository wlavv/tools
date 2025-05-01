<style>
    body {
        background: black;
        color: white;
        font-family: Arial, sans-serif;
        margin: 0;
        overflow: hidden;
    }

    #info {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(0,0,0,0.7);
        padding: 8px;
        border-radius: 4px;
    }

    #croppedImage {
        position: absolute;
        top: 300px;
        left: 10px;
        border: 2px solid white;
        background: rgba(0, 0, 0, 0.5);
    }

    .container {
        display: flex;
        align-items: center;
    }

    #videoElement {
        width: 500px;
        height: auto;  /* Ajuste automático para manter a proporção do vídeo */
    }

    #cropZone {
        width: 500px;
        height: 750px;
        border: 2px solid red; /* Opcional: para destacar a área de crop */
        position: relative;
    }


</style>