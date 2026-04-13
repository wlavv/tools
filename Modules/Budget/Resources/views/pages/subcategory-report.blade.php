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

<div>
    <div class="row g-3">

        <div class="col-12">
            <div class="card budget-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-3">

                        <div style="width: 150px; height: 52px;">
                            <div style="float: left; margin: 16px 5px 5px 0;">YEAR:</div>
                            <select
                                id="yearSelectorSubcategory"
                                class="form-select"
                                style="border: 1px solid #ddd !important; width: 90px; font-size: 18px; float: left;"
                                onchange="onSubcategoryFilterChange()"
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
                                id="monthSelectorSubcategory"
                                class="form-select"
                                style="border: 1px solid #ddd !important; width: 140px; font-size: 18px; float: left;"
                                onchange="onSubcategoryFilterChange()"
                            >
                                @foreach($months as $monthNumber => $monthLabel)
                                    <option value="{{ $monthNumber }}" @selected((int)$month === (int)$monthNumber)>
                                        {{ $monthLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="width: 320px; height: 52px;">
                            <div style="float: left; margin: 16px 5px 5px 0;">CATEGORY:</div>
                            <select
                                id="categorySelectorSubcategory"
                                class="form-select"
                                style="border: 1px solid #ddd !important; width: 200px; font-size: 18px; float: left;"
                                onchange="onSubcategoryFilterChange()"
                            >
                                <option value="">All categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" @selected($selectedCategory === $category->slug)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Subcategory expense</h5>
                    <div class="budget-chart-wrap">
                        <canvas id="budgetSubcategoryExpenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card budget-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Forecast vs real</h5>
                    <div class="budget-chart-wrap">
                        <canvas id="budgetSubcategoryForecastChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card budget-card">
                <div class="card-body table-responsive">
                    <table class="table table-hover budget-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Subcategory</th>
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
                                    <td>{{ $row->subcategory_name }}</td>
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
    function onSubcategoryFilterChange() {
        const year = document.getElementById('yearSelectorSubcategory').value;
        const month = document.getElementById('monthSelectorSubcategory').value;
        const category = document.getElementById('categorySelectorSubcategory').value;

        const url = new URL("{{ route('budget.reports.subcategory') }}", window.location.origin);
        url.searchParams.set('year', year);
        url.searchParams.set('month', month);

        if (category) {
            url.searchParams.set('category', category);
        } else {
            url.searchParams.delete('category');
        }

        window.location.href = url.toString();
    }

    budgetChart('budgetSubcategoryExpenseChart', {
        type: 'bar',
        data: {
            labels: @json($chart_labels),
            datasets: [
                { label: 'Expense', data: @json($chart_amounts) }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y'
        }
    });

    budgetChart('budgetSubcategoryForecastChart', {
        type: 'bar',
        data: {
            labels: @json($chart_labels),
            datasets: [
                { label: 'Forecast', data: @json($chart_forecast) },
                { label: 'Expense', data: @json($chart_amounts) }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
@endsection