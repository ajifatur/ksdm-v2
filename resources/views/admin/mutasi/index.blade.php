@extends('faturhelper::layouts/admin/main')

@section('title', $jenis == 'remun' ? 'Mutasi Remun Terproses' : 'Mutasi Tunjangan Profesi Dosen Terproses')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">{{ $jenis == 'remun' ? 'Mutasi Remun Terproses' : 'Mutasi Tunjangan Profesi Dosen Terproses' }}</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="0" disabled>--Pilih Bulan--</option>
                            @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($m) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="0" disabled>--Pilih Tahun--</option>
                            @for($y=date('Y'); $y>=2023; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                        <button type="submit" class="btn btn-sm btn-info"><i class="bi-filter me-1"></i> Filter</button>
                    </div>
                </div>
            </form>
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
                                <th rowspan="{{ $jenis == 'remun' ? 2 : 1 }}">Nama / NIP</th>
                                <th rowspan="{{ $jenis == 'remun' ? 2 : 1 }}">{{ $jenis == 'remun' ? 'Jenis / Deskripsi' : 'Jenis' }}</th>
                                <th rowspan="{{ $jenis == 'remun' ? 2 : 1 }}">Status Kepegawaian</th>
                                <th rowspan="{{ $jenis == 'remun' ? 2 : 1 }}">Golru</th>
                                <th rowspan="{{ $jenis == 'remun' ? 2 : 1 }}">MKG</th>
                                <th rowspan="{{ $jenis == 'remun' ? 2 : 1 }}">Jabatan</th>
                                <th rowspan="{{ $jenis == 'remun' ? 2 : 1 }}">Unit</th>
                                <th rowspan="{{ $jenis == 'remun' ? 2 : 1 }}">TMT</th>
                                @if($jenis == 'remun')
                                <th colspan="3">Remun</th>
                                @endif
								<th rowspan="{{ $jenis == 'remun' ? 2 : 1 }}" width="30">Opsi</th>
                            </tr>
                            @if($jenis == 'remun')
                            <tr>
                                <th width="70">Penerimaan</th>
                                <th width="70">Gaji</th>
                                <th width="70">Insentif</th>
                            </tr>
                            @endif
                        </thead>
                        <tbody>
                            @foreach($mutasi as $m)
                            <tr class="{{ $jenis == 'remun' && $m->remun_gaji == 0 ? 'bg-secondary text-white' : '' }}">
                                <td>{{ strtoupper($m->pegawai->nama) }}<br>{{ $m->pegawai->nip }}</td>
                                <td>
                                    {{ $m->jenis->nama }}
                                    @if($jenis == 'remun')
                                        <br>
                                        {{ $m->uraian != '' ? '('.$m->uraian.')' : '' }}
                                    @endif
                                </td>
                                <td>{{ $m->status_kepegawaian ? $m->status_kepegawaian->nama : '-' }}</td>
                                <td>{{ $m->golru ? $m->golru->nama : '-' }}</td>
                                <td>{{ $m->gaji_pokok ? $m->gaji_pokok->nama : '-' }}</td>
                                <td>
                                    @if($jenis == 'remun')
                                        @if(count($m->detail) > 0)
                                            @foreach($m->detail as $key2=>$d)
                                                {{ $d->jabatan ? $d->jabatan->nama : '-' }}
                                                @if($key2 != count($m->detail)-1)<hr class="my-0">@endif
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    @else
                                        {{ $m->pegawai->jabfung->nama }}
                                    @endif
                                </td>
                                <td>
                                    @if($jenis == 'remun')
                                        @if(count($m->detail) > 0)
                                            @foreach($m->detail as $key2=>$d)
                                                {{ $d->unit ? $d->unit->nama : '-' }}
                                                @if($key2 != count($m->detail)-1)<hr class="my-0">@endif
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    @else
                                        {{ $m->pegawai->unit->nama }}
                                    @endif
                                </td>
                                <td>
                                    <span class="d-none">{{ $m->tmt }}</span>
                                    {{ $m->tmt != null ? date('d/m/Y', strtotime($m->tmt)) : '-' }}
                                </td>
                                @if($jenis == 'remun')
                                <td align="right">{{ number_format($m->remun_penerimaan) }}</td>
                                <td align="right">{{ number_format($m->remun_gaji) }}</td>
                                <td align="right">{{ number_format($m->remun_insentif) }}</td>
                                @endif
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
