<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="10"><b>Nama</b></th>
            <th align="center" width="10"><b>Golru</b></th>
            <th align="center" width="10"><b>MKG</b></th>
            <th align="center" width="15"><b>Gaji Pokok</b></th>

        </tr>
    </thead>
    <tbody>
        @foreach($data['gaji_pokok'] as $d)
            <tr>
                <td>{{ $d->nama }}</td>
                <td>{{ $d->golru->nama }}</td>
                <td>{{ $d->mkg }}</td>
                <td>{{ $d->gaji_pokok }}</td>
            </tr>
        @endforeach
    </tbody>
</table>