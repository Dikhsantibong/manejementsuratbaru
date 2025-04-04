<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header img {
            width: 100px;
            height: auto;
            margin-bottom: 15px;
            filter: drop-shadow(0 4px 3px rgb(0 0 0 / 0.07));
        }
        .header h2 {
            font-size: 20px;
            font-weight: bold;
            margin: 5px 0;
            color: #333;
        }
        .header .subtitle {
            font-size: 16px;
            color: #4a5568;
            margin: 10px 0;
        }
        .filter-info {
            margin: 20px auto;
            padding: 10px;
            background-color: #ebf4ff;
            border-radius: 8px;
            color: #2c5282;
            text-align: center;
            font-weight: bold;
            width: 80%;
            border: 1px solid #bfdbfe;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        th {
            background-color: #66a7cc;
            color: #333;
            font-weight: bold;
            padding: 12px 8px;
            border: 1px solid #999;
            text-transform: uppercase;
        }
        td {
            padding: 12px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #4a5568;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 15px;
            font-size: 10px;
            text-align: center;
            border-top: 2px solid #e2e8f0;
            background: linear-gradient(to bottom, #ffffff, #f8fafc);
            color: #4a5568;
        }
        .page-number {
            font-weight: bold;
            color: #2c5282;
        }
        .page-number:before {
            content: counter(page);
        }
        .no-data {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #718096;
        }
        td.centered {
            text-align: center;
        }
        td.perihal {
            text-align: justify;
            line-height: 1.6;
        }
        .print-info {
            font-size: 9px;
            color: #718096;
            text-align: right;
            margin-top: 5px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo">
        <h2>PEMERINTAH PROVINSI SULAWESI TENGGARA</h2>
        <h2>{{ $title }}</h2>
        <div class="subtitle">{{ $filterInfo }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">No. Agenda</th>
                <th style="width: 15%;">No. Surat</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 18%;">Pengirim</th>
                <th style="width: 25%;">Perihal</th>
                <th style="width: 13%;">Disposisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->no_agenda }}</td>
                    <td>{{ $item->no_surat }}</td>
                    <td>{{ $item->tanggal_terima ? $item->tanggal_terima->isoFormat('D MMMM Y') : '-' }}</td>
                    <td>{{ $item->pengirim }}</td>
                    <td>{{ $item->perihal }}</td>
                    <td>{{ $item->disposisi }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div>Dicetak pada: {{ now()->isoFormat('dddd, D MMMM Y') }}</div>
        <div>Halaman <span class="page-number"></span></div>
    </div>
</body>
</html> 