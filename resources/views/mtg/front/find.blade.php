<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>AR Card Tracker - Captura</title>

    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        video {
            border: 2px solid #444;
            border-radius: 8px;
            width: 80%;
            max-width: 400px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>

    <h1>Identificar Carta MTG</h1>
    <video id="video" autoplay playsinline></video><br>
    <button onclick="captureImage()">📸 Capturar e Identificar</button>

    <script>
        const video = document.getElementById('video');

        // Aceder à câmara
        navigator.mediaDevices.getUserMedia({ video: true })
            .then((stream) => {
                video.srcObject = stream;
            })
            .catch((err) => {
                alert("Erro ao aceder à câmara: " + err);
            });

        function captureImage() {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);

            const base64Image = canvas.toDataURL('image/jpeg');

            fetch('/mtg/find-card-base64', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ base64_image: base64Image })
            })
            .then(response => response.json())
            .then(data => {
                if (data.found) {
                    alert("Carta encontrada: " + data.card.name);
                } else {
                    alert("Carta não encontrada.");
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                alert("Erro ao comunicar com o servidor.");
            });
        }
    </script>

</body>
</html>
