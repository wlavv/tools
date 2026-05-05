<script>
(function(){
    document.querySelectorAll('[data-pm-ajax-details]').forEach(function(details){
        details.addEventListener('toggle', function(){
            if (!details.open || details.dataset.loaded === '1') return;
            const target = details.querySelector('[data-pm-ajax-target]');
            if (!target) return;
            fetch(details.dataset.url, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(function(response){ return response.text(); })
                .then(function(html){ target.innerHTML = html; details.dataset.loaded = '1'; })
                .catch(function(){ target.innerHTML = '<div class="pm-empty">Não foi possível carregar os dados.</div>'; });
        });
    });
})();
</script>
