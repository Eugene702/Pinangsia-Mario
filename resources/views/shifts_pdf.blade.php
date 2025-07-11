<!DOCTYPE html>
<html>

<head>
    <title>Laporan Shift</title>
    <style>
        body {
            font-family: Arial;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            background-color: #eee;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN JADWAL SHIFT</h2>
        <p>Periode: {{ $month }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Staff</th>
                <th>Pola</th>
                <th>Shift</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($shifts as $index => $shift)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $shift['staff'] }}</td>
                    <td>{{ $shift['pattern'] }}</td>
                    <td>{{ $shift['shifts'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 10px; text-align: right;">
        Dicetak: {{ $printed_date }}
    </div>
</body>

</html>
