<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="25"><b>nrp/npu</b></th>
            <th align="center" width="40"><b>nama</b></th>
            <th align="center" width="25"><b>unit</b></th>
            <th align="center" width="15"><b>status kawin</b></th>
            <th align="center" width="15"><b>status pajak</b></th>
            <th align="center" width="15"><b>gjpokok</b></th>
            <th align="center" width="15"><b>tjdosen</b></th>
            <th align="center" width="15"><b>tjlain</b></th>
            <th align="center" width="15"><b>tjbpjskes4</b></th>
            <th align="center" width="15"><b>tjbpjsket</b></th>
            <th align="center" width="15"><b>kotor</b></th>
            <th align="center" width="15"><b>iuranbpjskes1</b></th>
            <th align="center" width="15"><b>iuranbpjsket3</b></th>
            <th align="center" width="15"><b>jmlbpjskes</b></th>
            <th align="center" width="15"><b>jmlbpjsket</b></th>
            <th align="center" width="15"><b>bersih</b></th>
            <th align="center" width="25"><b>kategori</b></th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $d)
            <tr>
                <td>{{ $d->pegawai->npu != null ? $d->pegawai->npu : $d->pegawai->nip }}</td>
                <td>{{ $d->pegawai->nama }}</td>
                <td>{{ $d->unit->nama }}</td>
                <td>{{ $d->status_kawin }}</td>
                <td>{{ $d->status_pajak }}</td>
                <td>{{ $d->gjpokok }}</td>
                <td>{{ $d->tjdosen }}</td>
                <td>{{ $d->tjlain }}</td>
                <td>{{ $d->tjbpjskes4 }}</td>
                <td>{{ $d->tjbpjsket }}</td>
                <td>{{ $d->kotor }}</td>
                <td>{{ $d->iuranbpjskes1 }}</td>
                <td>{{ $d->iuranbpjsket3 }}</td>
                <td>{{ $d->jmlbpjskes }}</td>
                <td>{{ $d->jmlbpjsket }}</td>
                <td>{{ $d->bersih }}</td>
                <td>{{ $d->kategori->nama }}</td>
            </tr>
        @endforeach
    </tbody>
</table>