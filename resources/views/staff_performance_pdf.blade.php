<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Staff</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .info-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .performance-table {
            width: 100%;
            border-collapse: collapse;
        }

        .performance-table th,
        .performance-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .performance-table th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN KINERJA STAFF</h1>
        <p>Hotel Management System</p>
        <p>Periode: {{ date('d M Y', strtotime($periodStart)) }} - {{ date('d M Y', strtotime($periodEnd)) }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><strong>Nama Staff</strong></td>
            <td width="30%">{{ $staff->name }}</td>
            <td width="20%"><strong>Jabatan</strong></td>
            <td width="30%">{{ $staff->jabatan }}</td>
        </tr>
        <tr>
            <td><strong>Nomor Telepon</strong></td>
            <td>{{ $staff->no_telp }}</td>
            <td><strong>Role</strong></td>
            <td>{{ ucfirst($staff->role) }}</td>
        </tr>
    </table>

    <h3>Statistik Kinerja</h3>
    <table class="performance-table">
        <thead>
            <tr>
                <th>Metrik</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Kamar yang Dibersihkan</td>
                <td>{{ $totalRooms }}</td>
            </tr>
            <tr>
                <td>Rata-rata Durasi Pembersihan (menit)</td>
                <td>{{ $avgDuration }}</td>
            </tr>
            <tr>
                <td>Total Permintaan Layanan</td>
                <td>{{ $requests }}</td>
            </tr>
            <tr>
                <td>Rata-rata Kamar per Hari</td>
                <td>{{ $roomsPerDay }}</td>
            </tr>
        </tbody>
    </table>

    @if ($rating && $notes)
        <h3 style="margin-top: 20px;">Evaluasi</h3>
        <table class="performance-table">
            <tr>
                <td width="20%"><strong>Rating</strong></td>
                <td width="80%">{{ $rating }}/5</td>
            </tr>
            <tr>
                <td><strong>Catatan</strong></td>
                <td>{{ $notes }}</td>
            </tr>
        </table>
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ date('d M Y H:i') }}</p>
    </div>
</body>

</html>
