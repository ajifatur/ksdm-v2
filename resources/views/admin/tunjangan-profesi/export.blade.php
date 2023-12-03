<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="5"><b>NO</b></th>
            <th align="center" width="25"><b>NIP</b></th>
            <th align="center" width="60"><b>NAMA SUPPLIER</b></th>
            <th align="center" width="60"><b>NAMA PEMILIK REKENING</b></th>
            <th align="center" width="25"><b>NO REKENING</b></th>
            <th align="center" width="15"><b>TUNJANGAN</b></th>
            <th align="center" width="15"><b>PPH</b></th>
            <th align="center" width="15"><b>DITERIMAKAN</b></th>
            <th align="center" width="10"><b>BULAN</b></th>
            <th align="center" width="10"><b>TAHUN</b></th>
            <th align="center" width="20"><b>JENIS</b></th>
            <th align="center" width="20"><b>UNIT</b></th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $key=>$d)
            <tr>
                <td>{{ ($key+1) }}</td>
                <td>{{ $d->nip }}</td>
                <td>{{ $d->nama }}</td>
                <td>{{ $d->nama_rekening }}</td>
                <td>{{ $d->nomor_rekening }}</td>
                <td>{{ $d->tunjangan }}</td>
                <td>{{ $d->pph }}</td>
                <td>{{ $d->diterimakan }}</td>
                <td>{{ $d->bulan }}</td>
                <td>{{ $d->tahun }}</td>
                <td>{{ $d->angkatan->jenis->nama }}</td>
                <td>{{ $d->unit->nama }}</td>
            </tr>
        @endforeach
    </tbody>
</table>