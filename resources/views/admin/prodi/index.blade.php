@extends('faturhelper::layouts/admin/main')

@section('title', 'Program Studi')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Program Studi</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-header d-sm-flex justify-content-end align-items-center">
                <div>
                    <select name="tahun" class="form-select form-select-sm">
                        <option value="0" disabled selected>--Pilih Tahun--</option>
                        @for($y=date('Y');$y>=2024;$y--)
                        <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama</th>
                                <th width="100">Jumlah</th>
                                <th width="50">Kriteria</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $total = 0; ?>
                            @foreach($prodi as $p)
                            <tr>
                                <td>{{ $p->nama }}</td>
                                <td align="right">{{ number_format($p->kriteria()->where('tahun','=',$tahun)->first()->jumlah) }}</td>
                                <td>{{ $p->kriteria()->where('tahun','=',$tahun)->first()->kriteria }}</td>
                            </tr>
                            <?php $total += $p->kriteria()->where('tahun','=',$tahun)->first()->jumlah ?>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td>Total</td>
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
        fixedHeader: true
    });
    
    // Change the select
    $(document).on("change", ".card-header select", function() {
		var tahun = $("select[name=tahun]").val();
        window.location.href = Spandiv.URL("{{ route('admin.prodi.index') }}", {tahun: tahun});
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
