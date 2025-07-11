<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin-bottom: 5px;
        }

        .header p {
            margin-top: 0;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Peminjam</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($borrowings as $index => $borrowing)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $borrowing->inventory->name }}</td>
                    <td>{{ $borrowing->borrowed_quantity }}</td>
                    <td>{{ $borrowing->user->name }}</td>
                    <td>{{ $borrowing->borrowed_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $borrowing->returned_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>
                        @if ($borrowing->status == 'borrowed')
                            <span style="color: orange;">Dipinjam</span>
                        @elseif($borrowing->status == 'returned')
                            <span style="color: green;">Dikembalikan</span>
                        @else
                            <span style="color: red;">{{ ucfirst($borrowing->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $borrowing->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ config('app.name') }} - {{ now()->year }}
    </div>
</body>

</html>
