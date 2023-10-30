@extends('faturhelper::layouts/admin/main')

@section('title', 'Blacklist')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Blacklist</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Unit</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($blacklist as $b)
                            <tr>
                                <td><a href="{{ route('admin.pegawai.detail', ['id' => $b->pegawai->id]) }}">'{{ $b->pegawai->nip }}</a></td>
                                <td>{{ title_name($b->pegawai->nama, $b->pegawai->gelar_depan, $b->pegawai->gelar_belakang) }}</td>
                                <td>{{ $b->pegawai->unit ? $b->pegawai->unit->nama : '-' }}</td>
                                <td>{{ $b->keterangan }}</td>
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
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
