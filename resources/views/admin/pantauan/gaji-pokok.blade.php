@extends('faturhelper::layouts/admin/main')

@section('title', 'Pantauan Gaji Pokok PNS')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Pantauan Gaji Pokok PNS</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="alert alert-warning fade show" role="alert">
                    <div class="alert-message">
                        <div class="fw-bold"><i class="bi-info-circle-fill me-1"></i> Info</div>
                        Gaji Pokok GPP dihitung sampai Gaji Induk <strong>{{ \Ajifatur\Helpers\DateTimeExt::month((int)$gaji_terakhir->bulan) }} {{ $gaji_terakhir->tahun }}</strong>.
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">NIP</th>
                                <th rowspan="2">Nama</th>
                                <th rowspan="2" width="80">Jenis</th>
                                <th rowspan="2">Unit</th>
                                <th colspan="2">Gaji Pokok Mutasi</th>
                                <th colspan="2">Gaji Pokok GPP</th>
                                <th rowspan="2" width="80">Cek</th>
                                <th rowspan="2" width="80">SPKGB</th>
                            </tr>
							<tr>
								<th width="60">MKG</th>
								<th width="80">Nominal</th>
								<th width="60">MKG</th>
								<th width="80">Nominal</th>
							</tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $p)
                            <tr>
                                <td>`{{ $p->nip }}</td>
                                <td>{{ strtoupper($p->nama) }}</td>
                                <td>{{ $p->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td>{{ $p->unit->nama }}</td>
                                <td>{{ $p->mutasi_gaji_pokok_terakhir ? "'".$p->mutasi_gaji_pokok_terakhir->nama : '-' }}</td>
                                <td align="right">{{ $p->mutasi_gaji_pokok_terakhir ? number_format($p->mutasi_gaji_pokok_terakhir->gaji_pokok) : '-' }}</td>
                                <td>{{ $p->gpp_gaji_pokok_terakhir ? "'".$p->gpp_gaji_pokok_terakhir->nama : '-' }}</td>
                                <td align="right">{{ $p->gpp_gaji_pokok_terakhir ? number_format($p->gpp_gaji_pokok_terakhir->gaji_pokok) : '-' }}</td>
								<td><span class="{{ $p->cek == 'Sama' ? 'text-success' : 'text-danger' }}">{{ $p->cek }}</span></td>
                                <td>
                                    <span class="d-none">{{ $p->spkgb_terakhir ? $p->spkgb_terakhir->mutasi->tmt : '-' }}</span>
                                    {{ $p->spkgb_terakhir ? date('d/m/Y', strtotime($p->spkgb_terakhir->mutasi->tmt)) : '-' }}
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
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection