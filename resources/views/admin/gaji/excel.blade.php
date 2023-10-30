<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="20"><b>nip</b></th>
            <th align="center" width="40"><b>nama</b></th>
            @foreach($data['kategori_gaji'] as $j)
            <th align="center" width="10"><b>{{ $j }}</b></th>
            @endforeach
        </tr>
    </thead>
    <tbody>
		<?php
			$gjpokok = 0; $tjistri = 0; $tjanak = 0; $tjupns = 0; $tjstruk = 0; $tjfungs = 0; $tjdaerah = 0; $tjpencil = 0; $tjlain = 0; $tjkompen = 0; $pembul = 0; $tjberas = 0; $tjpph = 0; $potpfkbul = 0; $potpfk2 = 0; $potpfk10 = 0; $potpph = 0; $potswrum = 0; $potkelbtj = 0; $potlain = 0; $pottabrum = 0; $bpjs = 0; $bpjs2 = 0;
		?>
        @foreach($data['gaji'] as $g)
            <tr>
                <td>{{ $g->nip }}</td>
                <td>{{ $g->nama }}</td>
                @foreach($data['kategori_gaji'] as $j)
                <td>{{ $g->{$j} }}</td>
                @endforeach
            </tr>
        @endforeach
		<tr>
			<td></td>
			<td></td>
            @foreach($data['kategori_gaji'] as $j)
			<td style="font-weight: bold;">{{ $data['gaji']->sum($j) }}</td>
            @endforeach
		</tr>
    </tbody>
</table>