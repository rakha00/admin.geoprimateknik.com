<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Invoice {{ $transaksi->no_invoice }}</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 13px;
      margin: 20px;
      margin-top: 20px
    }
    table {
      border-collapse: collapse;
      width: 100%;
    }
    td, th {
      border: 1px solid #000;
      padding: 6px;
      vertical-align: top;
    }
    .no-border td {
      border: none !important;
    }
    .center {
      text-align: center;
    }
    .right {
      text-align: right;
    }
    .bold {
      font-weight: bold;
    }
    .header td {
      border: none;
    }
    .signature {
      margin-top: -120px;
      text-align: right;
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <table class="header">
    <tr>
      <td style="width:20%; text-align:center;">
        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('logo.jpg'))) }}" style="max-width:80px;">
      </td>
      <td style="width:50%;">
        <strong style="font-size: 17px">PT. Geoprima Teknik Persada</strong><br><br>
        Cibis Nine Building Lantai 11<br>
        Jl. TB Simatupang No. 2<br>
        Pasar Minggu - Jakarta Selatan<br>
        Telp : 081233331260 / 021-26965956
      </td>
      <td style="margin-top:-30px; width:30%; text-align:center; font-size:18px; font-weight:bold;">
        INVOICE
      </td>
    </tr>
  </table>

  <!-- INFO INVOICE -->
  <table style="margin-top:-70px;">
    <tr>
      <td style="width:70%; border:none;"></td>
      <td>No Invoice</td>
      <td>{{ $transaksi->no_invoice }}</td>
    </tr>
    <tr>
      <td style="border:none;"></td>
      <td>Tanggal</td>
      <td style="font-size: 12px">{{ \Carbon\Carbon::parse($transaksi->tanggal)->translatedFormat('d F Y') }}</td>
    </tr>
  </table>
  <br>
   <hr><hr>
<br>
  <!-- KEPADA -->
  <table style="margin-top:10px; width: 50%;">
    <tr>
      <td>
        <strong>Kepada Yth</strong><br>
        {{ $transaksi->toko->nama_konsumen }}<br>
        {{ $transaksi->toko->alamat ?? '' }}
      </td>
    </tr>
  </table>

  <!-- TABEL ITEM -->
  <table style="margin-top:15px;">
    <tr>
      <th style="width:5%;">No</th>
      <th style="width:55%;">Description</th>
      <th style="width:10%;">QTY</th>
      <th style="width:15%;">Price</th>
      <th style="width:15%;">Total</th>
    </tr>
    @php
      $subtotal = 0;
    @endphp
    @foreach($transaksi->details as $i => $d)
      @php
        $lineTotal = ($d->harga_jual ?? 0) * ($d->jumlah_keluar ?? 0);
        $subtotal += $lineTotal;
      @endphp
      <tr>
        <td class="center">{{ $i+1 }}</td>
        <td>{{ $d->nama_unit }}</td>
        <td class="center">{{ $d->jumlah_keluar }}</td>
        <td class="right">{{ number_format($d->harga_jual, 0, ',', '.') }}</td>
        <td class="right">{{ number_format($lineTotal, 0, ',', '.') }}</td>
      </tr>
    @endforeach
    @php
      $ppn = $subtotal * 0.11;
      $grandTotal = $subtotal + $ppn;
    @endphp
    <tr>
      <td colspan="4" class="right bold">Total</td>
      <td class="right">{{ number_format($subtotal, 0, ',', '.') }}</td>
    </tr>
    <tr>
      <td colspan="4" class="right bold">PPN 11%</td>
      <td class="right">{{ number_format($ppn, 0, ',', '.') }}</td>
    </tr>
    <tr>
      <td colspan="4" class="right bold">Grand Total</td>
      <td class="right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
    </tr>
  </table>

  <!-- BANK INFO -->
  <table style="margin-top:20px; width:60%;">
    <tr>
      <td>
        <strong>Description</strong><br>
        Pembayaran dialamatkan kepada : <br>
        (Payment should be addressed to :)<br>
        Bank BCA<br>
        ACC. No. : 8802-999.444<br>
        A/N : PT. Geoprima Teknik Persada
      </td>
    </tr>
  </table>

  <!-- SIGNATURE -->
  <div class="signature">
    Jakarta Selatan, {{ \Carbon\Carbon::parse($transaksi->tanggal)->translatedFormat('d F Y') }}<br><br><br><br><br><br><br>
    <strong>{{ $transaksi->dibuat_oleh ?? '................' }}</strong>
  </div>

</body>
</html>
