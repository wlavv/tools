<style>


.task-item {
    width: 100%;
    box-sizing: border-box;
    padding: 8px 10px;
    border: 1px solid #ccc;
    margin-bottom: 5px;
    background: #ddd;
    display: flex;
    flex-direction: column;
}

.task-main {
    display: flex;
    align-items: left;
}

.drag-handle {
    cursor: grab;
    margin-right: 10px;
    color: dodgerblue;
}

.task-title {
    flex-grow: 1;
    cursor: pointer;
    text-align: left;
}

.task-actions {
    margin-right: 10px;
}

.task-list {
    list-style: none;
    padding-top: 5px;
    padding-left: 10px;
    padding-right: 10px;
    margin-top: 5px;
    min-height: 20px;
    width: 100%;
}

.task-list.sortable-applied {
    border: 2px dashed #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
    position: relative;
}

.drop-placeholder {
    padding: 0px;
    text-align: left;
    color: #888;
    font-style: italic;
    height: 20px;
}

/* Subtarefas reais */
.task-list > .task-item {
    margin: 5px 0;
    background: #fff;
    padding-left: 15px;
}


.toHide{ display: none; }
.drop-placeholder{ display: none; }
</style>
