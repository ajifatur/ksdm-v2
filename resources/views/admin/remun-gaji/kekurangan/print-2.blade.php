<html>
<head>
    <title>{{ $title }}</title>
    <style>
        #title {width: 100%; text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 25px; line-height: 20px;}
        table {border-collapse: collapse; font-size: 12.5px;}
        table thead tr th {border: #333 solid 2px;}
        table tbody tr td {border: #333 solid 1px; padding-right: 3px; padding-left: 3px;}
        table tfoot tr td {border: #333 solid 2px;}
        table tfoot tr#sign td {border-width: 0px;}
        table tfoot #sign-content {margin-top: 20px;}
        #sign {margin-top: 20px;}
        .page-break {page-break-after: always;}
        .d-none {display: none;}
    </style>
</head>
<body>
    <div id="title">
        DAFTAR PERHITUNGAN KEKURANGAN PEMBAYARAN REMUNERASI KOMPONEN GAJI UNSUR TENAGA {{ $kategori == 1 ? 'PENDIDIK' : 'KEPENDIDIKAN' }} UNNES<br>
        BERDASAR KEPUTUSAN REKTOR NOMOR B/72/UN37/HK/2023 TANGGAL 20 JANUARI 2023 BESERTA PERUBAHANNYA<br>
        BULAN {{ strtoupper($rentang_bulan) }} 2023 {{ $unit ? 'PADA '.strtoupper($unit->nama) : '' }}
    </div>
    <table style="width: 100%">
        <thead>
            <tr>
                <th align="center" rowspan="2" width="30"><b>No</b></th>
                <th align="center" rowspan="2"><b>Nama / NIP</b></th>
                <th align="center" rowspan="2" width="30"><b>Gol</b></th>
                <th align="center" rowspan="2" width="105"><b>Jabatan</b></th>
                <th align="center" rowspan="2" width="105"><b>Sub Nama Jabatan</b></th>
                <th align="center" rowspan="2" width="30"><b>Layer</b></th>
                <th align="center" rowspan="2" width="30"><b>Grade</b></th>
                <th align="center" colspan="3"><b>Kekurangan/Kelebihan Pembayaran Remunerasi Gaji</b></th>
                <th align="center" rowspan="2" width="60"><b>Dibayarkan</b></th>
            </tr>
            <tr>
                <th align="center" width="60"><b>Terbayar</b></th>
                <th align="center" width="60"><b>Seharusnya</b></th>
                <th align="center" width="60"><b>Selisih</b></th>
            </tr>
            <tr>
                <th align="center"><em>1</em></th>
                <th align="center"><em>2</em></th>
                <th align="center"><em>3</em></th>
                <th align="center"><em>4</em></th>
                <th align="center"><em>5</em></th>
                <th align="center"><em>6</em></th>
                <th align="center"><em>7</em></th>
                <th align="center"><em>8</em></th>
                <th align="center"><em>9</em></th>
                <th align="center"><em>10 = 9 - 8</em></th>
                <th align="center"><em>11</em></th>
            </tr>
        </thead>
        <tbody>
            @foreach($kekurangan as $key=>$k)
                <tr>
                    <td align="center">{{ ($key+1) }}</td>
                    <td>{{ strtoupper($k->pegawai->nama) }}<br>{{ $k->pegawai->nip }}</td>
                    @if($k->mutasi && $k->mutasi->golru && $k->mutasi->golru->golongan)
					<td align="center">{{ $k->mutasi->golru->golongan->nama }}</td>
                    @else
					<td align="center">{{ $k->pegawai->golongan->nama }}</td>
                    @endif
                    @if($k->mutasi)
                        <td>{{ $k->mutasi->detail()->where('status','=',1)->first()->jabatan ? $k->mutasi->detail()->where('status','=',1)->first()->jabatan->nama : '-' }}</td>
                        <td>{{ $k->mutasi->detail()->where('status','=',1)->first()->jabatan ? $k->mutasi->detail()->where('status','=',1)->first()->jabatan->sub : '-' }}</td>
                        <td align="center">{{ $k->mutasi->detail()->where('status','=',1)->first()->layer ? $k->mutasi->detail()->where('status','=',1)->first()->layer->nama : '-' }}</td>
                        <td align="center">{{ $k->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar ? $k->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar->grade : '-' }}</td>
                    @else
                        <td>-</td>
                        <td>-</td>
                        <td align="center">-</td>
                        <td align="center">-</td>
                    @endif
                    <td align="right">{{ number_format($k->total_terbayar,0,'.','.') }}</td>
                    <td align="right">{{ number_format($k->total_seharusnya,0,'.','.') }}</td>
                    <td align="right">{{ number_format($k->total_selisih,0,'.','.') }}</td>
                    <td align="right">{{ $k->total_selisih < 0 ? 0 : number_format($k->total_selisih,0,'.','.') }}</td>
                </tr>
                @foreach($k->detail as $d)
                    <tr bgcolor="#e3e3e3">
                        <td></td>
                        <td colspan="6" align="center" height="20">{{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($d->bulan)) }} {{ $d->tahun }}</td>
                        <td align="right">{{ number_format($d->terbayar,0,'.','.') }}</td>
                        <td align="right">{{ number_format($d->seharusnya,0,'.','.') }}</td>
                        <td align="right">{{ number_format($d->selisih,0,'.','.') }}</td>
                        <td align="right"></td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td align="center" colspan="7" height="20"><b>Jumlah</b></td>
                <td align="right"><b>{{ number_format($total['terbayar'],0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($total['seharusnya'],0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($total['selisih'],0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($total['selisih_plus'],0,'.','.') }}</b></td>
            </tr>
            <tr id="sign">
                <td colspan="8" width="80%"></td>
                <td colspan="3" height="80" valign="top">
                    <div id="sign-content">
                        Semarang,
                        <br><br>
                        Pejabat Pembuat Komitmen
                        <br><br><br><br><br>
                        Siti Mursidah, S.Pd., M.Si.
                        <br>
                        NIP. 197710262005022001
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>