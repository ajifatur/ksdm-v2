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
        DAFTAR PERHITUNGAN PEMBAYARAN {{ strtoupper($nama) }} UNNES<br>
        SESUAI {{ $header }}
    </div>
    <table border="0" style="width: 100%" id="identity">
        <tr>
            <td style="width: 80%">
                <table style="width: 100%">
                    <tr>
                        <td width="150">SATUAN KERJA</td>
                        <td width="10">:</td>
                        <td>UNIVERSITAS NEGERI SEMARANG</td>
                    </tr>
                    <tr>
                        <td>BULAN</td>
                        <td>:</td>
                        <td>{{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($bulan)) }} {{ $tahun }}</td>
                    </tr>
                    <tr>
                        <td>MAK</td>
                        <td>:</td>
                        <td>{{ $jenis->mak }}</td>
                    </tr>
                </table>
            </td>
            @if(isset($angkatan))
            <td>
                <table style="width: 100%">
                    <tr>
                        <td width="60">ANGKATAN</td>
                        <td width="10">:</td>
                        <td>{{ $angkatan->nama }}</td>
                    </tr>
                </table>
            </td>
            @endif
        </tr>
    </table>
    <table style="width: 100%" id="data">
        <thead>
            <tr>
                <th align="center" width="20" height="20"><b>No</b></th>
                <th align="center"><b>Nama</b></th>
                <th align="center" width="60"><b>NIP</b></th>
                <th align="center" width="30"><b>Gol.</b></th>
                <th align="center" width="80"><b>Unit Kerja</b></th>
                @if(!isset($angkatan))
                <th align="center" width="60"><b>Angkatan</b></th>
                @endif
                <th align="center" width="60"><b>Gaji Pokok</b></th>
                <th align="center" width="40"><b>Frekuensi</b></th>
                <th align="center" width="60"><b>Tunjangan</b></th>
                <th align="center" width="60"><b>PPh Ps. 21</b></th>
                <th align="center" width="60"><b>Diterimakan</b></th>
                <th align="center" width="60"><b>Rekening</b></th>
            </tr>
        </thead>
        <tbody>
            @foreach($tunjangan as $key=>$t)
                <?php
                    $gaji_pokok = $t->gaji_pokok ? $t->gaji_pokok->gaji_pokok : ($t->angkatan->jenis_id == 1 ? $t->tunjangan / 2 : $t->tunjangan);
                    $frekuensi = $t->angkatan->jenis_id == 1 ? $t->tunjangan / $gaji_pokok / 2 : $t->tunjangan / $gaji_pokok;
                ?>
                <tr>
                    <td align="center" height="20">{{ ($key+1) }}</td>
                    <td>{{ strtoupper($t->nama) }}</td>
                    <td align="center">{{ $t->nip }}</td>
                    <td align="center">{{ $t->golongan ? $t->golongan->nama : '-' }}</td>
                    <td>{{ $t->unit ? $t->unit->nama : '-' }}</td>
                    @if(!isset($angkatan))
                    <td>{{ $t->angkatan->nama }}</td>
                    @endif
                    <td align="right">{{ number_format($gaji_pokok,0,'.','.') }}</td>
                    <td align="right">{{ number_format($frekuensi,0,'.','.') }}</td>
                    <td align="right">{{ number_format($t->tunjangan,0,'.','.') }}</td>
                    <td align="right">{{ number_format($t->pph,0,'.','.') }}</td>
                    <td align="right">{{ number_format($t->diterimakan,0,'.','.') }}</td>
                    <td>{{ $t->nomor_rekening }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td align="center" colspan="{{ !isset($angkatan) ? 8 : 7 }}" height="20"><b>Jumlah</b></td>
                <!-- <td align="right"><b>{{ $t->angkatan->jenis_id == 1 ? number_format($tunjangan->sum('tunjangan') / 2,0,'.','.') : number_format($tunjangan->sum('tunjangan'),0,'.','.') }}</b></td> -->
                <td align="right"><b>{{ number_format($tunjangan->sum('tunjangan'),0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($tunjangan->sum('pph'),0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($tunjangan->sum('diterimakan'),0,'.','.') }}</b></td>
                <td></td>
            </tr>
            <tr id="sign">
                <td colspan="{{ !isset($angkatan) ? 12 : 11 }}" height="80" valign="top">
                    <table width="100%" id="sign-content">
                        <tr>
                            <td width="40%">
                                Mengetahui
                                <br>
                                a.n. Kuasa Pengguna Anggaran
                                <br>
                                Pejabat Pembuat Komitmen
                                <br><br><br><br>
                                {{ title_name(ttd('ppk', $tanggal)->pegawai->nama, ttd('ppk', $tanggal)->pegawai->gelar_depan, ttd('ppk', $tanggal)->pegawai->gelar_belakang) }}
                                <br>
                                NIP. {{ ttd('ppk', $tanggal)->pegawai->nip }}
                            </td>
                            <td width="30%">
                                <br><br>
                                Bendahara Pengeluaran
                                <br><br><br><br>
                                {{ title_name(ttd('bpeng', $tanggal)->pegawai->nama, ttd('bpeng', $tanggal)->pegawai->gelar_depan, ttd('bpeng', $tanggal)->pegawai->gelar_belakang) }}
                                <br>
                                NIP. {{ ttd('bpeng', $tanggal)->pegawai->nip }}
                            </td>
                            <td width="30%">
                                <br>
                                <!-- Semarang, {{ date('j') }} {{ \Ajifatur\Helpers\DateTimeExt::month(date('n')) }} {{ date('Y') }} -->
                                <br>
                                Petugas Pengelolaan Administrasi Belanja Pegawai
                                <br><br><br><br>
                                {{ title_name(ttd('ppabp_1', $tanggal)->pegawai->nama, ttd('ppabp_1', $tanggal)->pegawai->gelar_depan, ttd('ppabp_1', $tanggal)->pegawai->gelar_belakang) }}
                                <br>
                                NIP. {{ ttd('ppabp_1', $tanggal)->pegawai->nip }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>