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



@include("customTools.budget.includes.panel", ['title' => 'CASA',                           'slug' => 'casa',       'data' => []])
        @include("customTools.budget.includes.panel", ['title' => 'ESCOLA',                         'slug' => 'escola',     'data' => []])
        @include("customTools.budget.includes.panel", ['title' => 'AUTOMÓVEIS',                     'slug' => 'auto',       'data' => []])
        @include("customTools.budget.includes.panel", ['title' => 'CUIDADOS PESSOAIS / BEM ESTAR',  'slug' => 'bemestar',   'data' => []])
        @include("customTools.budget.includes.panel", ['title' => 'SERVIÇOS',                       'slug' => 'servicos',   'data' => []])
        @include("customTools.budget.includes.panel", ['title' => 'ALIMENTAÇÃO',                    'slug' => 'alimentacao','data' => []])
        @include("customTools.budget.includes.panel", ['title' => 'EXTRAS',                         'slug' => 'extras',     'data' => []])
        @include("customTools.budget.includes.panel", ['title' => 'POTES',                          'slug' => 'potes',      'data' => []])
</style>