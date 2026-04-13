@once
<script>
function tasksSelectPanel(key){
    document.querySelectorAll('.tasks-member-chip').forEach((chip) => chip.classList.remove('active'));
    document.querySelectorAll('.tasks-panel').forEach((panel) => panel.style.display = 'none');
    const activeChip = document.querySelector('.tasks-member-chip[data-key="'+key+'"]');
    const activePanel = document.querySelector('.tasks-panel[data-key="'+key+'"]');
    if (activeChip) activeChip.classList.add('active');
    if (activePanel) activePanel.style.display = '';
}
function triggerTasksDateModal(){
    const now = new Date();
    const currentMonth = now.getMonth() + 1;
    const currentYear = now.getFullYear();
    Swal.fire({
        title: 'Selecionar mês e ano',
        html: `<select id="swal-mes" class="swal2-input">${[...Array(12).keys()].map(i => `<option value="${i+1}" ${currentMonth === i+1 ? 'selected' : ''}>${new Date(0, i).toLocaleString('pt-PT', { month: 'long' })}</option>`).join('')}</select><select id="swal-ano" class="swal2-input">${[currentYear-1,currentYear,currentYear+1].map(y => `<option value="${y}" ${currentYear === y ? 'selected' : ''}>${y}</option>`).join('')}</select>`,
        showCancelButton: true,
        confirmButtonText: 'Ver calendário',
        cancelButtonText: 'Cancelar',
        preConfirm: () => ({ mes: document.getElementById('swal-mes').value, ano: document.getElementById('swal-ano').value })
    }).then((result) => {
        if (result.isConfirmed) window.location.href = '{{ url('/hr/tasks/calendar') }}/' + result.value.ano + '/' + result.value.mes;
    });
}
async function saveTaskState(button, taskId, doneState, targetDate = null){
    const card = button.closest('.task-card');
    if (!card) return;
    card.classList.add('is-saving');
    try {
        const response = await fetch('{{ route("tasks.updateDone") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                id: taskId,
                type: 1,
                done: doneState,
                date: targetDate,
            })
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Não foi possível atualizar a tarefa.');
        applyTaskState(card, doneState, data.recorded_value || 0);
        updateMemberProgress(card.dataset.memberKey, card, doneState, data.recorded_value || 0);
    } catch (error) {
        Swal.fire('Erro', error.message || 'Erro ao atualizar tarefa.', 'error');
    } finally {
        card.classList.remove('is-saving');
    }
}
function applyTaskState(card, doneState, value){
    const isDone = parseInt(doneState, 10) === 1;
    const successBtn = card.querySelector('.task-toggle-btn.is-success');
    const dangerBtn = card.querySelector('.task-toggle-btn.is-danger');
    const badge = card.querySelector('.task-state-badge');
    const valuePill = card.querySelector('[class*="value-pill-"]');

    card.classList.toggle('is-done', isDone);
    card.classList.toggle('is-pending', !isDone);
    if (successBtn) successBtn.classList.toggle('active', isDone);
    if (dangerBtn) dangerBtn.classList.toggle('active', !isDone);
    if (badge) {
        const penalty = card.dataset.countsForCompletion === '0';
        badge.textContent = penalty ? (isDone ? 'Aplicada' : 'Por aplicar') : (isDone ? 'Concluída' : 'Pendente');
        badge.classList.toggle('is-done', isDone);
        badge.classList.toggle('is-pending', !isDone);
    }
    if (valuePill) {
        valuePill.textContent = 'Registado ' + Number(value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
    }
}
function updateMemberProgress(memberKey, card, doneState, recordedValue){
    if (!window.tasksPageStats || !window.tasksPageStats[memberKey]) return;
    const stats = window.tasksPageStats[memberKey];
    const previousDone = card.dataset.doneState ? parseInt(card.dataset.doneState, 10) : 0;
    const nextDone = parseInt(doneState, 10);
    const countsForCompletion = card.dataset.countsForCompletion === '1';
    const baseValue = parseFloat(card.dataset.baseValue || '0');
    const valueMode = card.dataset.valueMode || 'add';

    if (countsForCompletion && previousDone !== nextDone) {
        stats.month_done += (nextDone - previousDone);
        stats.today_done += (nextDone - previousDone);
    }

    const previousValue = previousDone === 1 ? (valueMode === 'subtract' ? -baseValue : baseValue) : 0;
    const nextValue = nextDone === 1 ? (valueMode === 'subtract' ? -baseValue : baseValue) : 0;
    stats.month_value = Number(stats.month_value || 0) + (nextValue - previousValue);
    card.dataset.doneState = String(nextDone);

    const percent = stats.month_total > 0 ? ((stats.month_done / stats.month_total) * 100) : 0;
    const chip = document.querySelector('.tasks-member-chip[data-key="'+memberKey+'"]');
    if (chip) {
        const chipProgress = chip.querySelector('.member-chip-progress');
        const chipPercent = chip.querySelector('.member-chip-percent');
        if (chipProgress) chipProgress.textContent = stats.month_done + ' / ' + stats.month_total;
        if (chipPercent) chipPercent.textContent = Math.round(percent) + '%';
    }

    const monthLabel = document.querySelector('.current-member-month-label-' + memberKey);
    const percentLabel = document.querySelector('.current-member-percent-' + memberKey);
    const todayLabel = document.querySelector('.current-member-today-' + memberKey);
    const progressBar = document.querySelector('.current-member-progress-bar-' + memberKey);
    const valueLabel = document.querySelector('.current-member-value-' + memberKey);
    if (monthLabel) monthLabel.textContent = stats.month_done + ' de ' + stats.month_total + ' tarefas';
    if (percentLabel) percentLabel.textContent = percent.toLocaleString('pt-PT', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + '%';
    if (todayLabel) todayLabel.textContent = stats.today_done + ' de ' + stats.today_total;
    if (progressBar) progressBar.style.width = percent + '%';
    if (valueLabel) valueLabel.textContent = Number(stats.month_value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';

    updateRewardBoxes(memberKey, percent, stats.month_done, stats.month_total);
}
function updateRewardBoxes(memberKey, percent, monthDone, monthTotal){
    const stats = window.tasksPageStats[memberKey];
    if (!stats || !Array.isArray(stats.rewards)) return;
    let achieved = null, next = null, remaining = null;
    stats.rewards.forEach((reward) => {
        if (percent >= parseFloat(reward.threshold_percent || 0)) achieved = reward;
        else if (!next) {
            next = reward;
            remaining = Math.max(Math.ceil((monthTotal * parseFloat(reward.threshold_percent || 0)) / 100) - monthDone, 0);
        }
    });
    const achievedTitle = document.querySelector('.current-member-reward-achieved-' + memberKey);
    const nextTitle = document.querySelector('.current-member-reward-next-' + memberKey);
    const nextRemaining = document.querySelector('.current-member-reward-remaining-' + memberKey);
    if (achievedTitle) achievedTitle.textContent = 'Prémio: ' + (achieved ? achieved.name : '—');
    if (nextTitle) nextTitle.textContent = 'Próximo: ' + (next ? next.name : 'Máximo');
    if (nextRemaining) nextRemaining.textContent = next ? ('Faltam ' + remaining + ' tarefa(s)') : 'Objetivo máximo concluído';
}
function confirmDeactivate(formId){
    Swal.fire({title:'Confirmar',text:'Pretendes desativar este registo?',icon:'warning',showCancelButton:true,confirmButtonText:'Sim',cancelButtonText:'Cancelar'}).then((result) => {
        if(result.isConfirmed){ document.getElementById(formId).submit(); }
    });
}
function calendarSelectMember(dayKey, memberKey){
    document.querySelectorAll('[data-day="'+dayKey+'"][data-role="calendar-tab"]').forEach((el)=>el.classList.remove('active'));
    document.querySelectorAll('[data-day="'+dayKey+'"][data-role="calendar-panel"]').forEach((el)=>el.classList.remove('active'));
    const tab = document.querySelector('[data-day="'+dayKey+'"][data-member="'+memberKey+'"][data-role="calendar-tab"]');
    const panel = document.querySelector('[data-day="'+dayKey+'"][data-member="'+memberKey+'"][data-role="calendar-panel"]');
    if (tab) tab.classList.add('active');
    if (panel) panel.classList.add('active');
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.task-card').forEach((card) => {
        const activeBtn = card.querySelector('.task-toggle-btn.active');
        card.dataset.doneState = activeBtn && activeBtn.classList.contains('is-success') ? '1' : '0';
    });
});
</script>
@endonce
