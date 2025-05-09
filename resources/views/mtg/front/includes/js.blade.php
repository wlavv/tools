<script src="https://aframe.io/releases/1.4.2/aframe.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mind-ar@1.2.2/dist/mindar-image-aframe.prod.js"></script>

<script>

    const glassPanel = document.getElementById('multi-panel');
    const mytarget = document.getElementById('mytarget');

    AFRAME.registerComponent('mytarget', {

        init: function () {
            this.el.addEventListener('targetFound', event => {
                console.log("Target found!!");
            });
            this.el.addEventListener('targetLost', event => {
                console.log("Target lost!!");
            });
        }

    });

    function loadCardDetails(edition, collectorNumber) {

        alert(edition);
        alert(collectorNumber);
        
        $.ajax({
            url: "{{ route('mtg.postCardDetail') }}",
            method: 'POST',
            data: {
                edition: edition,
                collector_number: collectorNumber,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('.cardContainer').html(response);
            },
            error: function(xhr) {
                console.error("Erro ao carregar os detalhes da carta:", xhr);
                $('.cardContainer').html('<p>Erro ao carregar os dados.</p>');
            }
        });
    }


    mytarget.addEventListener('targetFound', () => {
        glassPanel.classList.remove('hidden-panel');
        loadCardDetails('mir', 280);
    });

    mytarget.addEventListener('targetLost', () => {
        glassPanel.classList.add('hidden-panel');
    });


</script>
