@extends('faturhelper::layouts/admin/main')

@section('title', 'Perubahan Tunjangan Profesi')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Perubahan Tunjangan Profesi</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
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
                @if(count($tunjangan_bulan_ini) > 0 && count($tunjangan_bulan_sebelumnya) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                            <thead class="bg-light">
                                <tr>
                                    <th>NIP</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>Unit</th>
                                    <th>Status<br>Kepegawaian</th>
                                    <th>Angkatan</th>
                                    <th class="notexport">TMT</th>
                                    <th class="d-none">TMT</th>
                                    <th>Keterangan</th>
                                    <th>Sebelum</th>
                                    <th>Sesudah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pegawai_on as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p->id]) }}">'{{ $p->nip }}</a></td>
                                        <td>{{ strtoupper($p->nama) }}</td>
                                        <td>{{ $p->jabfung ? $p->jabfung->nama : '-' }}</td>
                                        <td>{{ $p->unit ? $p->unit->nama : '-' }}</td>
                                        <td>{{ $p->status_kepegawaian->nama }}</td>
                                        <td>{{ $p->tunjangan_profesi()->first()->angkatan->nama }}</td>
                                        <td>
                                            <span class="d-none">{{ $p->mutasi_serdos ? $p->mutasi_serdos->tmt : '-' }}</span>
                                            {{ $p->mutasi_serdos ? date('d/m/Y', strtotime($p->mutasi_serdos->tmt)) : '-' }}
                                        </td>
                                        <td class="d-none">{{ $p->mutasi_serdos ? date('d/m/Y', strtotime($p->mutasi_serdos->tmt)) : '-' }}</td>
                                        <td>{{ $p->mutasi_serdos ? $p->mutasi_serdos->jenis->nama : '' }}</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>
                                @endforeach
                                @foreach($pegawai_off as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p->id]) }}">'{{ $p->nip }}</a></td>
                                        <td>{{ strtoupper($p->nama) }}</td>
                                        <td>{{ $p->jabfung ? $p->jabfung->nama : '-' }}</td>
                                        <td>{{ $p->unit ? $p->unit->nama : '-' }}</td>
                                        <td>{{ $p->status_kepegawaian->nama }}</td>
                                        <td>{{ $p->tunjangan_profesi()->first()->angkatan->nama }}</td>
                                        <td>
                                            <span class="d-none">{{ $p->mutasi_serdos ? $p->mutasi_serdos->tmt : $p->tmt_non_aktif }}</span>
                                            {{ $p->mutasi_serdos ? date('d/m/Y', strtotime($p->mutasi_serdos->tmt)) : date('d/m/Y', strtotime($p->tmt_non_aktif)) }}
                                        </td>
                                        <td class="d-none">{{ $p->mutasi_serdos ? date('d/m/Y', strtotime($p->mutasi_serdos->tmt)) : date('d/m/Y', strtotime($p->tmt_non_aktif)) }}</td>
                                        <td>{{ $p->mutasi_serdos ? $p->mutasi_serdos->jenis->nama : $p->status_kerja->nama }}</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>
                                @endforeach
                                @foreach($perubahan_tunjangan as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p['pegawai']->id]) }}">'{{ $p['pegawai']->nip }}</a></td>
                                        <td>{{ strtoupper($p['pegawai']->nama) }}</td>
                                        <td>{{ $p['pegawai']->jabfung ? $p['pegawai']->jabfung->nama : '-' }}</td>
                                        <td>{{ $p['pegawai']->unit ? $p['pegawai']->unit->nama : '-' }}</td>
                                        <td>{{ $p['pegawai']->status_kepegawaian->nama }}</td>
                                        <td>{{ $p['pegawai']->tunjangan_profesi()->first()->angkatan->nama }}</td>
                                        <td>
                                            <span class="d-none">{{ $p['mutasi_serdos'] ? $p['mutasi_serdos']->tmt : $p['pegawai']->tmt_non_aktif }}</span>
                                            {{ $p['mutasi_serdos'] ? date('d/m/Y', strtotime($p['mutasi_serdos']->tmt)) : date('d/m/Y', strtotime($p['pegawai']->tmt_non_aktif)) }}
                                        </td>
                                        <td class="d-none">{{ $p['mutasi_serdos'] ? date('d/m/Y', strtotime($p['mutasi_serdos']->tmt)) : date('d/m/Y', strtotime($p['pegawai']->tmt_non_aktif)) }}</td>
                                        <td>{{ $p['mutasi_serdos'] ? $p['mutasi_serdos']->jenis->nama : '' }}</td>
										<td>{{ number_format($p['sebelum'],0,',',',') }}</td>
										<td>{{ number_format($p['sesudah'],0,',',',') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-danger mb-0" role="alert">
                        <div class="alert-message">Belum ada data.</div>
                    </div>
                @endif
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
        buttons: true,
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
