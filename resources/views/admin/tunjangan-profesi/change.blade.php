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
                                    <th>Status Pegawai</th>
                                    <th>Angkatan Serdos</th>
                                    <th class="notexport">TMT</th>
                                    <th class="d-none">TMT</th>
                                    <th>Keterangan</th>
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
                                            <span class="d-none">{{ $tanggal }}</span>
                                            {{ date('d/m/Y', strtotime($tanggal)) }}
                                        </td>
                                        <td class="d-none">{{ date('d/m/Y', strtotime($tanggal)) }}</td>
                                        <td>Pengaktifan Kembali</td>
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
                                            <span class="d-none">{{ $p->tmt_non_aktif }}</span>
                                            {{ $p->tmt_non_aktif != null ? date('d/m/Y', strtotime($p->tmt_non_aktif)) : '-' }}
                                        </td>
                                        <td class="d-none">{{ $p->tmt_non_aktif != null ? date('d/m/Y', strtotime($p->tmt_non_aktif)) : '-' }}</td>
                                        <td>{{ $p->status_kerja->nama }}</td>
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
