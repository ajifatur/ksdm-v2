<html>
<head>
    <title>{{ $title }}</title>
    <style>
        #title {width: 100%; text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 25px; line-height: 20px;}
        #data {border-collapse: collapse; font-size: 11px;}
        #data thead tr th {border: #333 solid 1.5px;word-wrap: break-word; word-break: break-all;}
        #data tbody tr td {border: #333 solid 1.5px; padding-right: 3px; padding-left: 3px;}
        #data tfoot tr td {border: #333 solid 1.5px; padding-right: 3px; padding-left: 3px;}
        #data tfoot tr#sign td {border-width: 0px;}
        #sign-content {margin-top: 20px; font-size: 12px;}
        .page-break {page-break-after: always;}
        .d-none {display: none;}
    </style>
</head>
<body>
    <div id="title">
        DAFTAR PEMBAYARAN GAJI PEGAWAI TIDAK TETAP {{ $kategori ? $kategori->kategori == 1 ? 'TENAGA PENDIDIK' : 'TENAGA KEPENDIDIKAN' : '' }}<br>
        UNIVERSITAS NEGERI SEMARANG<br>
        {{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($bulan)) }} {{ $tahun }}
    </div>
    <table style="width: 100%" id="data">
        <thead class="bg-light">
            <tr>
                <th align="center" valign="middle" width="8" height="60"><b>No</b></th>
                <th align="center" valign="middle"><b>Nama/NIK/NPWP</b></th>
                <th align="center" valign="middle" width="20"><b>NRP/NPU</b></th>
                <th align="center" valign="middle" width="14"><b>Status Kawin /<br>Status Pajak</b></th>
                <th align="center" valign="middle" width="16"><b>No & Tanggal<br>Perjanjian Kerja</b></th>
                <th align="center" valign="middle" width="14" bgcolor="#00B050"><b>Gaji Pokok</b></th>
                @if(($kategori && $kategori->kategori == 1) || $kategori == null)
                <th align="center" valign="middle" width="14" bgcolor="#00B050"><b>Tunjangan Dosen NIDK</b></th>
                @endif
                <th align="center" valign="middle" width="14" bgcolor="#00B050"><b>Tunjangan Lainnya</b></th>
                <th align="center" valign="middle" width="14" bgcolor="#00B050"><b>Tunjangan<br>BPJS<br>Kesehatan (4%)</b></th>
                <th align="center" valign="middle" width="14" bgcolor="#00B050"><b>Tunjangan<br>BPJS<br>Ketenagakerjaan</b></th>
                <th align="center" valign="middle" width="14"><b>Jumlah Penghasilan Kotor</b></th>
                <th align="center" valign="middle" width="14" bgcolor="#FFFF00"><b>Iuran<br>BPJS<br>Kesehatan (1%)</b></th>
                <th align="center" valign="middle" width="14" bgcolor="#FFFF00"><b>Iuran<br>BPJS<br>Ketenagakerjaan (3%)</b></th>
                <th align="center" valign="middle" width="14" bgcolor="#FFFF00"><b>Jumlah<br>BPJS<br>Kesehatan</b></th>
                <th align="center" valign="middle" width="14" bgcolor="#FFFF00"><b>Jumlah<br>BPJS<br>Ketenagakerjaan</b></th>
                <th align="center" valign="middle" width="14"><b>Jumlah Diterimakan</b></th>
                <th align="center" valign="middle" width="20" bgcolor="#FFFF00"><b>No Rekening</b></th>
            </tr>
            <tr>
                <th align="center" valign="middle" height="12"><em>1</em></th>
                <th align="center" valign="middle"><em>2</em></th>
                <th align="center" valign="middle"><em>3</em></th>
                <th align="center" valign="middle"><em>4</em></th>
                <th align="center" valign="middle"><em>5</em></th>
                <th align="center" valign="middle"><em>6</em></th>
                @if(($kategori && $kategori->kategori == 1) || $kategori == null)
                <th align="center" valign="middle"><em>7</em></th>
                @endif
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? 8 : 7 }}</em></th>
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? 9 : 8 }}</em></th>
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? 10 : 9 }}</em></th>
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? '11 = 6+7+8+9+10' : '10 = 6+7+8+9' }}</em></th>
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? 12 : 11 }}</em></th>
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? 13 : 12 }}</em></th>
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? '14 = 9+12' : '13 = 8+11' }}</em></th>
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? '15 = 10+13' : '14 = 9+12' }}</em></th>
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? '16 = 11-14-15' : '15 = 10-13-14' }}</em></th>
                <th align="center" valign="middle"><em>{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? 17 : 16 }}</em></th>
            </tr>
        </thead>
        <tbody>
            @foreach($gaji as $key=>$g)
            <tr>
                <td align="center" valign="middle">{{ $key+1 }}</td>
                <td valign="middle">{{ strtoupper($g->pegawai->nama) }}<br>{{ $g->pegawai->nik }}<br>{{ $g->pegawai->npwp }}</td>
                <td valign="middle">{{ $g->pegawai->npu != null ? $g->pegawai->npu : $g->pegawai->nip }}</td>
                <td align="center" valign="middle">{{ $g->status_kawin }}<br>{{ $g->status_pajak }}</td>
                <td valign="middle">{{ substr($g->sk->no_sk,0,15) }}<br>{{ substr($g->sk->no_sk,15) }}</td>
                <td align="right" valign="middle">{{ number_format($g->gjpokok,0,'.','.') }}</td>
                @if(($kategori && $kategori->kategori == 1) || $kategori == null)
                <td align="right" valign="middle">{{ number_format($g->tjdosen,0,'.','.') }}</td>
                @endif
                <td align="right" valign="middle">{{ number_format($g->tjlain,0,'.','.') }}</td>
                <td align="right" valign="middle">{{ number_format($g->tjbpjskes4,0,'.','.') }}</td>
                <td align="right" valign="middle">{{ number_format($g->tjbpjsket,0,'.','.') }}</td>
                <td align="right" valign="middle">{{ number_format($g->kotor,0,'.','.') }}</td>
                <td align="right" valign="middle">{{ number_format($g->iuranbpjskes1,0,'.','.') }}</td>
                <td align="right" valign="middle">{{ number_format($g->iuranbpjsket3,0,'.','.') }}</td>
                <td align="right" valign="middle">{{ number_format($g->jmlbpjskes,0,'.','.') }}</td>
                <td align="right" valign="middle">{{ number_format($g->jmlbpjsket,0,'.','.') }}</td>
                <td align="right" valign="middle">{{ number_format($g->bersih,0,'.','.') }}</td>
                <td align="right" valign="middle">{{ $g->nomor_rekening }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td align="center" valign="middle" colspan="5" height="45"><b>JUMLAH</b></td>
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('gjpokok'),0,'.','.') }}</b></td>
                @if(($kategori && $kategori->kategori == 1) || $kategori == null)
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('tjdosen'),0,'.','.') }}</b></td>
                @endif
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('tjlain'),0,'.','.') }}</b></td>
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('tjbpjskes4'),0,'.','.') }}</b></td>
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('tjbpjsket'),0,'.','.') }}</b></td>
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('kotor'),0,'.','.') }}</b></td>
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('iuranbpjskes1'),0,'.','.') }}</b></td>
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('iuranbpjsket3'),0,'.','.') }}</b></td>
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('jmlbpjskes'),0,'.','.') }}</b></td>
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('jmlbpjsket'),0,'.','.') }}</b></td>
                <td align="right" valign="middle"><b>{{ number_format($gaji->sum('bersih'),0,'.','.') }}</b></td>
                <td></td>
            </tr>
            <tr id="sign">
                <td colspan="{{ ($kategori && $kategori->kategori == 1) || $kategori == null ? 17 : 16 }}" height="80" valign="top">
                    <table width="100%" id="sign-content">
                        <tr>
                            <td width="3%"></td>
                            <td width="72%">
                                <br>
                                Mengetahui,
                                <br>
                                Pejabat Pembuat Komitmen
                                <br>
                                <br>
                                <br>
                                <br>
                                <br>
                                Siti Mursidah, S.Pd., M.Si.
                                <br>
                                NIP. 197710262005022001
                            </td>
                            <td width="25%">
                                Semarang, {{ \Ajifatur\Helpers\DateTimeExt::full(date('Y-m-d')) }}
                                <br>
                                <br>
                                PPABP
                                <br>
                                <br>
                                <br>
                                <br>
                                <br>
                                Sonny Broma Migusti, S.E.
                                <br>
                                NPU. 1985081220231021001
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>