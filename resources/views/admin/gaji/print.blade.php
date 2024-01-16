<html>
<head>
    <title>{{ $title }}</title>
    <style>
        #title {width: 100%; text-align: center; font-size: 15px; font-weight: bold; margin-bottom: 25px; line-height: 20px;}
        #identity {font-size: 15px; font-weight: bold; margin-bottom: 25px;}
        table {border-collapse: collapse; font-size: 14px;}
        #data thead tr th {border: #333 solid 1.5px;}
        #data tbody tr td {border: #333 solid 1.5px; padding-right: 3px; padding-left: 3px;}
        #data tfoot tr td {border: #333 solid 1.5px; padding-right: 3px; padding-left: 3px;}
        #data tfoot tr#sign td {border-width: 0px;}
        #sign-content {margin-top: 20px;}
        .page-break {page-break-after: always;}
        .d-none {display: none;}
    </style>
</head>
<body>
    <div id="title">
        DAFTAR PERHITUNGAN PEMBAYARAN {{ strtoupper($jenis->nama) }} {{ $anak_satker->jenis == 1 ? 'PNS' : 'PPPK'}} UNIVERSITAS NEGERI SEMARANG<br>
        BULAN {{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($bulan)) }} {{ $tahun }} {{ $anak_satker->jenis == 1 ? 'PADA '.$anak_satker->nama : '' }} ({{ $kategori == 1 ? 'DOSEN' : 'TENDIK' }})
    </div>
    <table style="width: 100%" id="data">
        <thead class="bg-light">
            <tr>
                <th rowspan="2" width="5">No</th>
                <th rowspan="2">Nama / NIP</th>
                <th colspan="4">Penghasilan</th>
                <th colspan="3">Potongan</th>
                <th rowspan="2" width="80">Gaji Bersih</th>
            </tr>
            <tr>
                <th width="80">Gaji Pokok,<br>Tunj. Istri,<br>Tunj. Anak</th>
                <th width="80">Tunj. Fungsional,<br>Tunj. Struktural,<br>Tunj. Umum</th>
                <th width="80">Tunj. Beras,<br>Tunj. Kh. Pajak,<br>Pembulatan</th>
                <th width="80">Jumlah Penghasilan Kotor</th>
                <th width="80">IWP,<br>BPJS,<br>BPJS2</th>
                <th width="80">Pajak Penghasilan</th>
                <th width="80">Jumlah Potongan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gaji as $key=>$g)
            <tr>
                <td align="right">{{ ($key+1) }}</td>
                <td>{{ strtoupper($g->pegawai->nama) }}<br>{{ $g->pegawai->nip }}</td>
                <td align="right">{{ number_format($g->gjpokok) }}<br>{{ number_format($g->tjistri) }}<br>{{ number_format($g->tjanak) }}</td>
                <td align="right">{{ number_format($g->tjfungs) }}<br>{{ number_format($g->tjstruk) }}<br>{{ number_format($g->tjupns) }}</td>
                <td align="right">{{ number_format($g->tjberas) }}<br>{{ number_format($g->tjpph) }}<br>{{ number_format($g->pembul) }}</td>
                <td align="right">{{ number_format($g->nominal) }}</td>
                <td align="right">{{ number_format($g->potpfk10) }}<br>{{ number_format($g->bpjs) }}<br>{{ number_format($g->bpjs2) }}</td>
                <td align="right">{{ number_format($g->potpph) }}</td>
                <td align="right">{{ number_format($g->potongan) }}</td>
                <td align="right">{{ number_format($g->nominal - $g->potongan) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" align="center"><b>Total</b></td>
                <td align="right"><b>{{ number_format($gaji->sum('gjpokok')) }}<br>{{ number_format($gaji->sum('tjistri')) }}<br>{{ number_format($gaji->sum('tjanak')) }}</b></td>
                <td align="right"><b>{{ number_format($gaji->sum('tjfungs')) }}<br>{{ number_format($gaji->sum('tjstruk')) }}<br>{{ number_format($gaji->sum('tjupns')) }}</b></td>
                <td align="right"><b>{{ number_format($gaji->sum('tjberas')) }}<br>{{ number_format($gaji->sum('tjpph')) }}<br>{{ number_format($gaji->sum('pembul')) }}</b></td>
                <td align="right"><b>{{ number_format($gaji->sum('nominal')) }}</b></td>
                <td align="right"><b>{{ number_format($gaji->sum('potpfk10')) }}<br>{{ number_format($gaji->sum('bpjs')) }}<br>{{ number_format($gaji->sum('bpjs2')) }}</b></td>
                <td align="right"><b>{{ number_format($gaji->sum('potpph')) }}</b></td>
                <td align="right"><b>{{ number_format($gaji->sum('potongan')) }}</b></td>
                <td align="right"><b>{{ number_format($gaji->sum('nominal') - $gaji->sum('potongan')) }}</b></td>
            </tr>
            <tr id="sign">
                <td colspan="10" height="80" valign="top">
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