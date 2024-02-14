@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Kekurangan Tunjangan Profesi')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Kekurangan Tunjangan Profesi</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-header d-sm-flex justify-content-end align-items-center">
                <select name="periode" class="form-select form-select-sm">
                    <option value="" disabled>--Pilih Periode--</option>
                    @foreach($periode as $p)
                        <option value="{{ $p['tahun'] }}-{{ $p['bulan'] }}" {{ $periode_tahun == $p['tahun'] && $periode_bulan == $p['bulan'] ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($p['bulan']) }} {{ $p['tahun'] }}</option>
                    @endforeach
                </select>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Jenis</th>
                                <th>Pegawai</th>
                                <th>Tunjangan</th>
                                <th>PPh Pasal 21</th>
                                <th>Diterimakan</th>
                                <th class="notexport" width="50">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                            <tr>
                                <td>{{ $d['jenis']->nama }}</td>
                                <td align="right">{{ number_format($d['pegawai']) }}</td>
                                <td align="right">{{ number_format($d['tunjangan']) }}</td>
                                <td align="right">{{ number_format($d['pph']) }}</td>
                                <td align="right">{{ number_format($d['diterimakan']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.tunjangan-profesi.export', ['jenis' => $d['jenis']->id, 'bulan' => $periode_bulan, 'tahun' => $periode_tahun, 'kekurangan' => 1]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total['pegawai']) }}</td>
                                <td align="right">{{ number_format($total['tunjangan']) }}</td>
                                <td align="right">{{ number_format($total['pph']) }}</td>
                                <td align="right">{{ number_format($total['diterimakan']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.tunjangan-profesi.export', ['bulan' => $periode_bulan, 'tahun' => $periode_tahun, 'kekurangan' => 1]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel"><i class="bi-file-excel"></i></a>
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
        buttons: true,
    });

    // Select2
    Spandiv.Select2("select[name=periode]");

    // Change the select
    $(document).on("change", ".card-header select", function() {
        var periode = $("select[name=periode]").val();
        if(periode != null)
            window.location.href = Spandiv.URL("{{ route('admin.tunjangan-profesi.kekurangan.monitoring') }}", {periode: periode});
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection