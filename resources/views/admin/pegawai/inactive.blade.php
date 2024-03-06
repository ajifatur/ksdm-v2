@extends('faturhelper::layouts/admin/main')

@section('title', 'List Pegawai Nonaktif')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">List Pegawai Nonaktif</h1>
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
                                <th width="80">Gelar Depan</th>
                                <th width="80">Gelar Belakang</th>
                                <th width="80">Golru</th>
                                <th width="80">Kategori</th>
                                <th width="80">Status Kepegawaian</th>
                                <th width="80">Status Kerja</th>
                                <th width="80">TMT Nonaktif</th>
                                <th width="20">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $p)
                            <tr>
                                <td><a class="{{ $p->status_kerja->status == 0 ? 'text-danger' : '' }}" href="{{ route('admin.pegawai.detail', ['id' => $p->id]) }}">'{{ $p->npu != null ? $p->npu : $p->nip }}</a></td>
                                <td>{{ $p->nama }}</td>
                                <td>{{ $p->gelar_depan }}</td>
                                <td>{{ $p->gelar_belakang }}</td>
                                <td>{{ $p->golru ? $p->golru->nama : '-' }}</td>
                                <td>{{ $p->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td>{{ $p->status_kepegawaian->nama }}</td>
                                <td>{{ $p->status_kerja->status == 0 ? $p->status_kerja->nama : 'Aktif' }}</td>
                                <td>
                                    <span class="d-none">{{ $p->tmt_non_aktif }}</span>
                                    {{ $p->tmt_non_aktif != null ? date('d/m/Y', strtotime($p->tmt_non_aktif)) : '' }}
                                </td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.pegawai.detail', ['id' => $p->id, 'mutasi' => true]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="List Mutasi"><i class="bi-list"></i></a>
                                        <a href="{{ route('admin.mutasi.form', ['id' => $p->id]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Tambah Mutasi"><i class="bi-plus"></i></a>
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
        buttons: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection