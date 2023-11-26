<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="20"><b>nip</b></th>
            <th align="center" width="40"><b>nama</b></th>
            @if($data['kategori'] == '')
            <th align="center" width="15"><b>jenis</b></th>
            <th align="center" width="20"><b>anak satker</b></th>
            <th align="center" width="20"><b>unit</b></th>
            @endif
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
                @if($data['kategori'] == '')
                <td>{{ $g->pegawai->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                <?php $anak_satker = \App\Models\AnakSatker::where('kode','=',$g->kdanak)->first(); ?>
                <td>{{ $anak_satker ? $anak_satker->kode.' - '.$anak_satker->nama : '-' }}</td>
                <td>{{ $g->unit ? $g->unit->nama : '-' }}</td>
                @endif
                @foreach($data['kategori_gaji'] as $j)
                <td>{{ $g->{$j} }}</td>
                @endforeach
            </tr>
        @endforeach
		<tr>
			<td></td>
			<td></td>
            @if($data['kategori'] == '')
			<td></td>
			<td></td>
			<td></td>
            @endif
            @foreach($data['kategori_gaji'] as $j)
			<td style="font-weight: bold;">{{ $data['gaji']->sum($j) }}</td>
            @endforeach
		</tr>
    </tbody>
</table>