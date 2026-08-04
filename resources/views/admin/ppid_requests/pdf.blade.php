<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>
        Permohonan Informasi PPID
    </title>

    <style>
    @page {
        margin: 25px 30px;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #1f2937;
        line-height: 1.5;
    }

    /* ===========================
       HEADER / KOP SURAT
    ============================ */

    .header {
        text-align: center;
        border-bottom: 3px solid #0f172a;
        padding-bottom: 12px;
        margin-bottom: 20px;
    }

    .logo {
        width: 80px;
        height: 80px;
        display: block;
        margin: 0 auto 8px auto;
    }

    .header-title {
        font-size: 20px;
        font-weight: bold;
        color: #0b2d5c;
        letter-spacing: .5px;
        margin: 0;
        padding: 0;
        line-height: 1.2;
    }

    .header-subtitle {
        font-size: 13px;
        font-weight: bold;
        margin-top: 5px;
        margin-bottom: 3px;
    }

    .header-description {
        font-size: 9px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* ===========================
       INFORMASI DOKUMEN
    ============================ */

    .document-info {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }

    .document-info td {
        padding: 4px 2px;
        vertical-align: top;
    }

    .document-info .label {
        width: 130px;
        font-weight: bold;
    }

    /* ===========================
       JUDUL SECTION
    ============================ */

    .section {
        margin-top: 18px;
        margin-bottom: 8px;
        padding: 7px 10px;
        background: #eef2f7;
        border-left: 5px solid #0b2d5c;
        font-size: 11px;
        font-weight: bold;
        color: #0b2d5c;
    }

    /* ===========================
       TABEL
    ============================ */

    table.data {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }

    table.data th,
    table.data td {
        border: 1px solid #cfd6df;
        padding: 7px 8px;
        vertical-align: top;
    }

    table.data th {
        width: 25%;
        background: #f5f7fa;
        text-align: left;
        font-weight: bold;
        color: #111827;
    }

    table.data td {
        background: #ffffff;
    }

    /* ===========================
       TEXT
    ============================ */

    .text-block {
        white-space: pre-line;
        line-height: 1.6;
    }

    .status {
        font-weight: bold;
        color: #0b2d5c;
    }

    /* ===========================
       FOOTER
    ============================ */

    .footer {
        margin-top: 28px;
        border-top: 1px solid #cfd6df;
        padding-top: 8px;
        text-align: center;
        font-size: 8px;
        color: #6b7280;
        line-height: 1.5;
    }

    /* ===========================
       PAGE BREAK
    ============================ */

    .page-break {
        page-break-before: always;
    }
</style>
</head>

<<body>

@php
    $logo = public_path('images/logo/logo-polda-papua-tengah.png');

    $logoBase64 = null;

    if (file_exists($logo)) {
        $type = pathinfo($logo, PATHINFO_EXTENSION);
        $data = file_get_contents($logo);

        $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
@endphp

@foreach ($ppidRequests as $index => $ppidRequest)

    @if ($index > 0)
        <div class="page-break"></div>
    @endif

    <div class="header">

        @if($logoBase64)
            <img
                src="{{ $logoBase64 }}"
                style="height:85px; margin-bottom:8px;">
        @endif

        <div class="header-title">
            KEPOLISIAN DAERAH PAPUA TENGAH
        </div>

        <div class="header-subtitle">
            PEJABAT PENGELOLA INFORMASI DAN DOKUMENTASI (PPID)
        </div>

        <div class="header-description">
            Dokumen Permohonan Informasi Publik
        </div>

    </div>
        <table class="document-info">

            <tr>
                <td class="label">
                    Nomor Tiket
                </td>

                <td>
                    : {{ $ppidRequest->ticket }}
                </td>
            </tr>

            <tr>
                <td class="label">
                    Tanggal Permohonan
                </td>

                <td>
                    : {{ $ppidRequest->created_at?->format('d/m/Y H:i') ?? '-' }}
                </td>
            </tr>

            <tr>
                <td class="label">
                    Status
                </td>

                <td class="status">
                    : {{ ucfirst($ppidRequest->status) }}
                </td>
            </tr>

        </table>

        <div class="section">
            I. IDENTITAS PEMOHON
        </div>

        <table class="data">

            <tr>
                <th>
                    Nama Lengkap
                </th>

                <td>
                    {{ $ppidRequest->name }}
                </td>
            </tr>

            <tr>
                <th>
                    Nomor Identitas
                </th>

                <td>
                    {{ $ppidRequest->identity_number }}
                </td>
            </tr>

            <tr>
                <th>
                    Nomor Telepon
                </th>

                <td>
                    {{ $ppidRequest->phone }}
                </td>
            </tr>

            <tr>
                <th>
                    Email
                </th>

                <td>
                    {{ $ppidRequest->email }}
                </td>
            </tr>

            <tr>
                <th>
                    Alamat
                </th>

                <td class="text-block">
                    {{ $ppidRequest->address }}
                </td>
            </tr>

        </table>

        <div class="section">
            II. PERMOHONAN INFORMASI
        </div>

        <table class="data">

            <tr>
                <th>
                    Informasi yang Diminta
                </th>

                <td class="text-block">
                    {{ $ppidRequest->information }}
                </td>
            </tr>

            <tr>
                <th>
                    Tujuan Penggunaan Informasi
                </th>

                <td class="text-block">
                    {{ $ppidRequest->purpose }}
                </td>
            </tr>

            <tr>
                <th>
                    Cara Memperoleh Informasi
                </th>

                <td>

                    @if ($ppidRequest->delivery_method === 'softcopy')
                        Softcopy
                    @elseif ($ppidRequest->delivery_method === 'hardcopy')
                        Hardcopy
                    @elseif ($ppidRequest->delivery_method === 'view')
                        Lihat Langsung
                    @else
                        {{ ucfirst($ppidRequest->delivery_method) }}
                    @endif

                </td>
            </tr>

        </table>

        <div class="section">
            III. PROSES PPID
        </div>

        <table class="data">

            <tr>
                <th>
                    Status Permohonan
                </th>

                <td>
                    {{ ucfirst($ppidRequest->status) }}
                </td>
            </tr>

            <tr>
                <th>
                    Petugas / Admin
                </th>

                <td>
                    {{ $ppidRequest->processor?->name ?? 'Belum ditentukan' }}
                </td>
            </tr>

            <tr>
                <th>
                    Waktu Pemrosesan
                </th>

                <td>
                    {{ $ppidRequest->processed_at?->format('d/m/Y H:i') ?? 'Belum diproses' }}
                </td>
            </tr>

            <tr>
                <th>
                    Catatan Admin
                </th>

                <td class="text-block">
                    {{ $ppidRequest->catatan_admin ?? 'Tidak ada catatan.' }}
                </td>
            </tr>

        </table>

        <div class="footer">

            Dokumen ini dihasilkan oleh CMS PPID Polda Papua Tengah.

            <br>

            Dicetak pada
            {{ now()->format('d/m/Y H:i') }}

        </div>

    @endforeach

</body>

</html>
