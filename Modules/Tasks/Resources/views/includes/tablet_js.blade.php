<script>
window.familyPlanner = (function () {
    let bootstrapReadyPromise = null;

    function fitStage() {
        const stage = document.querySelector('.tablet-stage');
        if (!stage) return;

        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const scale = Math.min(viewportWidth / 2160, viewportHeight / 1620);

        stage.style.transform = `scale(${scale})`;
        stage.style.marginLeft = `${Math.max((viewportWidth - (2160 * scale)) / 2, 0)}px`;
        stage.style.marginTop = `${Math.max((viewportHeight - (1620 * scale)) / 2, 0)}px`;
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function getTaskToggleRoute() {
        return window.familyPlannerTaskToggleRoute || '';
    }

    function getSelectedDate() {
        return window.familyPlannerSelectedDate || new Date().toISOString().slice(0, 10);
    }

    function updateClock() {
        const now = new Date();

        const timeEl = document.querySelector('[data-current-time]');
        const dateEl = document.querySelector('[data-current-date]');

        if (timeEl) {
            timeEl.textContent = now.toLocaleTimeString('pt-PT', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }

        if (dateEl) {
            dateEl.textContent = now.toLocaleDateString('pt-PT', {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            });
        }
    }

    async function ensureSweetAlert() {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            return true;
        }

        await new Promise(function (resolve, reject) {
            const existing = document.querySelector('script[data-family-planner-swal]');
            if (existing) {
                existing.addEventListener('load', function () { resolve(true); }, { once: true });
                existing.addEventListener('error', function () { reject(new Error('swal-load-failed')); }, { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            script.async = true;
            script.defer = true;
            script.setAttribute('data-family-planner-swal', '1');
            script.onload = function () { resolve(true); };
            script.onerror = function () { reject(new Error('swal-load-failed')); };
            document.head.appendChild(script);
        });

        return !!(window.Swal && typeof window.Swal.fire === 'function');
    }

    async function ensureBootstrapModal() {
        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
            return true;
        }

        if (bootstrapReadyPromise) {
            return bootstrapReadyPromise;
        }

        bootstrapReadyPromise = new Promise(function (resolve, reject) {
            const existing = document.querySelector('script[data-family-planner-bootstrap]');
            if (existing) {
                existing.addEventListener('load', function () {
                    resolve(!!(window.bootstrap && typeof window.bootstrap.Modal === 'function'));
                }, { once: true });
                existing.addEventListener('error', function () {
                    reject(new Error('bootstrap-load-failed'));
                }, { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js';
            script.async = true;
            script.defer = true;
            script.setAttribute('data-family-planner-bootstrap', '1');
            script.onload = function () {
                resolve(!!(window.bootstrap && typeof window.bootstrap.Modal === 'function'));
            };
            script.onerror = function () {
                reject(new Error('bootstrap-load-failed'));
            };
            document.head.appendChild(script);
        });

        return bootstrapReadyPromise;
    }

    function fallbackShowModal(modalEl) {
        if (!modalEl) return;

        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        modalEl.setAttribute('role', 'dialog');
        document.body.classList.add('modal-open');

        if (!document.querySelector('.modal-backdrop.family-planner-fallback')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show family-planner-fallback';
            document.body.appendChild(backdrop);
        }
    }

    function fallbackHideModal(modalEl) {
        if (!modalEl) return;

        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');

        document.querySelectorAll('.modal-backdrop.family-planner-fallback').forEach(function (el) {
            el.remove();
        });
    }

    async function openModalBySelector(selector) {
        if (!selector) return;

        const modalEl = document.querySelector(selector);
        if (!modalEl) return;

        try {
            await ensureBootstrapModal();
        } catch (e) {}

        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
            const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            return;
        }

        fallbackShowModal(modalEl);
    }

    function bindModalTriggers() {
        document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target]').forEach(function (trigger) {
            if (trigger.dataset.fpModalBound === '1') return;

            trigger.dataset.fpModalBound = '1';
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                openModalBySelector(trigger.getAttribute('data-bs-target'));
            });
        });

        document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (button) {
            if (button.dataset.fpDismissBound === '1') return;

            button.dataset.fpDismissBound = '1';
            button.addEventListener('click', function (event) {
                const modalEl = button.closest('.modal');
                if (!modalEl) return;

                if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                    const instance = window.bootstrap.Modal.getInstance(modalEl);
                    if (instance) {
                        instance.hide();
                        return;
                    }
                }

                event.preventDefault();
                fallbackHideModal(modalEl);
            });
        });

        document.querySelectorAll('.modal').forEach(function (modalEl) {
            if (modalEl.dataset.fpBackdropBound === '1') return;

            modalEl.dataset.fpBackdropBound = '1';
            modalEl.addEventListener('click', function (event) {
                if (event.target === modalEl && (!window.bootstrap || typeof window.bootstrap.Modal !== 'function')) {
                    fallbackHideModal(modalEl);
                }
            });
        });
    }

    async function submitTaskState(taskId, taskDate, responseState) {
        const csrfToken = getCsrfToken();
        const payload = {
            id: taskId,
            done: responseState,
            date: taskDate,
            _token: csrfToken,
        };

        const response = await fetch(getTaskToggleRoute(), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            throw new Error('Falha ao atualizar tarefa');
        }

        return response.json();
    }

    async function showConfirmDialog(options) {
        try {
            await ensureSweetAlert();
        } catch (e) {}

        if (window.Swal && typeof window.Swal.fire === 'function') {
            return window.Swal.fire(options);
        }

        const confirmed = window.confirm(options.text || options.title || 'Confirmar?');
        return Promise.resolve({ isConfirmed: confirmed });
    }

    async function showErrorDialog() {
        try {
            await ensureSweetAlert();
        } catch (e) {}

        if (window.Swal && typeof window.Swal.fire === 'function') {
            return window.Swal.fire({
                icon: 'error',
                title: 'Não foi possível atualizar',
                text: 'Ocorreu um erro ao guardar a resposta da tarefa.',
                confirmButtonText: 'Fechar',
            });
        }

        window.alert('Não foi possível atualizar a tarefa.');
    }

    async function confirmTaskState(button) {
        const taskId = button.dataset.taskId;
        const taskTitle = button.dataset.taskTitle || 'Tarefa';
        const taskDate = button.dataset.taskDate || getSelectedDate();
        const responseState = Number(button.dataset.taskState || 0);

        const isOk = responseState === 1;
        const title = isOk ? 'Marcar como OK?' : 'Marcar como Não OK?';
        const text = isOk
            ? `Confirmar que a tarefa “${taskTitle}” foi realizada?`
            : `Confirmar que a tarefa “${taskTitle}” não foi realizada?`;

        const result = await showConfirmDialog({
            icon: 'question',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: isOk ? 'Sim, marcar OK' : 'Sim, marcar Não OK',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: isOk ? '#4f9b72' : '#c96f6f',
        });

        if (!result || !result.isConfirmed) return;

        try {
            await submitTaskState(taskId, taskDate, responseState);
            window.location.reload();
        } catch (error) {
            await showErrorDialog();
        }
    }

    window.addEventListener('resize', fitStage);
    window.addEventListener('orientationchange', fitStage);

    document.addEventListener('DOMContentLoaded', function () {
        fitStage();
        updateClock();
        setInterval(updateClock, 30000);
        bindModalTriggers();

        ensureBootstrapModal()
            .then(function () {
                bindModalTriggers();
            })
            .catch(function () {});
    });

    return {
        fitStage,
        confirmTaskState,
        updateClock,
        openModalBySelector,
    };
})();
</script>
