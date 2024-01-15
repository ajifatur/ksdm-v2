@extends('faturhelper::layouts/admin/main')

@section('title', 'Peralihan BLU ke PTNBH')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Peralihan BLU ke PTNBH</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Golru</th>
                                <th>Gaji Pokok</th>
                                <th>Masa Kerja</th>
                                @for($i=12;$i>=1;$i--)
                                <th>MK B-{{ $i }}</th>
                                <th>GP B-{{ $i }}</th>
                                <th>TI B-{{ $i }}</th>
                                <th>TA B-{{ $i }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mutasi as $m)
                            <tr>
								<td>'{{ $m->pegawai->nip }}</td>
								<td>{{ $m->pegawai->nama }}</td>
                                <td>{{ $m->pegawai->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td>{{ $m->golru->nama }}</td>
                                <td>{{ number_format($m->gaji_pokok->gaji_pokok) }}</td>
                                <td>'{{ $m->gaji_pokok->nama }}</td>
                                @foreach($m->gaji as $g)
                                <td>'{{ \App\Models\GajiPokok::where('gaji_pokok','=',$g->gjpokok)->first()->nama }}</td>
                                <td>{{ $g->gjpokok }}</td>
                                <td>{{ $g->tjistri }}</td>
                                <td>{{ $g->tjanak }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
		</div>
	</div>
</div>

@endsection

@section('js')

<script type="text/javascript">
    // DataTable
    Spandiv.DataTable("#datatable", {
        orderAll: true,
        fixedHeader: true,
        buttons: true,
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
