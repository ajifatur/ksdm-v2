<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="25"><b>NIP</b></th>
            <th align="center" width="40"><b>NAMA</b></th>
            <th align="center" width="25"><b>UNIT</b></th>
            <th align="center" width="10"><b>GOL</b></th>
            <th align="center" width="15"><b>GJPOKOK</b></th>
            <th align="center" width="15"><b>TJISTRI</b></th>
            <th align="center" width="15"><b>TJANAK</b></th>
            <th align="center" width="15"><b>TJBERAS</b></th>
            <th align="center" width="15"><b>TJUMUM</b></th>
            <th align="center" width="15"><b>TJFUNGS</b></th>
            <th align="center" width="15"><b>BPJSKES1</b></th>
            <th align="center" width="15"><b>BPJSKET3</b></th>
            <th align="center" width="15"><b>NOMINAL</b></th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $d)
            <tr>
                <td>{{ $d->pegawai->npu != null ? $d->pegawai->npu : $d->pegawai->nip }}</td>
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
                <td>{{ $d->gjpokok }}</td>
                <td>{{ $d->tjistri }}</td>
                <td>{{ $d->tjanak }}</td>
                <td>{{ $d->tjberas }}</td>
                <td>{{ $d->tjumum }}</td>
                <td>{{ $d->tjfungs }}</td>
                <td>{{ $d->bpjskes1 }}</td>
                <td>{{ $d->bpjsket3 }}</td>
                <td>{{ $d->nominal }}</td>
            </tr>
        @endforeach
    </tbody>
</table>