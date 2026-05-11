<style>

#incomeContainer{ display: none; }
#expenseContainer{ display: none; }
#statsContainer{ display: none; }


.shortSpacing > :not(caption) > * > * {
  padding: 2px 5px !important;
}

.casaContent{ display: none; }
.escolaContent{ display: none; }
.autoContent{ display: none; }
.bemestarContent{ display: none; }
.servicosContent{ display: none; }
.alimentacaoContent{ display: none; }
.extrasContent{ display: none; }
.potesContent{ display: none; }

.casa_content{ display: none; }
.escola_content{ display: none; }
.auto_content{ display: none; }
.bemestar_content{ display: none; }
.servicos_content{ display: none; }
.alimentacao_content{ display: none; }
.extras_content{ display: none; }
.potes_content{ display: none; }

.potes_totals_Content_stats{ display: none; }

td.alert-danger {
    background-color: #f8d7da !important;
    color: #842029 !important;
    border-radius: 0;
}

td.alert-success {
    background-color: #d1e7dd !important;
    color: #0f5132 !important; 
    border-radius: 0;
}

table td:first-child {
    width: 120px !important;
    max-width: 120px !important;
    min-width: 120px !important;
}

.budget-card {
    margin-bottom: 0;
}

.budget-chart-wrap {
    position: relative;
    min-height: 320px;
}

.row.g-3 > [class*="col-"] {
    display: flex;
}

.row.g-3 > [class*="col-"] > .card {
    width: 100%;
}

.budget-card {
    margin-bottom: 0;
}

.budget-chart-wrap {
    position: relative;
    min-height: 320px;
}

.card.bg-success .card-body,
.card.bg-success .table,
.card.bg-success .table td,
.card.bg-success .table th,
.card.bg-success .table tr,
.card.bg-success .table-striped > tbody > tr:nth-of-type(odd) > * {
    background: transparent !important;
    color: inherit !important;
}

.card.bg-success {
    background-color: var(--bs-success) !important;
}

.card.bg-success .card-body {
    background: transparent !important;
}

.card.bg-success .table,
.card.bg-success .table > :not(caption) > * > * {
    background-color: transparent !important;
    color: inherit !important;
}

.panel-table-wrapper {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;

    -webkit-overflow-scrolling: touch;
}

/* opcional: melhora visual */
.panel-table-wrapper::-webkit-scrollbar {
    height: 6px;
}

.panel-table-wrapper::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 10px;
}

body.theme-dark .panel-table-wrapper::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.25);
}

</style>
