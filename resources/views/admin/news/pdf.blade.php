<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Berita</title>

<style>

body{
    font-family: DejaVu Sans,sans-serif;
    font-size:12px;
    color:#222;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th,td{
    border:1px solid #000;
    padding:6px;
    vertical-align:top;
}

th{
    background:#eeeeee;
}

.header{
    text-align:center;
    margin-bottom:20px;
}

.header h2{
    margin:0;
    font-size:18px;
}

.header h3{
    margin:5px 0;
    font-size:16px;
}

.header p{
    margin:0;
    font-size:12px;
}

</style>

</head>

<body>

<div class="header">

<h2>KEPOLISIAN NEGARA REPUBLIK INDONESIA</h2>

<h3>DAERAH PAPUA TENGAH</h3>

<p>Data Berita Website Polda Papua Tengah</p>

</div>

<table>

<thead>

<tr>

<th width="5%">No</th>

<th width="38%">Judul</th>

<th width="18%">Kategori</th>

<th width="12%">Status</th>

<th width="27%">Tanggal</th>

</tr>

</thead>

<tbody>

@foreach($news as $index => $item)

<tr>

<td align="center">
{{ $index+1 }}
</td>

<td>
{{ $item->title }}
</td>

<td>
{{ $item->category }}
</td>

<td align="center">
{{ ucfirst($item->status) }}
</td>

<td>
{{ optional($item->published_at)->format('d-m-Y H:i') ?? $item->created_at->format('d-m-Y H:i') }}
</td>

</tr>

@endforeach

</tbody>

</table>

</body>

</html>
