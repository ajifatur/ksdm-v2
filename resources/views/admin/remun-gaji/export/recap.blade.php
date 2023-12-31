<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="25"><b>NIP</b></th>
            <th align="center" width="40"><b>NAMA</b></th>
            <th align="center" width="25"><b>UNIT</b></th>
            <th align="center" width="10"><b>GOL</b></th>
            <th align="center" width="10"><b>LAYER</b></th>
            <th align="center" width="10"><b>GRADE</b></th>
            <th align="center" width="25"><b>STATUS</b></th>
            <th align="center" width="10"><b>JENIS</b></th>
            <th align="center" width="40"><b>JABATAN</b></th>
            <th align="center" width="40"><b>SUB NAMA</b></th>
            <th align="center" width="15"><b>REMUN GAJI</b></th>
            <th align="center" width="15"><b>DIBAYARKAN</b></th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $d)
            <?php
                $lebih_kurang = \App\Models\LebihKurang::where('pegawai_id','=',$d->pegawai->id)->where('bulan_proses','=',Request::query('bulan'))->where('tahun_proses','=',Request::query('tahun'))->where('triwulan_proses','=',0)->where('kekurangan','=',0)->get();
                $dibayarkan = $d->remun_gaji + $lebih_kurang->sum('selisih');
            ?>
            <tr>
                <td>{{ $d->pegawai->nip }}</td>
                <td>{{ strtoupper($d->pegawai->nama) }}</td>
                <td>{{ $d->unit ? $d->unit->nama : '-' }}</td>
                @if($d->pegawai->golongan)
                    <td align="center">{{ $d->pegawai->golongan->nama }}</td>
                @else
                    <td align="center">-</td>
                @endif
                <td>{{ $d->unit ? $d->unit->layer_id : '-' }}</td>
                <td>{{ $d->jabatan_dasar ? $d->jabatan_dasar->grade : '-' }}</td>
                <td>{{ $d->status_kepegawaian ? $d->status_kepegawaian->nama : '-' }}</td>
                <td>{{ $d->kategori == 1 ? 'Dosen' : 'Tendik' }}</td>
                <td>{{ $d->jabatan ? $d->jabatan->nama : '-' }}</td>
                <td>{{ $d->jabatan ? $d->jabatan->sub : '-' }}</td>
                <td>{{ $d->remun_gaji }}</td>
                <td>{{ $dibayarkan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>