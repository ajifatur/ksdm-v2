<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="25"><b>NIP</b></th>
            <th align="center" width="40"><b>NAMA</b></th>
            <th align="center" width="10"><b>GOL</b></th>
            <th align="center" width="25"><b>UNIT</b></th>
            <th align="center" width="10"><b>LAYER</b></th>
            <th align="center" width="10"><b>GRADE</b></th>
            <th align="center" width="40"><b>JABATAN</b></th>
            <th align="center" width="40"><b>SUB NAMA</b></th>
            <th align="center" width="25"><b>STATUS</b></th>
            <th align="center" width="10"><b>JENIS</b></th>
            <th align="center" width="15"><b>DIBAYARKAN</b></th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $k)
            <tr>
                <td>{{ $k->pegawai->nip }}</td>
                <td>{{ strtoupper($k->pegawai->nama) }}</td>
                @if($k->mutasi && $k->mutasi->golru && $k->mutasi->golru->golongan)
                <td align="center">{{ $k->mutasi->golru->golongan->nama }}</td>
                @else
                <td align="center">{{ $k->pegawai->golongan ? $k->pegawai->golongan->nama : '-' }}</td>
                @endif
                @if($k->remun_gaji)
                    <td>{{ $k->remun_gaji->unit->nama }}</td>
                    <td>{{ $k->remun_gaji->layer->nama }}</td>
                    <td>{{ $k->remun_gaji->jabatan_dasar->grade }}</td>
                    <td>{{ $k->remun_gaji->jabatan->nama }}</td>
                    <td>{{ $k->remun_gaji->jabatan->sub }}</td>
                @else
                    @if($k->mutasi)
                        <td align="center">{{ $k->mutasi->detail()->where('status','=',1)->first()->unit ? $k->mutasi->detail()->where('status','=',1)->first()->unit->nama : '-' }}</td>
                        <td align="center">{{ $k->mutasi->detail()->where('status','=',1)->first()->layer ? $k->mutasi->detail()->where('status','=',1)->first()->layer->nama : '-' }}</td>
                        <td align="center">{{ $k->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar ? $k->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar->grade : '-' }}</td>
                        <td>{{ $k->mutasi->detail()->where('status','=',1)->first()->jabatan ? $k->mutasi->detail()->where('status','=',1)->first()->jabatan->nama : '-' }}</td>
                        <td>{{ $k->mutasi->detail()->where('status','=',1)->first()->jabatan ? $k->mutasi->detail()->where('status','=',1)->first()->jabatan->sub : '-' }}</td>
                    @else
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    @endif
                @endif
                <td>{{ $k->status_kepegawaian ? $k->status_kepegawaian->nama : '-' }}</td>
                <td>{{ $k->kategori == 1 ? 'Dosen' : 'Tendik' }}</td>
                <td>{{ $k->total_selisih }}</td>
            </tr>
        @endforeach
    </tbody>
</table>