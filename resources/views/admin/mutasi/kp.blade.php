@extends('faturhelper::layouts/admin/main')

@section('title', 'Mutasi Pangkat')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Mutasi Pangkat</h1>
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
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama / NIP</th>
                                <th>Golru</th>
                                <th>MKG</th>
                                <th>MK Tahun</th>
                                <th>MK Bulan</th>
                                <th>TMT</th>
								<th width="30">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mutasi as $m)
                            <tr>
                                <td>{{ strtoupper($m->pegawai->nama) }}<br>{{ $m->pegawai->nip }}</td>
                                <td>{{ $m->golru ? $m->golru->nama : '-' }}</td>
                                <td>{{ $m->gaji_pokok ? $m->gaji_pokok->nama : '-' }}</td>
                                <td>{{ $m->perubahan ? $m->perubahan->mk_tahun : '' }}</td>
                                <td>{{ $m->perubahan ? $m->perubahan->mk_bulan : '' }}</td>
                                <td>
                                    <span class="d-none">{{ $m->tmt }}</span>
                                    {{ date('d/m/Y', strtotime($m->tmt)) }}
                                </td>
								<td align="center">
									<div class="btn-group">
										<a href="{{ route('admin.mutasi.edit', ['id' => $m->pegawai_id, 'mutasi_id' => $m->id]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="bi-pencil"></i></a>
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

<form class="form-delete d-none" method="post" action="{{ route('admin.mutasi.delete') }}">
    @csrf
    <input type="hidden" name="id">
    <input type="hidden" name="redirect" value="{{ Request::url() }}">
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
