<style>
/* TASK ROW: FORCE SINGLE LINE */

.fp-task-list{
    width:100%;
    min-width:0;
    display:flex;
    flex-direction:column;
    gap:8px;
}

.fp-task-row{
    display:flex !important;
    align-items:center !important;
    gap:10px !important;
    width:100% !important;
    min-width:0 !important;
    margin:0 !important;
    padding:8px 10px !important;
    border-radius:5px !important;
    background:rgba(255,255,255,.18);
    border:1px solid rgba(255,255,255,.26);
    text-decoration:none;
}

.fp-task-row.is-done{
    opacity:.9;
}

.fp-task-checkbox{
    flex:0 0 22px !important;
    width:22px !important;
    height:22px !important;
    margin:0 !important;
}

.fp-task-checkmark{
    flex:0 0 22px !important;
    width:22px !important;
    height:22px !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
}

.fp-task-image{
    flex:0 0 50px !important;
    width:50px !important;
    height:50px !important;
    display:block !important;
    overflow:hidden !important;
    border-radius:5px !important;
    border:1px solid rgba(255,255,255,.34);
    background:rgba(255,255,255,.22);
}

.fp-task-image img{
    width:100% !important;
    height:100% !important;
    display:block !important;
    object-fit:cover !important;
}

.fp-task-title{
    flex:1 1 auto !important;
    min-width:0 !important;
    display:block !important;
    width:auto !important;
    margin:0 !important;
    white-space:nowrap !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    line-height:1.2 !important;
}

.fp-task-title.is-done{
    text-decoration:line-through;
    opacity:.75;
}

/* Kill old float-based rules */
.fp-task-row *,
.fp-task-image,
.fp-task-image img,
.fp-task-title{
    float:none !important;
}
</style>
