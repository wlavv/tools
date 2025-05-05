<script src="https://aframe.io/releases/1.4.2/aframe.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mind-ar@1.2.2/dist/mindar-image-aframe.prod.js"></script>

<script>
    AFRAME.registerComponent('mytarget', {
        init: function () {
            this.el.addEventListener('targetFound', event => {
                alert("Cloudpost!");
            });
            this.el.addEventListener('targetLost', event => {
                alert("Target lost!!");
            });
        }
    });
</script>
