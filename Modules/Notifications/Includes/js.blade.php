<script>
(function(){
  function renderItems(root, data){
    const badge = root.querySelector('[data-notifications-badge]');
    const list = root.querySelector('[data-notifications-list]');
    if (badge) {
      badge.textContent = data.unread || 0;
      badge.classList.toggle('d-none', !(data.unread > 0));
    }
    if (!list) return;
    const items = data.items || [];
    if (!items.length) {
      list.innerHTML = '<div class="p-3 text-muted small">Sem notificações.</div>';
      return;
    }
    list.innerHTML = items.map(function(item){
      return '<a class="list-group-item list-group-item-action ' + (!item.read ? 'is-unread' : '') + '" href="' + item.url + '">' +
             '<div class="d-flex justify-content-between gap-2"><div class="fw-semibold text-truncate">' + item.title + '</div><div class="small text-muted">' + (item.created_at || '') + '</div></div>' +
             '<div class="small text-muted mt-1">' + (item.message || '') + '</div></a>';
    }).join('') + '<div class="p-2 border-top text-center"><a class="btn btn-sm btn-outline-primary" href="' + data.index_url + '">Ver todas</a></div>';
  }
  function boot(root){
    const url = root.dataset.url;
    const interval = (parseInt(root.dataset.polling || '30', 10) || 30) * 1000;
    const fetchData = function(){
      fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(data){ renderItems(root, data); })
        .catch(function(){});
    };
    fetchData();
    setInterval(fetchData, interval);
  }
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('[data-notifications-dropdown]').forEach(boot);
  });
})();
</script>
