@extends('layouts.app')

@include('budget::includes.js')
@include('budget::includes.css')
@include('budget::includes.reports-css')
@include('budget::includes.reports-js')

@section('content')
    <div>
        <div class="row">
            @include('budget::includes.kpi')

            <div class="col-12 mb-3">
                <div class="row">
                    <div class="col-lg-8 mb-3 mb-lg-0">
<div class="card budget-card h-100 budget-collapsible">
    <div class="card-body">
        <button type="button"
                class="budget-card-toggle"
                data-budget-collapse="budgetCategoryComparePanel">
            <div>
                <h5 class="mb-0">Forecast vs Real by Category</h5>
                <div class="text-muted small">
                    Month {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}
                </div>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </button>

        <div id="budgetCategoryComparePanel" class="budget-collapse-panel">
            <div class="budget-chart-wrap">
                <canvas id="budgetCategoryCompareChart"></canvas>
            </div>
        </div>
    </div>
</div>
                    </div>

                    <div class="col-lg-4">
<div class="card budget-card h-100 budget-collapsible">
    <div class="card-body">
        <button type="button"
                class="budget-card-toggle"
                data-budget-collapse="budgetTopSubcategoriesPanel">
            <div>
                <h5 class="mb-0">Top Subcategories</h5>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </button>

        <div id="budgetTopSubcategoriesPanel" class="budget-collapse-panel">
            <div class="budget-chart-wrap">
                <canvas id="budgetTopSubcategoriesChart"></canvas>
            </div>
        </div>
    </div>
</div>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-3">
<div class="card budget-card budget-collapsible">
    <div class="card-body">
        <button type="button"
                class="budget-card-toggle"
                data-budget-collapse="budgetMonthlyEvolutionPanel">
            <div>
                <h5 class="mb-0">Monthly Evolution</h5>
                <div class="text-muted small">Income vs expense across {{ $year }}</div>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </button>

        <div id="budgetMonthlyEvolutionPanel" class="budget-collapse-panel">
            <div class="budget-chart-wrap">
                <canvas id="budgetMonthlyEvolutionChart"></canvas>
            </div>
        </div>
    </div>
</div>
            </div>

            @foreach($budget->expense as $row)
                @include('budget::includes.panel', [
                    'title' => $row->name,
                    'slug' => $row->slug,
                    'forecast' => $row->forecast,
                    'row' => $row
                ])
            @endforeach

            @include('budget::includes.footer')
        </div>
    </div>

    <style>

.budget-card-toggle {
    width: 100%;
    padding: 0;
    border: 0;
    background: transparent;
    color: inherit;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;

    text-align: left;
}

.budget-card-toggle i {
    transition: transform .2s ease;
}

.budget-collapsible.is-open .budget-card-toggle i {
    transform: rotate(180deg);
}

.budget-collapse-panel {
    margin-top: 16px;
}

/* Desktop: sempre aberto */
@media (min-width: 768px) {
    .budget-collapse-panel {
        display: block !important;
    }

    .budget-card-toggle {
        cursor: default;
    }

    .budget-card-toggle i {
        display: none;
    }
}

/* Mobile: fechado por defeito */
@media (max-width: 767.98px) {
    .budget-collapse-panel {
        display: none;
    }

    .budget-collapsible.is-open .budget-collapse-panel {
        display: block;
    }

    .budget-card-toggle {
        cursor: pointer;
    }
}

    </style>

    <script>

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-budget-collapse]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (window.innerWidth >= 768) {
                return;
            }

            const card = button.closest('.budget-collapsible');

            if (!card) {
                return;
            }

            card.classList.toggle('is-open');
        });
    });
});
        
        budgetChart('budgetCategoryCompareChart', {
            type: 'bar',
            data: {
                labels: @json($chart_category_labels),
                datasets: [
                    { label: 'Forecast', data: @json($chart_category_forecast) },
                    { label: 'Expense', data: @json($chart_category_expense) }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        budgetChart('budgetTopSubcategoriesChart', {
            type: 'doughnut',
            data: {
                labels: @json($chart_top_subcategory_labels),
                datasets: [
                    { data: @json($chart_top_subcategory_amounts) }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        budgetChart('budgetMonthlyEvolutionChart', {
            type: 'line',
            data: {
                labels: @json($chart_month_labels),
                datasets: [
                    { label: 'Income', data: @json($chart_month_income), tension: 0.3 },
                    { label: 'Expense', data: @json($chart_month_expense), tension: 0.3 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
@endsection