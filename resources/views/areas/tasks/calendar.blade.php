@extends('layouts.app')

@section('content')

@php
    $monthlyTotals = [];
@endphp

<div class="bg-gray-100 rounded-lg shadow mb-6 p-4">
    <h2 class="text-xl font-bold mb-4 text-center">Calendário de Tarefas - {{ $month }}/{{ $year }}</h2>
        @foreach ($calendar as $day => $users)
            <div class="m-5 bg-white rounded-md shadow-sm">
                <h3 class="text-lg font-semibold p-1 text-center" style="margin: 5px; border-bottom: 1px solid #bbb;">{{ \Carbon\Carbon::parse($day)->format('d/m/Y') }}</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="row">
                        @foreach ($users as $user => $tasks)
                            <div class="col-lg-3">
                                <div class="p-3">
                                    <h4 class="text-md font-bold text-blue-700 mb-2">{{ $user }}</h4>
                                    <ul class="space-y-1" style="padding-left: 0;">
                                        @foreach ($tasks as $task)
                                            <li class="flex justify-between items-center text-sm" style="list-style: none;">
                                                <span class="{{ $task['done'] ? 'text-green-600' : 'text-red-600' }}"> 
                                                    {{ $task['done'] ? '✅' : '❌' }} 
                                                </span>
                                                <span>{{ $task['name'] }}</span>
                                                <span class="flex items-center gap-2" style="float: right;">
                                                    @if ($task['type'] == 2)
                                                        <span class="text-gray-500 text-xs">{{ number_format($task['value'], 2) }} €</span>
                                                    @endif
                                                </span>
                                            </li>
                                            @php $type = $task['type']; @endphp
                                        @endforeach
                                    </ul>

                                    {{-- Total do utilizador (type = 2) --}}
                                    @php
                                        $total = collect($tasks)
                                                    ->where('type', 2)
                                                    ->sum('value');
                                    @endphp
                                    
                                    @php
                                        $dailyTotal = 0;

                                        foreach ($tasks as $taskItem) {
                                            if ($taskItem['type'] == 2) {
                                                $dailyTotal += $taskItem['value'];

                                                if (!isset($monthlyTotals[$user])) {
                                                    $monthlyTotals[$user] = 0;
                                                }

                                                $monthlyTotals[$user] += $taskItem['value'];
                                            }
                                        }

                                    @endphp

                                    @if ($type == 2)
                                        <div class="mt-3 font-semibold text-green-700">
                                            <span style="font-size: bolder; font-size: 30px;">Total:</span> <span style="font-size: bolder; font-size: 30px;float: right;">{{ number_format($dailyTotal, 2) }} €</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <div class="bg-white rounded-lg shadow p-4 mt-6">
            <h3 class="text-xl font-bold mb-4 text-center text-indigo-700">Total do Mês por Utilizador</h3>
            <div class="row">
            <div class="col-lg-3"></div>
                @foreach ($monthlyTotals as $user => $total)
                    <div class="col-lg-3">
                        <div class="mt-3 font-semibold text-green-700 text-center">
                            <span class="font-semibold" style="font-size: bolder; font-size: 30px;">{{ $user }}: </span>
                            @if( $total == 0)
                            <span style="font-size: bolder; font-size: 30px;color: dodgerblue;">{{ number_format($total, 2) }} €</span>
                            @elseif( $total > 0)
                                <span style="font-size: bolder; font-size: 30px;color: green;">{{ number_format($total, 2) }} €</span>
                            @else
                                <span style="font-size: bolder; font-size: 30px;color: red;">{{ number_format($total, 2) }} €</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            <div class="col-lg-3"></div>
        </div>

    @if (empty($calendar))
        <p class="text-center text-gray-600">Não existem tarefas para este mês.</p>
    @endif
</div>
@endsection
