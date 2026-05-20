@extends('layouts.app')

@include('budget::includes.reports-css')
@include('budget::includes.reports-js')

@section('content')
<div class="lsg-content px-0">
    <div class="row g-3">

        @php
            $availableYears = [2025, 2026];
        @endphp

        <div class="col-12">
            <div class="card budget-card">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2" style="min-height: 52px;">
                        <div>YEAR:</div>

                        <select
                            id="yearSelectorAnnual"
                            class="form-select"
                            style="border: 1px solid #ddd !important; width: 90px; font-size: 18px;"
                            onchange="onAnnualYearChange()"
                        >
                            @foreach($availableYears as $availableYear)
                                <option value="{{ $availableYear }}" @selected((int)$year === (int)$availableYear)>
                                    {{ $availableYear }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Annual monthly evolution</h5>
                    <div class="budget-chart-wrap">
                        <canvas id="budgetAnnualEvolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Annual category distribution</h5>
                    <div class="budget-chart-wrap">
                        <canvas id="budgetAnnualCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card budget-card h-100">
                <div class="card-body table-responsive">
                    <h5 class="mb-3">Monthly table</h5>
                    <table class="table table-hover budget-table mb-0 lsg-datatable">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Income</th>
                                <th class="text-end">Expense</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyRows as $row)
                                <tr>
                                    <td>{{ $row->month_label }}</td>
                                    <td class="text-end">{{ number_format($row->income, 2, '.', ' ') }} €</td>
                                    <td class="text-end">{{ number_format($row->expense, 2, '.', ' ') }} €</td>
                                    <td class="text-end">{{ number_format($row->balance, 2, '.', ' ') }} €</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card budget-card h-100">
                <div class="card-body table-responsive">
                    <h5 class="mb-3">Annual categories</h5>
                    <table class="table table-hover budget-table mb-0 lsg-datatable">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Expense</th>
                                <th class="text-end">Usage %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($annualCategoryRows as $row)
                                <tr>
                                    <td>{{ $row->category_name }}</td>
                                    <td class="text-end">{{ number_format($row->amount, 2, '.', ' ') }} €</td>
                                    <td class="text-end">{{ number_format($row->usage_percent, 2, '.', ' ') }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function onAnnualYearChange() {
        const year = document.getElementById('yearSelectorAnnual').value;
        const url = new URL("{{ route('budget.reports.annual') }}", window.location.origin);
        url.searchParams.set('year', year);
        window.location.href = url.toString();
    }

    budgetChart('budgetAnnualEvolutionChart', {
        type: 'line',
        data: {
            labels: @json($chart_month_labels),
            datasets: [
                { label: 'Income', data: @json($chart_month_income), tension: 0.3 },
                { label: 'Expense', data: @json($chart_month_expense), tension: 0.3 },
                { label: 'Balance', data: @json($chart_month_balance), tension: 0.3 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    budgetChart('budgetAnnualCategoryChart', {
        type: 'doughnut',
        data: {
            labels: @json($chart_annual_category_labels),
            datasets: [{ data: @json($chart_annual_category_amounts) }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endsection
