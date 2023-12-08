@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring SPKGB Pegawai PTNBH')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring SPKGB Pegawai PTNBH</h1>
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
                                <th>Bulan</th>
                                <th>Jumlah Diproses</th>
                                <th width="50">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                            <tr>
                                <td><span class="d-none">{{ $d['bulan'] < 10 ? '0'.$d['bulan'] : $d['bulan'] }}</span>{{ $d['nama'] }}</td>
                                <td align="right">{{ $d['spkgb'] }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.spkgb.ptnbh.index', ['bulan' => $d['bulan'], 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="List SPKGB"><i class="bi-eye"></i></a>
                                        <a href="{{ route('admin.spkgb.print.recap', ['bulan' => $d['bulan'], 'tahun' => $tahun, 'type' => 2]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Cetak Rekap PDF" target="_blank"><i class="bi-file-pdf"></i></a>
                                        <a href="{{ route('admin.spkgb.print.batch', ['bulan' => $d['bulan'], 'tahun' => $tahun, 'type' => 2]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Cetak Batch PDF" target="_blank"><i class="bi-list"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total) }}</td>
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