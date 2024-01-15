@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring '.($jenis ? $jenis->nama : 'Gaji').' ASN')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring {{ $jenis ? $jenis->nama : 'Gaji' }} ASN</h1>
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
                                <th rowspan="2">Anak Satker</th>
                                <th colspan="3">Dosen</th>
                                <th colspan="3">Tendik</th>
                                <th colspan="3">Total</th>
                                <th rowspan="2" width="60">Opsi</th>
                            </tr>
                            <tr>
                                <th width="80">Pegawai</th>
                                <th width="80">Nominal</th>
                                <th width="80">Potongan</th>
                                <th width="80">Pegawai</th>
                                <th width="80">Nominal</th>
                                <th width="80">Potongan</th>
                                <th width="80">Pegawai</th>
                                <th width="80">Nominal</th>
                                <th width="80">Potongan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                            <tr>
                                <td>{{ $d['anak_satker']->kode }} - {{ $d['anak_satker']->nama }}</td>
                                <td align="right">{{ number_format($d['dosen_jumlah']) }}</td>
                                <td align="right">{{ number_format($d['dosen_nominal']) }}</td>
                                <td align="right">{{ number_format($d['dosen_potongan']) }}</td>
                                <td align="right">{{ number_format($d['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($d['tendik_nominal']) }}</td>
                                <td align="right">{{ number_format($d['tendik_potongan']) }}</td>
                                <td align="right">{{ number_format($d['dosen_jumlah'] + $d['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($d['dosen_nominal'] + $d['tendik_nominal']) }}</td>
                                <td align="right">{{ number_format($d['dosen_potongan'] + $d['tendik_potongan']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        @if($jenis)
                                        <a href="{{ route('admin.gaji.index', ['id' => $d['anak_satker']->id, 'jenis' => $jenis->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat List"><i class="bi-eye"></i></a>
                                        @endif
                                        <a href="{{ route('admin.gaji.monthly', ['id' => $d['anak_satker']->id, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Lihat Rekap Bulanan"><i class="bi-calendar-check"></i></a>
                                        @if($jenis)
                                        <a href="{{ route('admin.gaji.export', ['id' => $d['anak_satker']->id, 'jenis' => $jenis->id, 'kategori' => 1, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.gaji.export', ['id' => $d['anak_satker']->id, 'jenis' => $jenis->id, 'kategori' => 2, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total (PNS)</td>
                                <td align="right">{{ number_format($total_pns['dosen_jumlah']) }}</td>
                                <td align="right">{{ number_format($total_pns['dosen_nominal']) }}</td>
                                <td align="right">{{ number_format($total_pns['dosen_potongan']) }}</td>
                                <td align="right">{{ number_format($total_pns['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($total_pns['tendik_nominal']) }}</td>
                                <td align="right">{{ number_format($total_pns['tendik_potongan']) }}</td>
                                <td align="right">{{ number_format($total_pns['dosen_jumlah'] + $total_pns['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($total_pns['dosen_nominal'] + $total_pns['tendik_nominal']) }}</td>
                                <td align="right">{{ number_format($total_pns['dosen_potongan'] + $total_pns['tendik_potongan']) }}</td>
                                <td align="center">
                                    @if($jenis)
                                    <div class="btn-group">
                                        <a href="{{ route('admin.gaji.export', ['bulan' => $bulan, 'jenis' => $jenis->id, 'status' => 1, 'kategori' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.gaji.export', ['bulan' => $bulan, 'jenis' => $jenis->id, 'status' => 1, 'kategori' => 2, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.gaji.export', ['bulan' => $bulan, 'jenis' => $jenis->id, 'status' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Semua"><i class="bi-file-excel"></i></a>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td align="center">Total (PPPK)</td>
                                <td align="right">{{ number_format($total_pppk['dosen_jumlah']) }}</td>
                                <td align="right">{{ number_format($total_pppk['dosen_nominal']) }}</td>
                                <td align="right">{{ number_format($total_pppk['dosen_potongan']) }}</td>
                                <td align="right">{{ number_format($total_pppk['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($total_pppk['tendik_nominal']) }}</td>
                                <td align="right">{{ number_format($total_pppk['tendik_potongan']) }}</td>
                                <td align="right">{{ number_format($total_pppk['dosen_jumlah'] + $total_pppk['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($total_pppk['dosen_nominal'] + $total_pppk['tendik_nominal']) }}</td>
                                <td align="right">{{ number_format($total_pppk['dosen_potongan'] + $total_pppk['tendik_potongan']) }}</td>
                                <td align="center">
                                    @if($jenis)
                                    <div class="btn-group">
                                        <a href="{{ route('admin.gaji.export', ['bulan' => $bulan, 'jenis' => $jenis->id, 'status' => 2, 'kategori' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.gaji.export', ['bulan' => $bulan, 'jenis' => $jenis->id, 'status' => 2, 'kategori' => 2, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.gaji.export', ['bulan' => $bulan, 'jenis' => $jenis->id, 'status' => 2, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Semua"><i class="bi-file-excel"></i></a>
                                    </div>
                                    @endif
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