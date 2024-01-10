<?php

// Cek mutasi
function check_mutasi($pegawai, $bulan, $tahun) {
    // Get mutasi terbaru
    $mutasi = $pegawai->mutasi()->whereHas('jenis', function(\Illuminate\Database\Eloquent\Builder $query) {
		return $query->where('remun','=',1);
	})->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();

    // Get mutasi sebelumnya
    $mutasi_sebelum = $pegawai->mutasi()->whereHas('jenis', function(\Illuminate\Database\Eloquent\Builder $query) {
		return $query->where('remun','=',1);
	})->where('bulan','!=',$bulan)->orWhere('tahun','!=',$tahun)->orderBy('tahun','desc')->orderBy('bulan','desc')->first();

    // Jika rangkap jabatan, mengecek perubahan
    if($mutasi && count($mutasi->detail) > 1) {
        $jabatan = [];
        foreach($mutasi_sebelum->detail as $d) {
            if($d->jabatan)
                array_push($jabatan, $d->jabatan->nama);
        }

        $id = '';
        foreach($mutasi->detail as $d) {
            // if(!in_array($d->jabatan_id, $mutasi_sebelum->detail()->pluck('jabatan_id')->toArray())) {
            if($d->status == 1 && !in_array($d->jabatan->nama, $jabatan)) {
                $id = $d->jabatan->nama;
            }
        }
        // if($id != '') return false;
        if($id != '') return true;
        else return false;
    }
    else
        return true;
}

?>

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
        DAFTAR PERHITUNGAN PEMBAYARAN REMUNERASI KOMPONEN GAJI UNSUR TENAGA {{ $kategori == 1 ? 'PENDIDIK' : 'KEPENDIDIKAN' }} UNNES<br>
        BERDASAR {{ $sk->nama }} TANGGAL {{ strtoupper(\Ajifatur\Helpers\DateTimeExt::full($sk->tanggal)) }} BESERTA PERUBAHANNYA<br>
        BULAN {{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($bulan)) }} {{ $tahun }} PADA {{ strtoupper($unit->nama) }}
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
                <th align="center" rowspan="2" width="60"><b>Remunerasi Gaji (30%)</b></th>
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
                <th align="center"><em>12 = 10 + 11</em></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $total_terbayar = 0;
                $total_seharusnya = 0;
                $total_selisih = 0;
                $total_dibayarkan = 0;
            ?>
            @foreach($remun_gaji as $key=>$r)
                <?php
                    $lebih_kurang = \App\Models\LebihKurang::where('pegawai_id','=',$r->pegawai->id)->where('bulan_proses','=',Request::query('bulan'))->where('tahun_proses','=',Request::query('tahun'))->where('triwulan_proses','=',0)->where('selisih','!=',0)->where('kekurangan','=',0)->get();
                    $dibayarkan = $r->remun_gaji + $lebih_kurang->sum('selisih');
                    $mutasi = $r->pegawai->mutasi()->whereHas('jenis', function(\Illuminate\Database\Eloquent\Builder $query) {
						return $query->where('remun','=',1);
					})->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();

                    // Sum total
                    $total_terbayar += $lebih_kurang->sum('terbayar');
                    $total_seharusnya += $lebih_kurang->sum('seharusnya');
                    $total_selisih += $lebih_kurang->sum('selisih');
                    $total_dibayarkan += $dibayarkan;
                ?>
                <tr bgcolor="{{ $mutasi && check_mutasi($r->pegawai, $bulan, $tahun) ? '#ffdd00' : '' }}">
                    <td align="center">{{ ($key+1) }}</td>
                    <td>{{ strtoupper($r->pegawai->nama) }}<br>{{ $r->pegawai->npu != null ? $r->pegawai->npu : $r->pegawai->nip }}</td>
					@if($r->pegawai->golongan)
					<td align="center">{{ $r->pegawai->golongan->nama }}</td>
					@else
					<td align="center">-</td>
					@endif
					{{--
                    @if($r->golru)
                        <td align="center">{{ $r->golru->golongan->nama }}</td>
                    @else
                        @if($r->pegawai->golongan)
                            <td align="center">{{ $r->pegawai->golongan->nama }}</td>
                        @else
                            <td align="center">-</td>
                        @endif
                    @endif
					--}}
                    <td>{{ $r->jabatan ? $r->jabatan->nama : '-' }}</td>
                    @if($r->jabatan && in_array($r->jabatan->nama, ['Koordinator Program Studi A','Koordinator Program Studi B','Koordinator Program Studi C']))
                        <td>
                            @foreach($r->koorprodi as $koorprodi)
                                Koorprodi {{ $koorprodi->prodi->nama }}<br>
                            @endforeach
                        </td>
                    @else
                        <td>{{ $r->jabatan ? $r->jabatan->sub : '-' }}</td>
                    @endif
                    <td align="center">{{ $r->layer ? $r->layer->nama : '-' }}</td>
                    <td align="center">{{ $r->jabatan_dasar ? $r->jabatan_dasar->grade : '-' }}</td>
                    <td align="right">{{ $r->remun_gaji != $dibayarkan ? number_format($lebih_kurang->sum('terbayar'),0,'.','.') : '' }}</td>
                    <td align="right">{{ $r->remun_gaji != $dibayarkan ? number_format($lebih_kurang->sum('seharusnya'),0,'.','.') : '' }}</td>
                    <td align="right">{{ $r->remun_gaji != $dibayarkan ? number_format($lebih_kurang->sum('selisih'),0,'.','.') : '' }}</td>
                    <td align="right">{{ number_format($r->remun_gaji,0,'.','.') }}</td>
                    <td align="right">{{ number_format($dibayarkan,0,'.','.') }}</td>
                </tr>
                @foreach($lebih_kurang as $lk)
                <tr bgcolor="#e3e3e3">
                    <td></td>
                    <td colspan="6" align="center" height="20">{{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($lk->bulan)) }} {{ $lk->tahun }}</td>
                    <td align="right">{{ number_format($lk->terbayar,0,'.','.') }}</td>
                    <td align="right">{{ number_format($lk->seharusnya,0,'.','.') }}</td>
                    <td align="right">{{ number_format($lk->selisih,0,'.','.') }}</td>
                    <td align="right"></td>
                    <td align="right"></td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td align="center" colspan="7" height="20"><b>Jumlah</b></td>
                <td align="right"><b>{{ number_format($total_terbayar,0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($total_seharusnya,0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($total_selisih,0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($remun_gaji->sum('remun_gaji'),0,'.','.') }}</b></td>
                <td align="right"><b>{{ number_format($total_dibayarkan,0,'.','.') }}</b></td>
            </tr>
            <tr id="sign">
                <td colspan="9" width="80%"></td>
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