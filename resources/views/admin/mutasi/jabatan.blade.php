@extends('faturhelper::layouts/admin/main')

@section('title', 'Mutasi Jabatan')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Mutasi Jabatan</h1>
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
                                <th>Status Kepegawaian</th>
                                <th>Golru</th>
                                <th>MKG</th>
                                <th>Jabatan</th>
                                <th>Sub</th>
                                <th>Unit</th>
                                <th>TMT</th>
								<th width="30">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mutasi as $m)
                            <tr class="{{ $m->remun_gaji == 0 ? 'bg-secondary text-white' : '' }}">
                                <td>`{{ $m->pegawai->nip }}</td>
                                <td>{{ strtoupper($m->pegawai->nama) }}<br></td>
                                <td>{{ $m->status_kepegawaian ? $m->status_kepegawaian->nama : '-' }}</td>
                                <td>{{ $m->golru ? $m->golru->nama : '-' }}</td>
                                <td>{{ $m->gaji_pokok ? $m->gaji_pokok->nama : '-' }}</td>
                                @if($m->jenis_id == 1)
                                    <td>
                                        @foreach($m->detail as $key2=>$d)
                                            @if($d->jabatan && $d->jabatan->jenis_id == 1)
                                                {{ $d->jabatan ? $d->jabatan->nama : '-' }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($m->detail as $key2=>$d)
                                            @if($d->jabatan && $d->jabatan->jenis_id == 1)
                                                {{ $d->jabatan ? $d->jabatan->sub : '-' }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($m->detail as $key2=>$d)
                                            @if($d->jabatan && $d->jabatan->jenis_id == 1)
                                                {{ $d->unit ? $d->unit->nama : '-' }}
                                            @endif
                                        @endforeach
                                    </td>
                                @else
                                    <td>-</td>
                                    <td>-</td>
                                @endif
                                <td>
                                    <span class="d-none">{{ $m->tmt }}</span>
                                    {{ $m->tmt != null ? date('d/m/Y', strtotime($m->tmt)) : '-' }}
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
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
