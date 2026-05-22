<script>
window.calendarModuleLoaded = true;
</script>
<script>
(function () {
    function openCalendarContextCreateModal() {
        var modal = document.getElementById('calendarContextCreateModal');

        if (!modal) {
            return false;
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
        } else if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery(modal).modal('show');
        } else {
            modal.classList.add('show');
            modal.style.display = 'block';
            modal.removeAttribute('aria-hidden');
            modal.setAttribute('aria-modal', 'true');
            document.body.classList.add('modal-open');

            if (!document.querySelector('.modal-backdrop.calendar-modal-backdrop')) {
                var backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show calendar-modal-backdrop';
                document.body.appendChild(backdrop);
            }
        }

        if (window.location.hash === '#calendarContextCreateModal') {
            history.replaceState(null, '', window.location.pathname + window.location.search);
        }

        return true;
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('.calendar-open-context-modal, a[href$="#calendarContextCreateModal"]');

        if (!trigger) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        openCalendarContextCreateModal();
    }, true);

    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-bs-dismiss="modal"], .btn-close')) {
            return;
        }

        var modal = event.target.closest('.modal');

        if (!modal || window.bootstrap || (window.jQuery && typeof window.jQuery.fn.modal === 'function')) {
            return;
        }

        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.calendar-modal-backdrop').forEach(function (backdrop) {
            backdrop.remove();
        });
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        if (window.location.hash === '#calendarContextCreateModal') {
            openCalendarContextCreateModal();
        }
    });
})();
</script>
