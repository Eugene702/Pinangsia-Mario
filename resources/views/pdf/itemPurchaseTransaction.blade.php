<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian Barang</title>
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
            border-bottom: 2px solid #f97316;
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
            background: #fef3e2;
            border-radius: 6px;
            border-left: 4px solid #f97316;
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
            border-bottom: 1px solid #f97316;
            page-break-after: avoid;
        }

        .purchase-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            font-size: 10px;
        }

        .purchase-table thead {
            background: #f97316;
            color: white;
        }

        .purchase-table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            vertical-align: top;
        }









        .purchase-table tbody tr {
            page-break-inside: avoid;
        }

        .purchase-table tbody tr:nth-child(even) {
            background: #fefefe;
        }

        .purchase-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .purchase-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 9px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .purchase-table .item-name {
            font-weight: 600;
            color: #1f2937;
        }

        .purchase-table .supplier {
            color: #6b7280;
            font-weight: 500;
        }

        .purchase-table .quantity {
            text-align: center;
            font-weight: 600;
            color: #f97316;
        }

        .purchase-table .price {
            text-align: right;
            font-weight: 600;
            color: #1f2937;
        }

        .purchase-table .notes {
            color: #6b7280;
            font-size: 8px;
            max-width: 120px;
            word-break: break-word;
        }

        .purchase-table .total {
            text-align: right;
            font-weight: 700;
            color: #f97316;
        }

        .purchase-table .date {
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
            border-bottom: 1px solid #f97316;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
            color: #f97316;
            margin-bottom: 4px;
        }

        .summary-card .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .total-section {
            background: #fef3e2;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #f97316;
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

        .total-row {
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
            color: #f97316;
        }

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

        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        @media print {
            .page {
                margin: 0;
                width: 210mm;
                min-height: 297mm;
            }

            .purchase-table thead {
                background: #f97316 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }

            .company-info {
                background: #fef3e2 !important;
                -webkit-print-color-adjust: exact;
            }

            .total-section {
                background: #fef3e2 !important;
                -webkit-print-color-adjust: exact;
            }

            .purchase-table thead {
                display: table-header-group;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <h1>Laporan Pembelian Barang</h1>
            <p class="subtitle">Daftar Transaksi Pembelian</p>
        </div>

        <div class="company-info">
            <div class="left">
                <h3>Pinangsia Hotel</h3>
                <p>Alamat: Alamat: Jl. Pinangsia I No.55 7, RT.7/RW.5, Pinangsia, Kec. Taman Sari, Kota Jakarta Barat,
                    Daerah
                    Khusus Ibukota Jakarta 11110</p>
                <p>Telepon: (021) 6246478</p>
                <p>Email: pinangsiahotel@yahoo.com</p>
            </div>
            <div class="right">
                <h3>Kode Laporan: RPC-{{ now()->format('Y') }}-{{ now()->format('m') }}</h3>
                <p>Dibuat: {{ now()->format('d F Y') }}</p>
            </div>
        </div>

        <div class="report-meta">
            <div class="meta-item">
                <div class="label">Periode</div>
                <div class="value">{{ $periode }}</div>
            </div>
            <div class="meta-item">
                <div class="label">Total Transaksi</div>
                <div class="value">{{ $totalTransactions }} Items</div>
            </div>
            <div class="meta-item">
                <div class="label">Total Pemasok</div>
                <div class="value">{{ $totalSupplier }} Vendor</div>
            </div>
            <div class="meta-item">
                <div class="label">Status</div>
                <div class="value">Approved</div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-title">Data Transaksi Pembelian</div>
            <table class="purchase-table">
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Nama Pemasok</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Catatan</th>
                        <th>Total Harga</th>
                        <th>Tanggal Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemPurchaseTransaction as $row)
                        <tr>
                            <td class="item-name">{{ $row->inventory->name }}</td>
                            <td class="supplier">{{ $row->supplier }}</td>
                            <td class="quantity">{{ $row->qty }} Unit</td>
                            <td class="price">{{ 'Rp ' . number_format($row->unitPrice, 0, ',', '.') }}</td>
                            <td class="notes">{{ $row->note }}</td>
                            <td class="total">{{ 'Rp ' . number_format($row->qty * $row->unitPrice, 0, ',', '.') }}</td>
                            <td class="date">{{ $row->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span class="total-label">TOTAL KESELURUHAN: </span>
                <span class="total-value">{{ 'Rp ' . number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Laporan ini dibuat secara otomatis oleh sistem procurement. Dokumen ini bersifat rahasia dan tidak untuk
                disebarluaskan.</p>
        </div>
    </div>
</body>

</html>
