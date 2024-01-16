<html>
<head>
    <title>{{ $title }}</title>
    <style>
        #title {width: 100%; text-align: center; font-size: 15px; font-weight: bold; margin-bottom: 25px; line-height: 20px;}
        #identity {font-size: 15px; font-weight: bold; margin-bottom: 25px;}
        table {border-collapse: collapse; font-size: 14px;}
        #data thead tr th {border: #333 solid 1.5px; padding-top: 5px; padding-bottom: 5px;}
        #data tbody tr td {border: #333 solid 1.5px; padding-right: 3px; padding-left: 3px; padding-top: 5px; padding-bottom: 5px;}
        #data tfoot tr td {border: #333 solid 1.5px; padding-right: 3px; padding-left: 3px; padding-top: 5px; padding-bottom: 5px;}
        #data tfoot tr#sign td {border-width: 0px;}
        #sign-content {margin-top: 20px;}
        .page-break {page-break-after: always;}
        .d-none {display: none;}
    </style>
</head>
<body>
    <div id="title">
        DAFTAR PERHITUNGAN PEMBAYARAN UANG MAKAN {{ $anak_satker->jenis == 1 ? 'PNS' : 'PPPK'}} UNIVERSITAS NEGERI SEMARANG<br>
        BULAN {{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($bulan)) }} {{ $tahun }} {{ $anak_satker->jenis == 1 ? 'PADA '.strtoupper($anak_satker->nama) : '' }} ({{ $kategori == 1 ? 'DOSEN' : 'TENDIK' }})
    </div>
    <table style="width: 100%" id="data">
        <thead class="bg-light">
            <tr>
                <th width="15">No</th>
                <th>NIP</th>
                <th>Nama</th>
                <th width="80">Jumlah Hari</th>
                <th width="80">Nominal Kotor</th>
                <th width="80">Potongan</th>
                <th width="80">Nominal Bersih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($uang_makan as $key=>$um)
            <tr>
                <td align="right">{{ ($key+1) }}</td>
                <td>{{ $um->pegawai->nip }}</td>
                <td>{{ strtoupper($um->pegawai->nama) }}</td>
                <td align="right">{{ number_format($um->jmlhari) }}</td>
                <td align="right">{{ number_format($um->kotor) }}</td>
                <td align="right">{{ number_format($um->potongan) }}</td>
                <td align="right">{{ number_format($um->bersih) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" align="center"><b>Total</b></td>
                <td align="right"><b>{{ number_format($uang_makan->sum('jmlhari')) }}</b></td>
                <td align="right"><b>{{ number_format($uang_makan->sum('kotor')) }}</b></td>
                <td align="right"><b>{{ number_format($uang_makan->sum('potongan')) }}</b></td>
                <td align="right"><b>{{ number_format($uang_makan->sum('bersih')) }}</b></td>
            </tr>
            <tr id="sign">
                <td colspan="7" height="80" valign="top">
                    <table width="100%" id="sign-content">
                        <tr>
                            <td width="75%"></td>
                            <td width="25%">
                                <br>
                                Pengelola Gaji/PPABP
                                <br>
                                <img src="{{ public_path('storage/tte/TTE Ari Pamungkas.png') }}" height="50" width="50">
                                <br>
                                Ari Pamungkas, S.E.
                                <br>
                                NIP. 198109242005011001
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>