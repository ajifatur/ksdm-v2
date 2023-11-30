<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="20"><b>nip</b></th>
            <th align="center" width="40"><b>nama</b></th>
            <th align="center" width="15"><b>jumlah_hari</b></th>
            <th align="center" width="10"><b>tarif</b></th>
            <th align="center" width="10"><b>pph</b></th>
            <th align="center" width="10"><b>kotor</b></th>
            <th align="center" width="10"><b>potongan</b></th>
            <th align="center" width="10"><b>bersih</b></th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['uang_makan'] as $um)
            <tr>
                <td>{{ $um->nip }}</td>
                <td>{{ $um->nama }}</td>
                <td>{{ $um->jmlhari }}</td>
                <td>{{ $um->tarif }}</td>
                <td>{{ $um->pph }}</td>
                <td>{{ $um->kotor }}</td>
                <td>{{ $um->potongan }}</td>
                <td>{{ $um->bersih }}</td>
            </tr>
        @endforeach
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td style="font-weight: bold;">{{ $data['uang_makan']->sum('kotor') }}</td>
			<td style="font-weight: bold;">{{ $data['uang_makan']->sum('potongan') }}</td>
			<td style="font-weight: bold;">{{ $data['uang_makan']->sum('bersih') }}</td>
		</tr>
    </tbody>
</table>