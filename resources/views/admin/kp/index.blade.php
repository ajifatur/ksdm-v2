@extends('faturhelper::layouts/admin/main')

@section('title', 'List Mutasi Pangkat')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">List Mutasi Pangkat</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-header d-sm-flex justify-content-end align-items-center">
                <select name="tmt" class="form-select form-select-sm">
                    <option value="" disabled>--Pilih TMT--</option>
                    @foreach($tmt as $t)
					<option value="{{ $t }}" {{ Request::query('tmt') == $t ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::full($t) }}</option>
                    @endforeach
                </select>
            </div>
            <hr class="my-0">
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
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Status Kepeg.</th>
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
                                <td><a href="{{ route('admin.pegawai.detail', ['id' => $m->pegawai->id]) }}">`{{ $m->pegawai->npu != null ? $m->pegawai->npu : $m->pegawai->nip }}</a></td>
                                <td>{{ strtoupper($m->pegawai->nama) }}</td>
                                <td>{{ $m->pegawai->status_kepegawaian->nama }}</td>
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
        fixedHeader: true,
        buttons: true
    });
	
    // Button Delete
    Spandiv.ButtonDelete(".btn-delete", ".form-delete");
	
    // Select2
    Spandiv.Select2("select[name=tmt]");
    
    // Change the select
    $(document).on("change", ".card-header select", function() {
		var tmt = $("select[name=tmt]").val();
        window.location.href = Spandiv.URL("{{ route('admin.kp.index') }}", {tmt: tmt});
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
