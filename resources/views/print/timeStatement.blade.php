<html>
<style>
    html,
    body {
        font-family: monospace;
        margin: 0;
        font-size: 10pt;
        padding: 5mm
    }

    body {

        background-color: black
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }
</style>

<head>
    @vite(['resources/js/app.ts'])
</head>

<body>
    <div style="height:210mm;width:297mm;background-color:white;overflow:hidden; padding: 10mm;">
        <header>
            <table>
                <tbody>

                    <tr>
                        <td>
                            {{ $organization->name }}
                        </td>
                        <td style="min-width:5mm"></td>
                        <td style="min-width:5mm"></td>
                        <td>
                            Zeitnachweisliste
                        </td>
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
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            Personalnummer:
                        </td>
                        <td>
                            {{ $user->staff_number ?? '/' }}
                        </td>
                        <td></td>
                        <td>
                            Name: {{ $user->first_name }} {{ $user->last_name }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="margin-top:5mm">
                Abrechnungszeitraum: {{ $month->format('Ym') }} vom {{ $month->startOfMonth()->format('d.m.Y') }} bis
                {{ $month->endOfMonth()->format('d.m.Y') }}
            </div>
        </header>
        <main>
            <div style="margin-top:5mm">
                Einzelergebnisse
            </div>
            <table>
                <thead>
                    <tr>
                        <td>Tag</td>
                        <td>Text</td>
                        <td>Homeoffice</td>
                        <td>Beguz</td>
                        <td>Enduz</td>
                        <td>erf.</td>
                        <td>Soll</td>
                        <td>Ist</td>
                        <td>Pause</td>
                        <td>Verfallz.</td>
                        <td>Glz.</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $day => $entry)
                        @if ($entry->type == 'absence')
                            <tr @if ($entry->entryIndex % 2 == 0) style="background-color: rgb(220, 220, 220);" @endif>
                                <td>{{ $entry->day }}</td>
                                <td style="white-space: nowrap">{{ $entry->absence_type }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endif

                        @if ($entry->type == 'shift')
                            @foreach ($entry->data as $shiftIndex => $shift)
                                @foreach ($shift->logs as $logIndex => $workLog)
                                    <tr
                                        @if ($shift->entryIndex % 2 == 0) style="background-color: rgb(220, 220, 220);" @endif>
                                        <td>{{ $entry->day }}</td>
                                        <td style="white-space: nowrap">{{ $entry->absence_type }}</td>
                                        <td>{{ $workLog['home_office'] }}</td>
                                        <td>{{ $workLog['start'] }}</td>
                                        <td>{{ $workLog['end'] }}</td>
                                        <td>{{ $workLog['duration'] }}</td>
                                        @if ($logIndex == count($shift->logs) - 1)
                                            <td>{{ $shift->should }}</td>
                                            <td>{{ $shift->is }}</td>
                                            @if ($shiftIndex == count($entry->data) - 1)
                                                <td>{{ $shift->pause }}</td>
                                            @else
                                                <td></td>
                                            @endif
                                            <td></td>
                                            <td>{{ $shift->transaction_value }} </td>
                                        @else
                                            <td></td>
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
                            <tr @if ($entry->entryIndex % 2 == 0) style="background-color: rgb(220, 220, 220);" @endif>
                                <td>{{ $entry->day }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>{{ $entry->should }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>{{ $entry->transaction_value }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </main>
    </div>
</body>

</html>
