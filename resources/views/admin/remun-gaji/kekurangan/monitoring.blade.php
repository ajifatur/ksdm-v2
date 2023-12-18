@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Kekurangan Remun Gaji')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Kekurangan Remun Gaji</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-header d-sm-flex justify-content-end align-items-center">
                <select name="periode" class="form-select form-select-sm">
                    <option value="" disabled>--Pilih Periode--</option>
                    @foreach($periode as $p)
                        @foreach($p['bulan'] as $b)
                        <option value="{{ $p['tahun'].'-'.$b }}" {{ Request::query('periode') == $p['tahun'].'-'.$b ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($b) }} {{ $p['tahun'] }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Unit</th>
                                <th width="30">Excel Simkeu</th>
                                <th width="30">Laporan PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                            <tr>
                                <td>{{ is_object($d['unit']) ? $d['unit']->pusat == 1 ? 'Pusat - '.$d['unit']->nama : $d['unit']->nama : $d['unit'] }}</td>
                                <td align="center">
                                    @if(is_object($d['unit']))
                                        @if($d['unit']->pusat == 0)
                                            <div class="btn-group">
                                                <a href="{{ route('admin.remun-gaji.kekurangan.export.single', ['kategori' => 1, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                                <a href="{{ route('admin.remun-gaji.kekurangan.export.single', ['kategori' => 2, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    @else
                                        <div class="btn-group">
                                            <a href="{{ route('admin.remun-gaji.kekurangan.export.pusat', ['kategori' => 1, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download"><i class="bi-file-excel"></i></a>
                                            <a href="{{ route('admin.remun-gaji.kekurangan.export.pusat', ['kategori' => 2, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download"><i class="bi-file-excel"></i></a>
                                        </div>
                                    @endif
                                </td>
                                <td align="center">
                                    @if(is_object($d['unit']))
                                        <div class="btn-group">
                                            <a href="{{ route('admin.remun-gaji.kekurangan.print', ['kategori' => 1, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" target="_blank" data-bs-toggle="tooltip" title="Download PDF Dosen"><i class="bi-file-pdf"></i></a>
                                            <a href="{{ route('admin.remun-gaji.kekurangan.print', ['kategori' => 2, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" target="_blank" data-bs-toggle="tooltip" title="Download PDF Tendik"><i class="bi-file-pdf"></i></a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.remun-gaji.kekurangan.export.single', ['kategori' => 1, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.remun-gaji.kekurangan.export.single', ['kategori' => 2, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.remun-gaji.kekurangan.export.recap', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Download Excel Semua"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.remun-gaji.kekurangan.print', ['kategori' => 1, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" target="_blank" data-bs-toggle="tooltip" title="Download PDF Dosen"><i class="bi-file-pdf"></i></a>
                                        <a href="{{ route('admin.remun-gaji.kekurangan.print', ['kategori' => 2, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" target="_blank" data-bs-toggle="tooltip" title="Download PDF Tendik"><i class="bi-file-pdf"></i></a>
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

    // Select2
    Spandiv.Select2("select[name=periode]");

    // Change the select
    $(document).on("change", ".card-header select", function() {
		var periode = $("select[name=periode]").val();
        window.location.href = Spandiv.URL("{{ route('admin.remun-gaji.kekurangan.monitoring') }}", {periode: periode});
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection