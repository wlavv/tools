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
                        <div class="card budget-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-0">Forecast vs Real by Category</h5>
                                        <div class="text-muted small">
                                            Month {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}
                                        </div>
                                    </div>
                                </div>
                                <div class="budget-chart-wrap">
                                    <canvas id="budgetCategoryCompareChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card budget-card h-100">
                            <div class="card-body">
                                <h5 class="mb-3">Top Subcategories</h5>
                                <div class="budget-chart-wrap">
                                    <canvas id="budgetTopSubcategoriesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-3">
                <div class="card budget-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-0">Monthly Evolution</h5>
                                <div class="text-muted small">Income vs expense across {{ $year }}</div>
                            </div>
                        </div>
                        <div class="budget-chart-wrap">
                            <canvas id="budgetMonthlyEvolutionChart"></canvas>
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

    <script>
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