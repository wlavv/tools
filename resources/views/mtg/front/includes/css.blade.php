<style>
    body {
        background: black;
        color: white;
        font-family: Arial, sans-serif;
        margin: 0;
        overflow: hidden;
    }

    /** 
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
        height: auto;  
    }

    .video-crop-container {
        display: flex;
        gap: 20px;
        margin-top: 50px;
        margin-left: 10px;
    }

    #videoContainer {
        position: relative;
        width: 1300px;
        height: 1500px;
    }


    #videoContainer video {
        width: 500px;
        height: auto;
        border: 2px solid white;
    }

    #cropZone {
        width: 500px;
        height: 750px;
        border: 2px solid red;
        background-color: rgba(0, 0, 0, 0.2);
    }

**/


    .video-crop-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

#videoContainer {
    position: relative;
    width: 1200px;
    height: 900px;
    margin: 0 auto;
    border: 1px solid #ddd;
}

#videoContainer video,
#videoContainer canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 1200px;
    height: 900px;
}

#info {
    margin: 20px 0;
    font-weight: bold;
    text-align: center;
    font-family: sans-serif;
    font-size: 1.2rem;
}

#cropZone {
    margin-top: 20px;
    text-align: center;
}

#cropZone img {
    max-width: 100%;
    height: auto;
}


</style>