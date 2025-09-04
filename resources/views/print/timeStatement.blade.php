<html>
<style>
    html,
    body {
        font-family: monospace;
        margin: 0;
        font-size: 10pt;
    }

    body {
        background-color: black;

    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    thead {
        border-bottom: 2px solid black;
    }

    .header-table td,
    .header-table th {
        text-align: left;
        padding-right: 5mm;
    }

    .data-table td,
    .data-table th {
        text-align: right;
    }

    .data-table td:first-child,
    .data-table th:first-child {
        text-align: left;
    }
</style>

<head>
    <title>Zeitnachweisliste {{ $user->first_name }} {{ $user->last_name }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/js/app.ts'])
</head>

@php

@endphp

<body>
    <div
        style="height:210mm;max-width:297mm;background-color:white;overflow:hidden; padding-left: 10mm;padding-right: 10mm;">
        @foreach ($monthData as $index => $mData)
            @foreach ($mData->data as $chunkIndex => $chunkData)
                <div
                    style="height:100vh; padding-top: 10mm; {{ $index == 0 && $chunkIndex == 0 ? 'page-break-before:avoid' : 'page-break-before:always' }}">
                    <header>
                        <table class="header-table" style="background-color: deepskyblue">
                            <tbody>
                                <tr>
                                    <td>
                                        {{ $organization->name }}
                                    </td>
                                    <td>
                                        Zeitnachweisliste
                                    </td>
                                    <td style="text-align:end">Stand {{ now()->format('d.m.Y') }}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        {{ $federal_state }}
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        Personalnummer: {{ $user->staff_number ?? '/' }}
                                    </td>
                                    <td>
                                        Name: {{ $user->first_name }} {{ $user->last_name }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <table style="margin-top:5mm">
                            <tr>
                                <td style="background-color: deepskyblue">
                                    Abrechnungszeitraum: {{ $mData->month->format('Ym') }}
                                    vom
                                    {{ $mData->month->startOfMonth()->format('d.m.Y') }} bis
                                    {{ $mData->month->endOfMonth()->format('d.m.Y') }}</td>
                            </tr>
                        </table>
                    </header>
                    <main>
                        <div style="margin-top:5mm">
                            Einzelergebnisse
                        </div>
                        <table class="data-table" style="margin-top:5mm">
                            <thead>
                                <tr>
                                    <th>Tag</th>
                                    <th>Text</th>
                                    <th>Arbeitsort</th>
                                    <th>Beguz</th>
                                    <th>Enduz</th>
                                    <th>erf.</th>
                                    <th>Fehlende Pause</th>
                                    <th>Ist</th>
                                    <th>Soll</th>
                                    <th>Glz.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($chunkData->entries as $day => $entry)
                                    @if ($entry->type == 'absence')
                                        <tr
                                            @if ($entry->entryIndex % 2 == 0) style="background-color: rgb(220, 220, 220);" @endif>
                                            <td>{{ $entry->day }}</td>
                                            <td style="white-space: nowrap">
                                                @if ($entry->holiday)
                                                    {{ $entry->holiday }}
                                                @else
                                                    {{ $entry->absence_type }}
                                                @endif
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>{{ $entry->transaction_value_text }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($entry->type == 'shift')
                                        @foreach ($entry->data as $shiftIndex => $shift)
                                            @foreach ($shift->entries as $entryIndex => $shiftEntry)
                                                <tr
                                                    @if ($shift->entryIndex % 2 == 0) style="background-color: rgb(220, 220, 220);" @endif>
                                                    <td>{{ $entry->day }}</td>
                                                    <td style="white-space: nowrap">
                                                        @if ($entry->holiday)
                                                            {{ $entry->holiday }}
                                                        @else
                                                            {{ $entry->absence_type }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $shiftEntry['type'] }}</td>
                                                    <td>{{ $shiftEntry['start'] }}</td>
                                                    <td>{{ $shiftEntry['end'] }}</td>
                                                    <td>{{ $shiftEntry['duration'] }}</td>
                                                    @if ($entryIndex == count($shift->entries) - 1)
                                                        <td>{{ $shift->missing_break_text }}</td>
                                                        <td>{{ $shift->is_text }}</td>
                                                        @if ($shiftIndex == count($entry->data) - 1)
                                                            <td>{{ $entry->should_text }}</td>
                                                        @else
                                                            <td></td>
                                                        @endif
                                                        <td>{{ $shift->transaction_value_text }}
                                                        </td>
                                                    @else
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endif
                                    @if ($entry->type == 'empty')
                                        <tr
                                            @if ($entry->entryIndex % 2 == 0) style="background-color: rgb(220, 220, 220);" @endif>
                                            <td>{{ $entry->day }}</td>
                                            <td>
                                                {{ $entry->holiday }}
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>{{ $entry->should_text }}</td>
                                            <td>{{ $entry->transaction_value_text }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="border-top: 1px solid black;border-bottom: 1px solid black">
                                    <td colspan="6">Summen:</td>
                                    <td>
                                        {{ App\Http\Controllers\UserController::formatDuration($chunkData->missing_break) }}
                                    </td>
                                    <td>{{ App\Http\Controllers\UserController::formatDuration($chunkData->is) }}
                                    </td>
                                    <td>
                                        {{ App\Http\Controllers\UserController::formatDuration($chunkData->should) }}
                                    </td>
                                    <td>
                                        {{ App\Http\Controllers\UserController::formatDuration($chunkData->transaction_value) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                    </main>
                </div>
            @endforeach

            <table>
                <thead>
                    <tr>
                        <td></td>
                        <td>Saldenübersicht</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>===============</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Art</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            Gleitzeitsaldo
                        </td>
                        <td>{{ App\Http\Controllers\UserController::formatDuration($mData->transaction_value, '00:00:00') }}
                        </td>
                        <td>Vormonat</td>
                        <td> {{ App\Http\Controllers\UserController::formatDuration($mData->previous_balance, '00:00:00') }}
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr style="border-top: 1px solid black">
                        <td>Arbeitszeit</td>
                        <td> {{ App\Http\Controllers\UserController::formatDuration($mData->is, '00:00:00') }}</td>
                        <td>Homeoffice</td>
                        <td> {{ App\Http\Controllers\UserController::formatDuration($mData->homeofficeDuration, '00:00:00') }}
                        </td>
                        <td>Dienstreisen</td>
                        <td> {{ App\Http\Controllers\UserController::formatDuration($mData->travellogDuration, '00:00:00') }}
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid black">
                        <td>Urlaub</td>
                        <td>{{ $mData->leave_days }}</td>
                        <td>genutzt</td>
                        <td> {{ $mData->leave_days_used }}</td>
                        <td>Rest</td>
                        <td>{{ $mData->leave_days - $mData->leave_days_used - $mData->leave_days_used_before }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    </div>
</body>

</html>
