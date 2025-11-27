<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Surat Jalan</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
    }
    table {
      border-collapse: collapse;
      width: 100%;
    }
    td, th {
      border: 1px solid #000;
      padding: 8px;
      vertical-align: top;
    }
    .center {
      text-align: center;
    }
    .header-table td {
      vertical-align: middle;
      font-size: 12px; /* <-- kecilin font header */
    }

    .header-table td p {
        margin: 0;
        font-size: 11px;
    }

    .header-table strong {
      font-size: 14px; /* judul di header lebih kecil */
    }
    .signature td {
      height: 120px;
      text-align: center;
      vertical-align: bottom;
        width: 50%;
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <table class="header-table">
    <tr>
      <td style="width: 20%; text-align:center;">
        <strong>DELIVERY<br>ORDER</strong>
      </td>
      <td style="width: 30%; text-align:center;">
        <strong>Nomor Pengiriman Barang</strong><br>
        <hr>
        {{ $transaksi->no_surat_jalan }}
      </td>
      <td style="width: 20%; text-align:center;">
        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('logo.jpg'))) }}" style="max-width:80px;">

      </td>
      <td style="width: 30%; line-height:1.3;">
        <strong>PT. GEOPRIMA TEKNIK PERSADA</strong>
        <p>Simatupang No.2 Cilandak Timur - Jakarta Selatan</p>
        <p>Telp. 02126965956 WA. 081211119213</p>
        <p>Email : admin@geoprimateknik.com</p>
        <p>Website : www.geoprimateknik.com</p>
      </td>
    </tr>
  </table>

  <!-- INFO PENGIRIM & PENERIMA -->
  <table style="margin-top:10px; font-size:13px;">
    <tr>
      <td style="width:50%;">
        <strong>Keterangan: </strong><br><br>
        {{ $transaksi->remarks }}
      </td>
      <td style="width:50%;">
        <strong>Dikirim Kepada :</strong><br><br>
        {{ $transaksi->toko->nama_konsumen }}<br>
        {{ $transaksi->toko->alamat ?? '' }}
      </td>
    </tr>
  </table>

  <!-- TABEL BARANG -->
  <table style="margin-top:10px; font-size:13px;">
    <tr>
      <th style="width:5%;">No.</th>
      <th style="width:45%;">Keterangan Barang / Model / No. Mesin</th>
      <th style="width:25%;">Kode Referensi</th>
      <th style="width:10%;">Qty</th>
      <th style="width:15%;">Satuan</th>
    </tr>
    @foreach ($transaksi->details as $i => $detail)
      <tr>
        <td class="center">{{ $i+1 }}.</td>
        <td>{{ $detail->nama_unit }}</td>
        <td>{{ $detail->remarks ?? '-' }}</td>
        <td class="center">{{ $detail->jumlah_keluar }}</td>
        <td class="center">{{ $detail->satuan ?? 'Set' }}</td>
      </tr>
    @endforeach
  </table>

  <!-- TANGGAL PENERIMAAN -->
  <p style="font-size:13px;">
    <strong>Tanggal Penerimaan Barang :</strong> 
    {{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y') }}
  </p>

  <!-- TANDA TANGAN -->
  <table class="signature" style="font-size:13px;">
    <tr>
      <td>
        Hormat Kami,<br><br><br><br><br><br>
        <br>
      </td>
      <td>
        Diterima Oleh,<br><br><br><br><br><br>
        (__________________________________)
      </td>
    </tr>
  </table>

</body>
</html>
