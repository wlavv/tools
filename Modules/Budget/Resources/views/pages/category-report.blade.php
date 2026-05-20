@extends('layouts.app')

@include('budget::includes.reports-css')
@include('budget::includes.reports-js')

@section('content')
@php
    $availableYears = [2025, 2026];

    $months = [
        1 => 'JANUARY',
        2 => 'FEBRUARY',
        3 => 'MARCH',
        4 => 'APRIL',
        5 => 'MAY',
        6 => 'JUNE',
        7 => 'JULY',
        8 => 'AUGUST',
        9 => 'SEPTEMBER',
        10 => 'OCTOBER',
        11 => 'NOVEMBER',
        12 => 'DECEMBER',
    ];
@endphp

<div class="lsg-content px-0">
    <div class="row g-3">

        <div class="col-12">
            <div class="card budget-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-3">

                        <div style="width: 150px; height: 52px;">
                            <div style="float: left; margin: 16px 5px 5px 0;">YEAR:</div>
                            <select
                                id="yearSelectorCategory"
                                class="form-select"
                                style="border: 1px solid #ddd !important; width: 90px; font-size: 18px; float: left;"
                                onchange="onCategoryFilterChange()"
                            >
                                @foreach($availableYears as $availableYear)
                                    <option value="{{ $availableYear }}" @selected((int)$year === (int)$availableYear)>
                                        {{ $availableYear }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="width: 220px; height: 52px;">
                            <div style="float: left; margin: 16px 5px 5px 0;">MONTH:</div>
                            <select
                                id="monthSelectorCategory"
                                class="form-select"
                                style="border: 1px solid #ddd !important; width: 140px; font-size: 18px; float: left;"
                                onchange="onCategoryFilterChange()"
                            >
                                @foreach($months as $monthNumber => $monthLabel)
                                    <option value="{{ $monthNumber }}" @selected((int)$month === (int)$monthNumber)>
                                        {{ $monthLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <div class="budget-kpi-title">Forecast</div>
                    <div class="budget-kpi-value">{{ number_format($summary['forecast'], 2, '.', ' ') }} €</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <div class="budget-kpi-title">Expense</div>
                    <div class="budget-kpi-value">{{ number_format($summary['expense'], 2, '.', ' ') }} €</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <div class="budget-kpi-title">Difference</div>
                    <div class="budget-kpi-value">{{ number_format($summary['difference'], 2, '.', ' ') }} €</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <div class="budget-kpi-title">Usage</div>
                    <div class="budget-kpi-value">{{ number_format($summary['usage_percent'], 2, '.', ' ') }}%</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Category comparison</h5>
                    <div class="budget-chart-wrap">
                        <canvas id="budgetCategoryReportChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Category usage</h5>
                    <div class="budget-chart-wrap">
                        <canvas id="budgetCategoryUsageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card budget-card">
                <div class="card-body table-responsive">
                    <h5 class="mb-3">Category table</h5>
                    <table class="table table-hover budget-table align-middle mb-0 lsg-datatable">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Forecast</th>
                                <th class="text-end">Expense</th>
                                <th class="text-end">Difference</th>
                                <th class="text-end">Usage %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{ $row->category_name }}</td>
                                    <td class="text-end">{{ number_format($row->forecast, 2, '.', ' ') }} €</td>
                                    <td class="text-end">{{ number_format($row->amount, 2, '.', ' ') }} €</td>
                                    <td class="text-end">{{ number_format($row->difference, 2, '.', ' ') }} €</td>
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
    function onCategoryFilterChange() {
        const year = document.getElementById('yearSelectorCategory').value;
        const month = document.getElementById('monthSelectorCategory').value;

        const url = new URL("{{ route('budget.reports.category') }}", window.location.origin);
        url.searchParams.set('year', year);
        url.searchParams.set('month', month);

        window.location.href = url.toString();
    }

    budgetChart('budgetCategoryReportChart', {
        type: 'bar',
        data: {
            labels: @json($chart_labels),
            datasets: [
                { label: 'Forecast', data: @json($chart_forecast) },
                { label: 'Expense', data: @json($chart_expense) }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    budgetChart('budgetCategoryUsageChart', {
        type: 'pie',
        data: {
            labels: @json($chart_labels),
            datasets: [
                { data: @json($chart_expense) }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
@endsection
