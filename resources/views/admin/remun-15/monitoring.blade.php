@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Remun ke-15')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Remun ke-15</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
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
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">Unit</th>
                                <th colspan="3">Pegawai</th>
                                <th colspan="2">Nominal</th>
                                <th rowspan="2" width="30">Excel Simkeu</th>
                            </tr>
                            <tr>
                                <th width="60">Dosen</th>
                                <th width="60">Tendik</th>
                                <th width="60">Dinolkan</th>
                                <th width="60">Dosen</th>
                                <th width="60">Tendik</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unit as $u)
                            <tr>
                                <td>{{ $u->nama }}</td>
                                <td align="right">{{ number_format($u->dosen_dibayarkan) }}</td>
                                <td align="right">{{ number_format($u->tendik_dibayarkan) }}</td>
                                <td align="right">{{ number_format($u->pegawai_dinolkan) }}</td>
                                <td align="right">{{ number_format($u->nominal_dosen) }}</td>
                                <td align="right">{{ number_format($u->nominal_tendik) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        @if($u->nama != 'Sekolah Pascasarjana')
                                        <a href="{{ route('admin.remun-15.export.single', ['kategori' => 1, 'unit' => $u->id, 'status' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        @endif
                                        <a href="{{ route('admin.remun-15.export.single', ['kategori' => 2, 'unit' => $u->id, 'status' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <td>Pusat</td>
                                <td align="right">{{ number_format($dosen_dibayarkan_pusat) }}</td>
                                <td align="right">{{ number_format($tendik_dibayarkan_pusat) }}</td>
                                <td align="right">{{ number_format($pegawai_dinolkan_pusat) }}</td>
                                <td align="right">{{ number_format($nominal_dosen_pusat) }}</td>
                                <td align="right">{{ number_format($nominal_tendik_pusat) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.remun-15.export.pusat', ['tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td>Total</td>
                                <td align="right">{{ number_format($total_dosen_dibayarkan) }}</td>
                                <td align="right">{{ number_format($total_tendik_dibayarkan) }}</td>
                                <td align="right">{{ number_format($total_pegawai_dinolkan) }}</td>
                                <td align="right">{{ number_format($total_nominal_dosen) }}</td>
                                <td align="right">{{ number_format($total_nominal_tendik) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.remun-15.export.recap', ['tahun' => $tahun]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Download Excel Rekap"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
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
        fixedHeader: true,
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection