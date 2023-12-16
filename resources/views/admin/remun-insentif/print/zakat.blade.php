<html>
<head>
    <title>{{ $title }}</title>
    <style>
        #title {width: 100%; text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 25px; line-height: 20px;}
        table {border-collapse: collapse; font-size: 12.5px;}
        table thead tr th {border: #333 solid 2px; height: 35px;}
        table tbody tr td {border: #333 solid 1px; padding-right: 3px; padding-left: 3px;}
        table tfoot tr td {border: #333 solid 2px;}
        table tfoot tr#sign td {border-width: 0px;}
        table tfoot #sign-content {margin-top: 20px; margin-left: 70%;}
        #sign {margin-top: 20px;}
        .page-break {page-break-after: always;}
        .d-none {display: none;}
    </style>
</head>
<body>
    <div id="title">
        DAFTAR PEMOTONGAN AMAL JARIYAH<br>PEMBAYARAN REMUNERASI UNSUR INSENTIF TRIWULAN {{ $romawi[$triwulan-1] }} TAHUN {{ $tahun }} BAGI TENAGA PENDIDIK<br>DAN TENAGA KEPENDIDIKAN {{ Request::query('pusat') == 1 ? 'PUSAT' : strtoupper($unit->nama) }} UNNES
    </div>
    <table style="width: 100%">
        <thead>
            <tr>
                <th align="center" width="30"><b>No</b></th>
                <th align="center"><b>Nama / NIP</b></th>
                <th align="center" width="60"><b>Jumlah (Rp)</b></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $total = 0;
                $i = 1;
            ?>
            @foreach($remun_insentif_dosen as $key=>$r)
                <tr>
                    <td align="center">{{ $i }}</td>
                    <td>{{ strtoupper($r->pegawai->nama) }}<br>{{ $r->pegawai->nip }}</td>
                    <td align="right">{{ number_format($r->pot_zakat,0,'.','.') }}</td>
                </tr>
                <?php
                    $total += $r->pot_zakat;
                    $i++;
                ?>
            @endforeach
            @foreach($remun_insentif_tendik as $key=>$r)
                <tr>
                    <td align="center">{{ $i }}</td>
                    <td>{{ strtoupper($r->pegawai->nama) }}<br>{{ $r->pegawai->nip }}</td>
                    <td align="right">{{ number_format($r->pot_zakat,0,'.','.') }}</td>
                </tr>
                <?php
                    $total += $r->pot_zakat;
                    $i++;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td align="center" colspan="2" height="20"><b>Jumlah</b></td>
                <td align="right"><b>{{ number_format($total,0,'.','.') }}</b></td>
            </tr>
            <tr id="sign">
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
    <!-- <table width="100%" id="sign">
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
    </table> -->
</body>
</html>