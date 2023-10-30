@extends('faturhelper::layouts/admin/main')

@section('title', 'Pencarian Pegawai')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Pencarian Pegawai</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <form method="get" action="{{ route('admin.pegawai.search') }}">
                    <p>Masukkan kata kunci:</p>
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" value="{{ session()->get('keyword') }}" placeholder="NIP/Nama" required autofocus>
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi-search"></i></button>
                    </div>
                </form>
            </div>
		</div>
	</div>
    @if($pegawai != [])
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th width="80">Kategori</th>
                                <th width="80">Status Kepegawaian</th>
                                <th width="80">Status Kerja</th>
                                <th width="60">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $p)
                            <tr class="{{ $p->status_kerja->status == 0 ? 'text-danger' : '' }}">
                                <td><a class="{{ $p->status_kerja->status == 0 ? 'text-danger' : '' }}" href="{{ route('admin.pegawai.detail', ['id' => $p->id]) }}">{{ $p->nip }}</a></td>
                                <td>{{ title_name($p->nama, $p->gelar_depan, $p->gelar_belakang) }}</td>
                                <td>{{ $p->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td>{{ $p->status_kepegawaian->nama }}</td>
                                <td>{{ $p->status_kerja->status == 0 ? $p->status_kerja->nama : 'Aktif' }}</td>
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
    @endif
</div>

@endsection

@section('js')

<script type="text/javascript">
    // DataTable
    Spandiv.DataTable("#datatable", {
        orderAll: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    #datatable tr.text-danger td {text-decoration: line-through;}
</style>

@endsection