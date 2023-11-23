@extends('faturhelper::layouts/admin/main')

@section('title', 'List Mutasi Serdos')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">List Mutasi Serdos</h1>
	<a href="{{ route('admin.tunjangan-profesi.mutasi.create') }}" class="btn btn-sm btn-primary"><i class="bi-plus me-1"></i> Tambah Mutasi</a>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                @if(Session::get('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="alert-message">{{ Session::get('message') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama / NIP</th>
                                <th>Unit</th>
                                <th>Angkatan</th>
                                <th>Jenis</th>
                                <th width="80">Status Serdos</th>
                                <th width="30">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mutasi_serdos as $m)
                            <tr>
                                <td>{{ strtoupper($m->pegawai->nama) }}<br>{{ $m->pegawai->nip }}</td>
                                <td>{{ $m->unit->nama }}</td>
                                <td>{{ $m->angkatan->nama }}</td>
                                <td>{{ $m->jenis->nama }}</td>
                                <td>{{ $m->jenis->status == 1 ? 'Aktif' : 'Nonaktif' }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.tunjangan-profesi.mutasi.edit', ['id' => $m->id]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="{{ $m->id }}" data-bs-toggle="tooltip" title="Hapus"><i class="bi-trash"></i></a>
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

<form class="form-delete d-none" method="post" action="{{ route('admin.tunjangan-profesi.mutasi.delete') }}">
    @csrf
    <input type="hidden" name="id">
</form>

@endsection

@section('js')

<script type="text/javascript">
    // DataTable
    Spandiv.DataTable("#datatable", {
        pageLength: -1,
        orderAll: true
    });
	
	// Button Delete
	Spandiv.ButtonDelete(".btn-delete", ".form-delete");
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
