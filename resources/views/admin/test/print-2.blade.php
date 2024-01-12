<html>
<head>
    <title>DAFTAR PERHITUNGAN PEMBAYARAN KEKURANGAN GAJI PEGAWAI TETAP NON ASN UNNES</title>
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
        hr {margin-top: 0.75px; margin-bottom: 0.75px;}
    </style>
</head>
<body>
    <div id="title">
        DAFTAR PERHITUNGAN PEMBAYARAN KEKURANGAN GAJI PEGAWAI TETAP NON ASN
        <br>
        UNIVERSITAS NEGERI SEMARANG
        <br>
        TAHUN 2023
    </div>
    <table style="width: 100%">
        <thead>
            <tr>
                <th align="center" rowspan="2" width="30"><b>No</b></th>
                <th align="center" rowspan="2"><b>Nama / NPU</b></th>
                <th align="center" rowspan="2" width="30"><b>Gol</b></th>
                <th align="center" rowspan="2" width="50"><b>Status Kawin</b></th>
                <th align="center" colspan="4"><b>Gaji Pokok, Tunj. Suami/Istri, Tunj. Anak</b></th>
                <th align="center" rowspan="2" width="150"><b>Keterangan</b></th>
            </tr>
            <tr>
                <th align="center" width="60"><b>Terbayar</b></th>
                <th align="center" width="60"><b>Seharusnya</b></th>
                <th align="center" width="60"><b>Selisih</b></th>
                <th align="center" width="60"><b>Dibayarkan</b></th>
            </tr>
            <tr>
                <th align="center"><em>1</em></th>
                <th align="center"><em>2</em></th>
                <th align="center"><em>3</em></th>
                <th align="center"><em>4</em></th>
                <th align="center"><em>5</em></th>
                <th align="center"><em>6</em></th>
                <th align="center"><em>7 = 6 - 5</em></th>
                <th align="center"><em>8</em></th>
                <th align="center"><em>9</em></th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($d as $npu=>$j)
                @php $k = 0; @endphp
                @foreach($j as $key=>$data)
                    @if($k == 0)
                    <tr>
                        <td align="center">{{ $i }}</td>
                        <td>{{ strtoupper($data['pegawai']->nama) }}<br>{{ $data['pegawai']->npu != null ? $data['pegawai']->npu : $data['pegawai']->nip }}</td>
                        @if($data['pegawai']->golongan)
                        <td align="center">{{ $data['pegawai']->golongan->nama }}</td>
                        @else
                        <td align="center">-</td>
                        @endif
                        <td align="center">{{ $data['status_kawin'] }}</td>
                        <td align="right">{{ number_format(array_sum($t_terbayar[$npu]),0,'.','.') }}</td>
                        <td align="right">{{ number_format(array_sum($t_seharusnya[$npu]),0,'.','.') }}</td>
                        <td align="right">{{ number_format(array_sum($t_selisih[$npu]),0,'.','.') }}</td>
                        <td align="right">{{ number_format(array_sum($t_selisih[$npu]),0,'.','.') }}</td>
                        <td></td>
                    </tr>
                    @endif

                    @php $x = 0; @endphp
                    @foreach($data['rincian'] as $key2=>$rincian)
                        @foreach($rincian as $key3=>$r)
                            @if($key3 == 0)
                            <tr bgcolor="#e3e3e3">
                                <td></td>
                                <td colspan="2" align="center" height="20">
                                    @if(count($rincian) > 1)
                                        {{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($rincian[count($rincian)-1]->bulan)) }} {{ $rincian[count($rincian)-1]->tahun }} s.d.
                                        {{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($r->bulan)) }} {{ $r->tahun }}
                                    @else
                                        {{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($r->bulan)) }} {{ $r->tahun }}
                                    @endif
                                    ( {{ count($rincian) }} X )
                                </td>
                                <td align="center">{{ $r->status_kawin }}</td>
                                <td align="right">
                                    {{ number_format($r->gjpokok,0,'.','.') }}<br>
                                    {{ number_format($r->tjistri,0,'.','.') }}<br>
                                    {{ number_format($r->tjanak,0,'.','.') }}
                                    <hr>
                                    <b>{{ number_format($r->gjpokok + $r->tjistri + $r->tjanak,0,'.','.') }}</b>
                                </td>
                                <td align="right">
                                    {{ number_format($r->gjpokok_seharusnya,0,'.','.') }}<br>
                                    {{ number_format($r->tjistri_seharusnya,0,'.','.') }}<br>
                                    {{ number_format($r->tjanak_seharusnya,0,'.','.') }}
                                    <hr>
                                    <b>{{ number_format($r->gjpokok_seharusnya + $r->tjistri_seharusnya + $r->tjanak_seharusnya,0,'.','.') }}</b>
                                </td>
                                <td align="right">
                                    {{ number_format($r->gjpokok_selisih,0,'.','.') }}<br>
                                    {{ number_format($r->tjistri_selisih,0,'.','.') }}<br>
                                    {{ number_format($r->tjanak_selisih,0,'.','.') }}
                                    <hr>
                                    <b>{{ number_format($r->gjpokok_selisih + $r->tjistri_selisih + $r->tjanak_selisih,0,'.','.') }}</b>
                                </td>
                                <td align="right">
                                    {{ number_format($r->gjpokok_selisih * count($rincian),0,'.','.') }}<br>
                                    {{ number_format($r->tjistri_selisih * count($rincian),0,'.','.') }}<br>
                                    {{ number_format($r->tjanak_selisih * count($rincian),0,'.','.') }}
                                    <hr>
                                    <b>{{ number_format(($r->gjpokok_selisih + $r->tjistri_selisih + $r->tjanak_selisih) * count($rincian),0,'.','.') }}</b>
                                </td>
                                @if($x == 0)
                                <td rowspan="{{ count($data['rincian']) }}">{{ $data['keterangan'] }}<br>TMT: {{ $data['tmt'] }}</td>
                                @endif
                            </tr>
                            @endif
                        @endforeach
                        @php $x++; @endphp
                    @endforeach
                    @php $k++; @endphp
                @endforeach
                @php $i++; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td align="center" colspan="4" height="20"><b>Jumlah</b></td>
                <td align="right"><b>{{ number_format($grand_total['terbayar'],0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($grand_total['seharusnya'],0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($grand_total['selisih'],0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($grand_total['selisih'],0,'.','.') }}</b></td>
                <td></td>
            </tr>
            <tr id="sign">
                <td colspan="7" width="80%"></td>
                <td colspan="2" height="80" valign="top">
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