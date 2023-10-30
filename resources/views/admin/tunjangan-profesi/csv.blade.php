<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="25"><b>NO</b></th>
            <th align="center" width="40"><b>NAMA_SUPPLIER</b></th>
            <th align="center" width="15"><b>NAMA_PEMILIK_REKENING</b></th>
            <th align="center" width="15"><b>NO_REKENING</b></th>
            <th align="center" width="15"><b>JUMLAH_UANG</b></th>
            <th align="center" width="15"><b>NIP</b></th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $key=>$d)
            <tr>
                <td>{{ ($key+1) }}</td>
                <td>{{ $d->nama }}</td>
                <td>{{ $d->nama_rekening }}</td>
                <td>{{ $d->nomor_rekening }}</td>
                <td>{{ $d->diterimakan }}</td>
                <td>{{ $d->nip }}</td>
            </tr>
        @endforeach
    </tbody>
</table>