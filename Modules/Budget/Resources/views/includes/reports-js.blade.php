<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function budgetChart(canvasId, config) {
        const el = document.getElementById(canvasId);
        if (!el) return null;
        return new Chart(el.getContext('2d'), config);
    }
</script>
