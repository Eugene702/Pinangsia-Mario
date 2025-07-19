<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kinerja Karyawan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #374151;
            background: #ffffff;
        }

        .page {
            width: 210mm;
            margin: 0 auto;
            background: white;
            position: relative;
            padding: 25mm 20mm;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f97316;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header .subtitle {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px;
            background: #fef3e2;
            border-radius: 8px;
            border-left: 4px solid #f97316;
        }

        .company-info .left {
            flex: 1;
        }

        .company-info .right {
            text-align: right;
        }

        .company-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .company-info p {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .report-meta {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .meta-item {
            background: #f9fafb;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .meta-item .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .meta-item .value {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .table-container {
            margin-bottom: 25px;
        }

        .table-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f97316;
        }

        .performance-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .performance-table thead {
            background: #f97316;
            color: white;
        }

        .performance-table th {
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .performance-table tbody tr:nth-child(even) {
            background: #fefefe;
        }

        .performance-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .performance-table td {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .performance-table .rank {
            width: 60px;
            text-align: center;
            font-weight: 700;
            color: #f97316;
            font-size: 14px;
            text-align: center;
        }

        .performance-table .employee-name {
            font-weight: 600;
            color: #1f2937;
        }

        .performance-table .metric {
            text-align: center;
            font-weight: 500;
            color: #6b7280;
        }

        .summary-section {
            margin-bottom: 25px;
        }

        .summary-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f97316;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .summary-card {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .summary-card .number {
            font-size: 24px;
            font-weight: 700;
            color: #f97316;
            margin-bottom: 5px;
        }

        .summary-card .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-box {
            text-align: center;
            width: 150px;
        }

        .signature-box .title {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 40px;
        }

        .signature-box .line {
            border-top: 1px solid #374151;
            padding-top: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
        }

        .signature-box .date {
            font-size: 10px;
            color: #6b7280;
            margin-top: 5px;
        }

        .footer {
            position: absolute;
            bottom: 15mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .performance-table tr,
        .performance-table tbody {
            page-break-inside: avoid;
        }

        .summary-card {
            page-break-inside: avoid;
        }

        @media print {
            .page {
                margin: 0;
                width: 210mm;
                min-height: 297mm;
            }

            .performance-table thead {
                background: #f97316 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }

            .company-info {
                background: #fef3e2 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <h1>Laporan Kinerja Karyawan</h1>
            <p class="subtitle">Evaluasi Performa Bulanan</p>
        </div>

        <div class="company-info">
            <div class="left">
                <h3>Pinangsia Hotel</h3>
                <p>Alamat: Jl. Pinangsia I No.55 7, RT.7/RW.5, Pinangsia, Kec. Taman Sari, Kota Jakarta Barat, Daerah
                    Khusus Ibukota Jakarta 11110</p>
                <p>Telepon: (021) 6246478</p>
                <p>Email: pinangsiahotel@yahoo.com</p>
            </div>
            <div class="right">
                <h3>Kode Laporan: RPT-{{ $year }}-{{ $month }}</h3>
                <p>Dibuat: {{ now()->format('d F Y') }}</p>
            </div>
        </div>

        <div class="report-meta">
            <div class="meta-item">
                <div class="label">Periode</div>
                <div class="value">{{ \Carbon\Carbon::create()->month((int) $month)->isoFormat('MMMM') }}
                    {{ $year }}</div>
            </div>
            <div class="meta-item">
                <div class="label">Total Karyawan</div>
                <div class="value">{{ $users->count() }} Orang</div>
            </div>
            <div class="meta-item">
                <div class="label">Departemen</div>
                <div class="value">Housekeeping</div>
            </div>
            <div class="meta-item">
                <div class="label">Status</div>
                <div class="value">Final</div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Data Kinerja Karyawan</div>
            <table class="performance-table">
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Nama Staff</th>
                        <th>Total Kamar Dibersihkan</th>
                        <th>Durasi Pembersihan</th>
                        <th>Jumlah Hadir</th>
                        <th>Skor Kinerja</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $row)
                        <tr>
                            <td class="rank">{{ $loop->iteration }}</td>
                            <td class="employee-name">{{ $row->name }}</td>
                            <td class="metric">{{ $row->cleaning_schedules_count }}</td>
                            <td class="metric">{{ round($row->cleaning_schedules_avg_cleaning_duration, 2) }} menit</td>
                            <td class="metric">{{ $row->present_count }}</td>
                            <td class="metric">{{ round($row->score, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary-section">
            <div class="summary-title">Ringkasan Kinerja</div>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="number">{{ $totalRooms }}</div>
                    <div class="label">Total Kamar</div>
                </div>
                <div class="summary-card">
                    <div class="number">{{ round($avgDuration, 2) }}</div>
                    <div class="label">Rata-rata Durasi</div>
                </div>
                <div class="summary-card">
                    <div class="number">{{ round($highestScore, 2) }}</div>
                    <div class="label">Skor Tertinggi</div>
                </div>
                <div class="summary-card">
                    <div class="number">{{ round($attendanceRate, 1) }}%</div>
                    <div class="label">Tingkat Kehadiran</div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Laporan ini dibuat secara otomatis oleh sistem. Dokumen ini bersifat rahasia dan tidak untuk
                disebarluaskan.</p>
        </div>
    </div>
</body>

</html>
