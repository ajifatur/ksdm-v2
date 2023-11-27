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
            <tr>
                <td>{{ $d->pegawai->nip }}</td>
                <td>{{ strtoupper($d->pegawai->nama) }}</td>
                <td>{{ $d->unit ? $d->unit->nama : '-' }}</td>
                @if($d->golru)
                    <td align="center">{{ $d->golru->golongan->nama }}</td>
                @else
                    @if($d->pegawai->golongan)
                        <td align="center">{{ $d->pegawai->golongan->nama }}</td>
                    @else
                        <td align="center">-</td>
                    @endif
                @endif
                <td>{{ $d->unit ? $d->unit->layer_id : '-' }}</td>
                <td>{{ $d->jabatan_dasar ? $d->jabatan_dasar->grade : '-' }}</td>
                <td>{{ $d->status_kepegawaian ? $d->status_kepegawaian->nama : '-' }}</td>
                <td>{{ $d->kategori == 1 ? 'Dosen' : 'Tendik' }}</td>
                <td>{{ $d->jabatan ? $d->jabatan->nama : '-' }}</td>
                <td>{{ $d->jabatan ? $d->jabatan->sub : '-' }}</td>
                <td>{{ $d->remun_insentif }}</td>
                <td>{{ $d->remun_insentif }}</td>
            </tr>
        @endforeach
    </tbody>
</table>