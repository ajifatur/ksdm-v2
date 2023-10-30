@extends('faturhelper::layouts/admin/main')

@section('title', 'Cek Mutasi PNS / CPNS')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Cek Mutasi PNS / CPNS</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="alert alert-warning fade show" role="alert">
                    <div class="alert-message">
                        <div class="fw-bold"><i class="bi-info-circle-fill me-1"></i> Info</div>
                        Yang berwarna hijau adalah jabatan tertinggi.
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama / NIP</th>
                                <th>Jenis</th>
                                <th>Periode</th>
                                <th>Status Kepegawaian</th>
                                <th>Jabatan</th>
                                <th>Unit</th>
                                <th>Golru</th>
                                <th>Masa Kerja</th>
                                <th>TMT</th>
                                <th width="20">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mutasi as $m)
                            <tr>
                                <td>{{ strtoupper($m->pegawai->nama) }}<br>{{ $m->pegawai->nip }}</td>
								<td>{{ $m->jenis->nama }}</td>
                                @if($m->bulan != 0 && $m->tahun != 0)
								    <td>{{ $m->tahun }} {{ \Ajifatur\Helpers\DateTimeExt::month($m->bulan) }}</td>
                                @else
                                    <td>-</td>
                                @endif
                                <td>{{ $m->status_kepegawaian->nama }}</td>
                                @if($m->jenis_id == 1)
                                    <td>
                                        @foreach($m->detail as $key2=>$d)
                                            <span class="{{ count($m->detail) > 1 && $d->status == 1 ? 'text-success' : '' }}">{{ $d->jabatan ? $d->jabatan->nama : '-' }}</span>
                                            @if($key2 != count($m->detail)-1)<hr class="my-0">@endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($m->detail as $key2=>$d)
                                            <span class="{{ count($m->detail) > 1 && $d->status == 1 ? 'text-success' : '' }}">{{ $d->unit ? $d->unit->nama : '-' }}</span>
                                            @if($key2 != count($m->detail)-1)<hr class="my-0">@endif
                                        @endforeach
                                    </td>
                                @else
                                    <td>-</td>
                                    <td>-</td>
                                @endif
								<td>{{ $m->golru ? $m->golru->nama : '-' }}</td>
								<td>{{ $m->gaji_pokok ? $m->gaji_pokok->nama : '-' }}</td>
                                <td>
                                    <span class="d-none">{{ $m->tmt }}</span>
                                    {{ $m->tmt != null ? date('d/m/Y', strtotime($m->tmt)) : '-' }}
                                </td>
								<td align="center">
									<div class="btn-group">
										<a href="{{ route('admin.mutasi.edit', ['id' => $m->pegawai_id, 'mutasi_id' => $m->id]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="bi-pencil"></i></a>
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
        fixedHeader: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
