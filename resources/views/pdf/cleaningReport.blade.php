<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembersihan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #374151;
            background: #ffffff;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            position: relative;
            padding: 20mm 15mm 25mm 15mm;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f59e0b;
            page-break-inside: avoid;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header .subtitle {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 12px;
            background: #fffbeb;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
            page-break-inside: avoid;
        }

        .company-info .left {
            flex: 1;
        }

        .company-info .right {
            text-align: right;
            flex: 1;
        }

        .company-info h3 {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .company-info p {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .report-meta {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .meta-item {
            background: #f9fafb;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
        }

        .meta-item .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            font-weight: 500;
        }

        .meta-item .value {
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
        }

        .table-container {
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #f59e0b;
            page-break-after: avoid;
        }

        .cleaning-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            font-size: 10px;
        }

        .cleaning-table thead {
            background: #f59e0b;
            color: white;
        }

        .cleaning-table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            vertical-align: top;
        }

        .cleaning-table th:nth-child(1) {
            width: 15%;
        }

        .cleaning-table th:nth-child(2) {
            width: 18%;
        }

        .cleaning-table th:nth-child(3) {
            width: 15%;
        }

        .cleaning-table th:nth-child(4) {
            width: 12%;
        }

        .cleaning-table th:nth-child(5) {
            width: 25%;
        }

        .cleaning-table th:nth-child(6) {
            width: 15%;
        }

        .cleaning-table tbody tr {
            page-break-inside: avoid;
        }

        .cleaning-table tbody tr:nth-child(even) {
            background: #fefefe;
        }

        .cleaning-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .cleaning-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 9px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .cleaning-table .room-number {
            font-weight: 600;
            color: #1f2937;
            text-align: center;
        }

        .cleaning-table .cleaner-name {
            color: #6b7280;
            font-weight: 500;
        }

        .cleaning-table .schedule {
            text-align: center;
            color: #6b7280;
            font-size: 8px;
        }

        .cleaning-table .status {
            text-align: center;
            font-weight: 600;
            font-size: 8px;
            text-transform: uppercase;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .cleaning-table .status.completed {
            background: #dcfce7;
            color: #166534;
        }

        .cleaning-table .status.in-progress {
            background: #fef3c7;
            color: #92400e;
        }

        .cleaning-table .status.pending {
            background: #fee2e2;
            color: #dc2626;
        }

        .cleaning-table .notes {
            color: #6b7280;
            font-size: 8px;
            max-width: 150px;
            word-break: break-word;
        }

        .cleaning-table .time {
            text-align: center;
            color: #6b7280;
            font-size: 8px;
        }

        .summary-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .summary-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #f59e0b;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 15px;
        }

        .summary-card {
            background: #f9fafb;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .summary-card .number {
            font-size: 18px;
            font-weight: 700;
            color: #f59e0b;
            margin-bottom: 4px;
        }

        .summary-card .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .summary-card.completed .number {
            color: #16a34a;
        }

        .summary-card.in-progress .number {
            color: #f59e0b;
        }

        .summary-card.pending .number {
            color: #dc2626;
        }

        .total-section {
            background: #fffbeb;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #f59e0b;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .total-row {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 0;
            font-weight: 700;
            font-size: 16px;
            padding: 12px 0;
            text-align: center;
        }

        .total-label {
            font-weight: 600;
            color: #1f2937;
        }

        .total-value {
            font-weight: 600;
            color: #f59e0b;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            width: 140px;
        }

        .signature-box .title {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 35px;
        }

        .signature-box .line {
            border-top: 1px solid #374151;
            padding-top: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #1f2937;
        }

        .signature-box .date {
            font-size: 9px;
            color: #6b7280;
            margin-top: 4px;
        }

        .footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        @media print {
            .page {
                margin: 0;
                width: 210mm;
                min-height: 297mm;
            }

            .cleaning-table thead {
                background: #f59e0b !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }

            .company-info {
                background: #fffbeb !important;
                -webkit-print-color-adjust: exact;
            }

            .total-section {
                background: #fffbeb !important;
                -webkit-print-color-adjust: exact;
            }

            .cleaning-table thead {
                display: table-header-group;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <h1>Laporan Pembersihan</h1>
            <p class="subtitle">Jadwal dan Status Pembersihan Kamar</p>
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
                <h3>Kode Laporan: HK-{{ now()->format('Y') }}-{{ now()->format('m') }}</h3>
                <p>Dibuat: {{ now()->format('d F Y') }}</p>
            </div>
        </div>

        <div class="report-meta">
            <div class="meta-item">
                <div class="label">Tanggal</div>
                <div class="value">{{ $period }}</div>
            </div>
            <div class="meta-item">
                <div class="label">Total Kamar</div>
                <div class="value">{{ $totalRooms }} Kamar</div>
            </div>
            <div class="meta-item">
                <div class="label">Petugas</div>
                <div class="value">{{ $totalStaff }} Orang</div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Data Pembersihan Kamar</div>
            <table class="cleaning-table">
                <thead>
                    <tr>
                        <th>Nomor Kamar</th>
                        <th>Ditugaskan ke</th>
                        <th>Jadwal Pembersihan</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cleaningSchedules as $row)
                        <tr>
                            <td class="room-number">{{ $row->room_id }}</td>
                            <td class="cleaner-name">{{ $row->assignedStaff->name }}</td>
                            <td class="schedule">{{ $row->scheduled_at->format('H:i') }}</td>
                            @if ($row->status === 'completed')
                                <td class="status completed">Selesai</td>
                            @elseif ($row->status === 'in_progress')
                                <td class="status in-progress">Proses</td>
                            @else
                                <td class="status pending">Menunggu</td>
                            @endif
                            <td class="notes">{{ $row->notes }}</td>
                            <td class="time">{{ $row->cleaning_duration }} Menit</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span class="total-label">TOTAL KAMAR DITUGASKAN: </span>
                <span class="total-value">{{ $totalRooms }} Kamar</span>
            </div>
        </div>

        <div class="summary-section">
            <div class="summary-title">Ringkasan Status Pembersihan</div>
            <div class="summary-grid">
                <div class="summary-card completed">
                    <div class="number">{{ $summary['complete'] }}</div>
                    <div class="label">Selesai</div>
                </div>
                <div class="summary-card in-progress">
                    <div class="number">{{ $summary['in_progress'] }}</div>
                    <div class="label">Sedang Proses</div>
                </div>
                <div class="summary-card pending">
                    <div class="number">{{ $summary['waiting'] }}</div>
                    <div class="label">Menunggu</div>
                </div>
                <div class="summary-card">
                    <div class="number">{{ $totalStaff }}</div>
                    <div class="label">Total Petugas</div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Laporan ini dibuat secara otomatis oleh sistem housekeeping. Dokumen ini bersifat internal dan tidak
                untuk disebarluaskan.</p>
        </div>
    </div>
</body>

</html>