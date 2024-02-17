@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring '.($jenis ? $jenis->nama : 'Gaji').' Pegawai Tidak Tetap')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring {{ $jenis ? $jenis->nama : 'Gaji' }} Pegawai Tidak Tetap</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            @if($jenis && $jenis->grup == 1)
                <form method="get" action="">
                    <input type="hidden" name="jenis" value="{{ Request::query('jenis') }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <div class="card-header d-sm-flex justify-content-end align-items-center">
                        <select id="periode" class="form-select form-select-sm">
                            <option value="" disabled>--Pilih Bulan dan Tahun--</option>
                            @foreach($tahun_bulan_grup as $tb)
                                @foreach($tb['bulan'] as $b)
                                    <option value="{{ $tb['tahun'] }}-{{ (int)$b }}" {{ $tb['tahun'].'-'.(int)$b == $tahun.'-'.$bulan ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month((int)$b) }} {{ $tb['tahun'] }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </form>
            @else
                <form method="get" action="">
                    <input type="hidden" name="jenis" value="{{ Request::query('jenis') }}">
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
                                @for($y=2024; $y>=2022; $y--)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                            <button type="submit" class="btn btn-sm btn-info"><i class="bi-filter me-1"></i> Filter</button>
                        </div>
                    </div>
                </form>
            @endif
            <hr class="my-0">
            <div class="card-body">
                @if(Session::get('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="alert-message">{{ Session::get('message') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">Kategori</th>
                                <th rowspan="2" width="80">Pegawai</th>
                                <th rowspan="2" width="80">Nominal Kotor</th>
                                <th rowspan="2" width="80">Nominal Bersih</th>
                                <th rowspan="2" width="30">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                            <tr>
                                <td>{{ $d['kategori']->nama }}</td>
                                <td align="right">{{ number_format($d['pegawai']) }}</td>
                                <td align="right">{{ number_format($d['kotor']) }}</td>
                                <td align="right">{{ number_format($d['bersih']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        @if($jenis)
                                        <a href="{{ route('admin.gaji-kontrak.index', ['kategori' => $d['kategori']->id, 'jenis' => $jenis->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat List"><i class="bi-eye"></i></a>
                                        <a href="{{ route('admin.gaji-kontrak.export.list', ['kategori' => $d['kategori']->id, 'jenis' => $jenis->id,'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel List"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.gaji-kontrak.export.single', ['kategori' => $d['kategori']->id, 'jenis' => $jenis->id,'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Excel Upload MyKeu"><i class="bi-file-excel"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total['pegawai']) }}</td>
                                <td align="right">{{ number_format($total['kotor']) }}</td>
                                <td align="right">{{ number_format($total['bersih']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        @if($jenis)
                                        <a href="{{ route('admin.gaji-kontrak.export.recap', ['jenis' => $jenis->id,'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Download Excel Rekap"><i class="bi-file-excel"></i></a>
                                        @endif
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
    if($("select[id=periode]").length > 0) {
        // Select2
        Spandiv.Select2("select[id=periode]");

        // Change periode
        $(document).on("change", "select[id=periode]", function(e) {
            e.preventDefault();
            var periode = $(this).val();
            var split = periode.split("-");
            $("input[name=bulan]").val(split[1]);
            $("input[name=tahun]").val(split[0]);
            $(this).parents("form").submit();
        })
    }

    // DataTable
    Spandiv.DataTable("#datatable", {
        orderAll: true,
        pageLength: -1
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection