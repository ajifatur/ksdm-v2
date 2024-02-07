@extends('faturhelper::layouts/admin/main')

@section('title', 'List Pegawai Aktif')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">List Pegawai Aktif</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2" width="120">NIP</th>
                                <th rowspan="2" width="200">Nama</th>
                                <th rowspan="2" width="80">Gelar Depan</th>
                                <th rowspan="2" width="80">Gelar Belakang</th>
                                <th rowspan="2" width="200">Tempat Lahir</th>
                                <th rowspan="2" width="200">Tanggal Lahir</th>
                                <th rowspan="2" width="80">Pangkat/Golru</th>
                                <th rowspan="2" width="80">MKG</th>
                                <th colspan="2">Jabatan Fungsional</th>
                                <th colspan="2">Jabatan Struktural</th>
                                <th rowspan="2" width="80">Kategori</th>
                                <th rowspan="2" width="80">Status Kepegawaian</th>
                                <th rowspan="2" width="20" class="notexport">Opsi</th>
                            </tr>
                            <tr>
                                <th width="200">Jabatan</th>
                                <th width="200">Unit</th>
                                <th width="200">Jabatan</th>
                                <th width="200">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $p)
                            <tr>
                                <td><a href="{{ route('admin.pegawai.detail', ['id' => $p->id]) }}">'{{ $p->npu != null ? $p->npu : $p->nip }}</a></td>
                                <td>{{ $p->nama }}</td>
                                <td>{{ $p->gelar_depan }}</td>
                                <td>{{ $p->gelar_belakang }}</td>
                                <td>{{ $p->tempat_lahir }}</td>
                                <td>{{ \Ajifatur\Helpers\DateTimeExt::full($p->tanggal_lahir) }}</td>
                                <td>{{ $p->golru ? $p->golru->indonesia.', '.$p->golru->nama : '-' }}</td>
                                <td>'{{ $p->masa_kerja ? $p->masa_kerja->nama : '-' }}</td>
                                <td>{{ $p->jabfung ? $p->jabfung->nama : '' }}</td>
                                <td>{{ $p->unit ? $p->unit->nama : '' }}</td>
                                <td>{{ $p->jabstruk ? $p->jabstruk->nama : '' }}</td>
                                <td>{{ $p->unit_jabstruk ? $p->unit_jabstruk->unit->nama : '' }}</td>
                                <td>{{ $p->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td>{{ $p->status_kepegawaian->nama }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.pegawai.edit', ['id' => $p->id]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit Pegawai"><i class="bi-pencil"></i></a>
                                        <a href="{{ route('admin.pegawai.detail', ['id' => $p->id, 'mutasi' => true]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="List Mutasi"><i class="bi-list"></i></a>
                                        <a href="{{ route('admin.mutasi.create', ['id' => $p->id]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Tambah Mutasi"><i class="bi-plus"></i></a>
                                    </div>
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
        fixedHeader: true,
        buttons: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    #datatable tr.text-danger td {text-decoration: line-through;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection