<table style="width: 100%">
    <thead>
        <tr>
            <th align="center" rowspan="2" width="30"><b>No</b></th>
            <th align="center" rowspan="2"><b>Nama / NPU</b></th>
            <th align="center" rowspan="2" width="30"><b>Gol</b></th>
            <th align="center" rowspan="2" width="50"><b>Status Kawin</b></th>
            <th align="center" colspan="3"><b>Gaji Pokok, Tunj. Suami/Istri, Tunj. Anak</b></th>
            <th align="center" rowspan="2" width="60"><b>Dibayarkan</b></th>
            <th align="center" rowspan="2" width="150"><b>Keterangan</b></th>
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
            <th align="center"><em>7 = 6 - 5</em></th>
            <th align="center"><em>8</em></th>
            <th align="center"><em>9</em></th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach($data['d'] as $npu=>$j)
            @php $k = 0; @endphp
            @foreach($j as $key=>$xxx)
                @if($k == 0)
                <tr>
                    <td align="center">{{ $i }}</td>
                    <td>{{ strtoupper($xxx['pegawai']->nama) }}<br>{{ $xxx['pegawai']->npu != null ? $xxx['pegawai']->npu : $xxx['pegawai']->nip }}</td>
                    @if($xxx['pegawai']->golongan)
                    <td align="center">{{ $xxx['pegawai']->golongan->nama }}</td>
                    @else
                    <td align="center">-</td>
                    @endif
                    <td align="center">{{ $xxx['status_kawin'] }}</td>
                    <td align="right">{{ array_sum($data['t_terbayar'][$npu]) }}</td>
                    <td align="right">{{ array_sum($data['t_seharusnya'][$npu]) }}</td>
                    <td align="right">{{ array_sum($data['t_selisih'][$npu]) }}</td>
                    <td align="right">{{ array_sum($data['t_selisih'][$npu])}}</td>
                    <td></td>
                </tr>
                @endif

                @php $x = 0; @endphp
                @foreach($xxx['rincian'] as $key2=>$rincian)
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
                            <td rowspan="{{ count($xxx['rincian']) }}">{{ $xxx['keterangan'] }}<br>TMT: {{ $xxx['tmt'] }}</td>
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
</table>