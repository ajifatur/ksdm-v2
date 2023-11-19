@extends('faturhelper::layouts/admin/main')

@section('title', 'Pantauan Pensiun PNS')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Pantauan Pensiun PNS</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama / NIP</th>
                                <th width="80">Jenis</th>
                                <th>Jabatan</th>
                                <th>Unit</th>
                                <th width="100">TMT Pensiun</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $p)
                            <tr>
                                <td>{{ strtoupper($p->nama) }}<br>{{ $p->nip }}</td>
                                <td>{{ $p->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td>{{ $p->jabfung->nama }}</td>
                                <td>{{ $p->unit->nama }}</td>
                                <td>
                                    <span class="d-none">{{ $p->tmt_pensiun }}</span>
                                    {{ $p->tmt_pensiun != null ? date('d/m/Y', strtotime($p->tmt_pensiun)) : '-' }}
                                </td>
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
        fixedHeader: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection