<html>
<head>
    <title>{{ $title }}</title>
    <style>
        #title {width: 100%; text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 25px; line-height: 20px;}
        table {border-collapse: collapse; font-size: 12.5px;}
        table thead tr th {border: #333 solid 2px;}
        table tbody tr td {padding-right: 3px; padding-left: 3px;}
        table tfoot tr td {border: #333 solid 2px;}
        #sign {margin-top: 20px;}
        .page-break {page-break-after: always;}
        .d-none {display: none;}
    </style>
</head>
<body>
    <div id="title">
        DAFTAR POTONGAN REMUNERASI UNSUR INSENTIF TRIWULAN {{ $triwulan }} BAGI PEJABAT PENGELOLA DAN PEGAWAI PTNBH UNNES<br>TAHUN ANGGARAN {{ $tahun }} PADA TENAGA {{ $kategori == 1 ? 'PENDIDIK' : 'KEPENDIDIKAN' }} {{ Request::query('pusat') == 1 ? 'PUSAT' : strtoupper($unit->nama) }}
    </div>
    <table border="1" style="width: 100%">
        <thead>
            <tr>
                <th align="center" width="30"><b>No</b></th>
                <th align="center"><b>Nama / NIP</b></th>
                <th align="center" width="30"><b>Gol</b></th>
                <th align="center" width="30"><b>Grade</b></th>
                <th align="center" width="105"><b>Jabatan</b></th>
                <th align="center" width="105"><b>Sub Nama Jabatan</b></th>
                <th align="center" width="40"><b>Jumlah Poin</b></th>
                <th align="center" width="60"><b>Jumlah Insentif</b></th>
                <th align="center" width="60"><b>Potongan</b></th>
                <th align="center" width="60"><b>Dibayarkan</b></th>
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
                <th align="center"><em>10 = 8 - 9</em></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $total_remun_insentif = 0;
                $total_potongan = 0;
                $total_dibayarkan = 0;
            ?>
            @foreach($potongan as $key=>$p)
                <?php
                    // Sum total
                    $total_remun_insentif += $p->remun_insentif->remun_insentif;
                    $total_potongan += $p->selisih;
                    $total_dibayarkan += ($p->remun_insentif->remun_insentif + $p->selisih);
                ?>
                <tr>
                    <td align="center">{{ ($key+1) }}</td>
                    <td>{{ strtoupper($p->pegawai->nama) }}<br>{{ $p->pegawai->nip }}</td>
                    @if($p->pegawai->golongan)
                        <td align="center">{{ $p->pegawai->golongan->nama }}</td>
                    @else
                        <td align="center">-</td>
                    @endif
                    <td align="center">{{ $p->remun_insentif->jabatan_dasar ? $p->remun_insentif->jabatan_dasar->grade : '-' }}</td>
                    <td>{{ $p->remun_insentif->jabatan ? $p->remun_insentif->jabatan->nama : '-' }}</td>
                    <td>{{ $p->remun_insentif->jabatan ? $p->remun_insentif->jabatan->sub : '-' }}</td>
                    <td align="right">{{ number_format($p->remun_insentif->poin,2,',',',') }}</td>
                    <td align="right">{{ number_format($p->remun_insentif->remun_insentif,0,'.','.') }}</td>
                    <td align="right">{{ number_format(abs($p->selisih),0,'.','.') }}</td>
                    <td align="right">{{ number_format($p->remun_insentif->remun_insentif + $p->selisih,0,'.','.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td align="center" colspan="7" height="20"><b>Jumlah</b></td>
                <td align="right"><b>{{ number_format($total_remun_insentif,0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format(abs($total_potongan),0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($total_dibayarkan,0,'.','.') }}</b></td>
            </tr>
        </tfoot>
    </table>
    <table width="100%" id="sign">
        <tr>
            <td width="80%"></td>
            <td height="80" valign="top">
                Semarang,
                <br><br>
                Pejabat Pembuat Komitmen
                <br><br><br><br><br>
                Siti Mursidah, S.Pd., M.Si.
                <br>
                NIP. 197710262005022001
            </td>
        </tr>
    </table>
</body>
</html>