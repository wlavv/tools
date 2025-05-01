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
        width: 500px;
        height: 750px;
        top: 10px;
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
        height: auto;
    }

    #cropZone {
        width: 500px;
        height: 750px;
        border: 2px solid red;
        position: relative;
    }


</style>