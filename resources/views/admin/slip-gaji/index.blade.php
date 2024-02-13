@extends('faturhelper::layouts/admin/main')

@section('title', 'Slip Gaji')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Slip Gaji</h1>
    <div class="btn-group">
        <a href="{{ route('admin.slip-gaji.create') }}" class="btn btn-sm btn-primary"><i class="bi-plus me-1"></i> Tambah</a>
    </div>
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
                                <th width="100">Tanggal</th>
                                <th width="150">Bulan, Tahun</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th width="30">Cetak</th>
                                <th width="30">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($slip_gaji as $s)
                            <tr>
                                <td>
                                    <span class="d-none">{{ $s->tanggal }}</span>
                                    {{ $s->tanggal != null ? date('d/m/Y', strtotime($s->tanggal)) : '' }}
                                </td>
                                <td>
                                    <span class="d-none">{{ $s->tahun }} {{ $s->bulan }}</span>
                                    {{ \Ajifatur\Helpers\DateTimeExt::month($s->bulan) }} {{ $s->tahun }}
                                </td>
                                <td>{{ $s->pegawai->nip }}</td>
                                <td>{{ strtoupper($s->pegawai->nama) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.slip-gaji.print', ['id' => $s->id, 'lang' => 'id']) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Cetak PDF" target="_blank">ID</a>
                                        <a href="{{ route('admin.slip-gaji.print', ['id' => $s->id, 'lang' => 'en']) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Cetak PDF" target="_blank">EN</a>
                                    </div>
                                </td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.slip-gaji.edit', ['id' => $s->id]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="{{ $s->id }}" data-bs-toggle="tooltip" title="Hapus"><i class="bi-trash"></i></a>
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

<form class="form-delete d-none" method="post" action="{{ route('admin.slip-gaji.delete') }}">
    @csrf
    <input type="hidden" name="id">
</form>

@endsection

@section('js')

<script type="text/javascript">
    // DataTable
    Spandiv.DataTable("#datatable", {
        orderAll: true,
        fixedHeader: true
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
