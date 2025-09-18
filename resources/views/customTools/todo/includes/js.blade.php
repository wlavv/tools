<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
function makeSortable(el) {
    new Sortable(el, {
        group: 'shared',
        handle: '.drag-handle',
        animation: 150,
        fallbackOnBody: true,
        swapThreshold: 0.65,
        onEnd(evt) { 
            updateDropPlaceholders(evt.to);
            updateDropPlaceholders(evt.from);
            saveOrder(); 
        },
        onAdd(evt) {
            // Remove placeholder se existir
            const placeholders = evt.to.querySelectorAll('.drop-placeholder');
            placeholders.forEach(ph => ph.remove());
            updateDropPlaceholders(evt.to);
            updateDropPlaceholders(evt.from);
            saveOrder();
        }
    });

    // Aplica recursivamente para sublistas
    el.querySelectorAll('.task-list').forEach(subList => {
        if (!subList.classList.contains('sortable-applied')) {
            subList.classList.add('sortable-applied');
            makeSortable(subList);
        }
        
        updateDropPlaceholders(subList);
    });
}

// Atualiza placeholders
function updateDropPlaceholders(list) {
    const hasItems = Array.from(list.children).some(li => li.classList.contains('task-item'));
    const placeholder = list.querySelector('.drop-placeholder');

    if (!hasItems && !placeholder) {
        const li = document.createElement('li');
        li.className = 'drop-placeholder';
        li.textContent = 'Drop here';
        list.appendChild(li);
    } else if (hasItems && placeholder) {
        placeholder.remove();
    }
}

function saveOrder() {
    const data1 = buildTaskTree(document.getElementById('taskRoot1'));
    console.log("Nova hierarquia Lista 1:", JSON.stringify(data1, null, 2));

    // AJAX para salvar posição no servidor
    $.ajax({
        url: "{{ route('todo.saveOrder') }}", // cria esta rota no Laravel
        type: "POST",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { order: data1 },
        success: function(response) {
            console.log("Ordem salva com sucesso!");
        },
        error: function(xhr, status, error) {
            console.error("Erro ao salvar ordem:", error);
        }
    });
}


function buildTaskTree(ul) {
    let tasks = [];
    ul.querySelectorAll(':scope > .task-item').forEach(li => {
        let task = { id: li.dataset.id, children: [] };
        let subList = li.querySelector(':scope > .task-list');
        if(subList) task.children = buildTaskTree(subList);
        tasks.push(task);
    });
    return tasks;
}

function updateDropPlaceholders(list) {
    const hasItems = Array.from(list.children).some(li => li.classList.contains('task-item'));
    let placeholder = list.querySelector('.drop-placeholder');

    if (!hasItems && !placeholder) {
        placeholder = document.createElement('li');
        placeholder.className = 'drop-placeholder';
        placeholder.textContent = 'Drop here';
        list.appendChild(placeholder);
    } else if (hasItems && placeholder) {
        placeholder.remove();
    }
}


document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('taskRoot1')) makeSortable(document.getElementById('taskRoot1'));
});

function addMainTask() {
    let modal = new bootstrap.Modal(document.getElementById('taskModal'));
    $('#deleteTaskBtn').hide();
    document.getElementById("taskForm").reset();
    modal.show();
}

function addSubTask(id_parent) {
    $('#id_parent').val(id_parent);
    let modal = new bootstrap.Modal(document.getElementById('taskModal'));
    $('#deleteTaskBtn').hide();
    document.getElementById("taskForm").reset();
    modal.show();
}

function editTask(el) {
    const li = el.closest(".task-item");
    if (!li) return;

    $("#taskModalLabel").text("Edit Task");
    $("#task_id").val(li.dataset.id);
    $("#id_parent").val(li.dataset.id_parent);
    $("#title").val(li.dataset.title);
    $("#start_date").val(li.dataset.start_date);
    $("#expected_time").val(li.dataset.expected_time);
    $("#comment").val(li.dataset.comment);
    $("#id_task").val(li.dataset.id);

    // Verifica se tem filhos
    const hasChildren = li.querySelector('.task-list > .task-item') !== null;
    if(hasChildren) {
        $('#deleteTaskBtn').hide(); // não mostra se tiver sub-tarefas
    } else {
        $('#deleteTaskBtn').show(); // mostra se for folha
    }
    
    $('.modal-footer').css('display', 'block');
    $("#taskModal").modal("show");
}

document.addEventListener("submit", function(e){
    const form = e.target.closest("#taskForm");
    if (!form) return; // não é o form que queremos
    e.preventDefault();

    let formData = new FormData(form);
    $.ajax({
        url: '{{route("todo.store")}}',
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) {
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error("Erro:", error);
        }
    });
});

function removeTask(el) {
    
    id = $('#id_task').val();
    
    Swal.fire({
        title: 'ARE YOU SURE?',
        text: "This task will be permanently removed!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'YES, REMOVE!',
        cancelButtonText: 'CANCEL'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/administration/groupStructure/project/todo/destroy/' + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.status === 'success') {
                        document.getElementById('todoTask-' + id).remove();
                        Swal.fire('Removido!', 'A tarefa foi removida.', 'success');
                        $("#taskModal").modal("hide");
                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao remover', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Erro', 'Erro ao remover', 'error');
                }
            });
        }
    });
}

function setTaskAsDone(id) {
    
    Swal.fire({
        title: 'ARE YOU SURE?',
        text: "This task will be set as done!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'YES, set as done!',
        cancelButtonText: 'CANCEL'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/administration/groupStructure/todo/done/' + id,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.status === 'success') {
                        document.getElementById('todoTask-' + id).remove();
                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao remover', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Erro', 'Erro ao remover', 'error');
                }
            });
        }
    });
}

function allowEdit(){
    
    $('.drag-handle').css('display', 'block');
    $('.task-actions button').css('display', 'block');
    $('.drop-zone').css('display', 'block');
    $('.task-actions').css('display', 'block');
    $('.drag-handle').css('display', 'block');
    $('.toHide').css('display', 'block');
    $('.task-list.sortable-applied .drop-placeholder').css('display', 'block');
    $('.task-list.sortable-applied').css('border', '2px dashed #ddd');

}

</script>
