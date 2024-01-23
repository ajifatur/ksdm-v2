@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring SPKGB PNS')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring SPKGB PNS</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="0" disabled>--Pilih Tahun--</option>
                            @for($y=date('Y')+1; $y>=2023; $y--)
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
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">Bulan</th>
                                <th colspan="3">Jumlah Diproses</th>
                                <th colspan="3">Opsi</th>
                            </tr>
                            <tr>
                                <th width="50">Dosen</th>
                                <th width="50">Tendik</th>
                                <th width="50">Semua</th>
                                <th width="50">Lihat</th>
                                <th width="50">Batch PDF</th>
                                <th width="50">Excel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                            <tr>
                                <td><span class="d-none">{{ $d['bulan'] < 10 ? '0'.$d['bulan'] : $d['bulan'] }}</span>{{ $d['nama'] }}</td>
                                <td align="right">{{ number_format($d['spkgb_dosen']) }}</td>
                                <td align="right">{{ number_format($d['spkgb_tendik']) }}</td>
                                <td align="right">{{ number_format($d['spkgb_semua']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.spkgb.pns.index', ['bulan' => $d['bulan'], 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="List SPKGB"><i class="bi-eye"></i></a>
                                        <a href="{{ route('admin.spkgb.print.recap', ['bulan' => $d['bulan'], 'tahun' => $tahun, 'type' => 1]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Cetak Rekap PDF" target="_blank"><i class="bi-file-pdf"></i></a>
                                    </div>
                                </td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.spkgb.print.batch', ['bulan' => $d['bulan'], 'tahun' => $tahun, 'type' => 1, 'jenis' => 1]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Cetak Batch PDF Dosen" target="_blank"><i class="bi-file-pdf"></i></a>
                                        <a href="{{ route('admin.spkgb.print.batch', ['bulan' => $d['bulan'], 'tahun' => $tahun, 'type' => 1, 'jenis' => 2]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Cetak Batch PDF Tendik" target="_blank"><i class="bi-file-pdf"></i></a>
                                        <a href="{{ route('admin.spkgb.print.batch', ['bulan' => $d['bulan'], 'tahun' => $tahun, 'type' => 1]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Cetak Batch PDF Semua" target="_blank"><i class="bi-file-pdf"></i></a>
                                    </div>
                                </td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.spkgb.pns.export', ['bulan' => $d['bulan'], 'tahun' => $tahun, 'jenis' => 1]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Cetak Excel Template Siradi Dosen" target="_blank"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.spkgb.pns.export', ['bulan' => $d['bulan'], 'tahun' => $tahun, 'jenis' => 2]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Cetak Excel Template Siradi Tendik" target="_blank"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.spkgb.pns.export', ['bulan' => $d['bulan'], 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Cetak Excel Template Siradi Semua" target="_blank"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total['dosen']) }}</td>
                                <td align="right">{{ number_format($total['tendik']) }}</td>
                                <td align="right">{{ number_format($total['semua']) }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
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
        pageLength: -1,
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection