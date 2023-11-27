@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Remun Gaji')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Remun Gaji</h1>
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
                                <th rowspan="2">Unit</th>
                                <th colspan="4">Dosen</th>
                                <th colspan="4">Tendik</th>
                                <th rowspan="2" width="30">Excel Simkeu</th>
                                <th rowspan="2" width="30">Laporan PDF</th>
                                <th rowspan="2" width="30">List</th>
                                <th rowspan="2" width="30">Rekap Mutasi</th>
                            </tr>
                            <tr>
                                <th width="90">Pegawai</th>
                                <th width="90">Remun Gaji</th>
                                <th width="90">Selisih</th>
                                <th width="90">Dibayarkan</th>
                                <th width="90">Pegawai</th>
                                <th width="90">Remun Gaji</th>
                                <th width="90">Selisih</th>
                                <th width="90">Dibayarkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $total_pegawai['dosen'] = 0;
                                $total_remun_gaji['dosen'] = 0;
                                $total_selisih['dosen'] = 0;
                                $total_dibayarkan['dosen'] = 0;
                                $total_pegawai['tendik'] = 0;
                                $total_remun_gaji['tendik'] = 0;
                                $total_selisih['tendik'] = 0;
                                $total_dibayarkan['tendik'] = 0;
                            ?>
                            @foreach($data as $d)
                            <tr>
                                <td>{{ is_object($d['unit']) ? $d['unit']->pusat == 1 ? 'Pusat - '.$d['unit']->nama : $d['unit']->nama : $d['unit'] }}</td>
                                @foreach($d['nominal'] as $key=>$n)
                                <td align="right">{{ number_format($n['pegawai']) }}</td>
                                <td align="right">{{ number_format($n['remun_gaji']) }}</td>
                                <td align="right">{{ number_format($n['selisih']) }}</td>
                                <td align="right">{{ number_format($n['dibayarkan']) }}</td>
                                <?php
                                    if((is_object($d['unit']) && $d['unit']->pusat == 0) || !is_object($d['unit'])) {
                                        if($key == 0) {
                                            $total_pegawai['dosen'] += $n['pegawai'];
                                            $total_remun_gaji['dosen'] += $n['remun_gaji'];
                                            $total_selisih['dosen'] += $n['selisih'];
                                            $total_dibayarkan['dosen'] += $n['dibayarkan'];
                                        }
                                        elseif($key == 1) {
                                            $total_pegawai['tendik'] += $n['pegawai'];
                                            $total_remun_gaji['tendik'] += $n['remun_gaji'];
                                            $total_selisih['tendik'] += $n['selisih'];
                                            $total_dibayarkan['tendik'] += $n['dibayarkan'];
                                        }
                                    }
                                ?>
                                @endforeach
                                <td align="center">
                                    @if(is_object($d['unit']))
                                        @if($d['unit']->pusat == 0)
                                            <div class="btn-group">
                                                <a href="{{ route('admin.remun-gaji.export.single', ['kategori' => 1, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                                <a href="{{ route('admin.remun-gaji.export.single', ['kategori' => 2, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    @else
                                        <div class="btn-group">
                                            <a href="{{ route('admin.remun-gaji.export.pusat', ['kategori' => 1, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download"><i class="bi-file-excel"></i></a>
                                            <a href="{{ route('admin.remun-gaji.export.pusat', ['kategori' => 2, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download"><i class="bi-file-excel"></i></a>
                                        </div>
                                    @endif
                                </td>
                                <td align="center">
                                    @if(is_object($d['unit']))
                                        <div class="btn-group">
                                            <a href="{{ route('admin.remun-gaji.print', ['kategori' => 1, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" target="_blank" data-bs-toggle="tooltip" title="Download PDF Dosen"><i class="bi-file-pdf"></i></a>
                                            <a href="{{ route('admin.remun-gaji.print', ['kategori' => 2, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" target="_blank" data-bs-toggle="tooltip" title="Download PDF Tendik"><i class="bi-file-pdf"></i></a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td align="center">
                                    @if(is_object($d['unit']))
                                        <div class="btn-group">
                                            <a href="{{ route('admin.remun-gaji.index', ['kategori' => 1, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="List Dosen"><i class="bi-eye"></i></a>
                                            <a href="{{ route('admin.remun-gaji.index', ['kategori' => 2, 'unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="List Tendik"><i class="bi-eye"></i></a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td align="center">
                                    @if(is_object($d['unit']))
                                        @if($d['unit']->pusat == 0)
                                            <div class="btn-group">
                                                <a href="{{ route('admin.remun-gaji.change', ['unit' => $d['unit']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Lihat Rekap Mutasi"><i class="bi-list"></i></a>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    @else
                                        <div class="btn-group">
                                            <a href="{{ route('admin.remun-gaji.change', ['unit' => 0, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Lihat Rekap Mutasi"><i class="bi-list"></i></a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total_pegawai['dosen']) }}</td>
                                <td align="right">{{ number_format($total_remun_gaji['dosen']) }}</td>
                                <td align="right">{{ number_format($total_selisih['dosen']) }}</td>
                                <td align="right">{{ number_format($total_dibayarkan['dosen']) }}</td>
                                <td align="right">{{ number_format($total_pegawai['tendik']) }}</td>
                                <td align="right">{{ number_format($total_remun_gaji['tendik']) }}</td>
                                <td align="right">{{ number_format($total_selisih['tendik']) }}</td>
                                <td align="right">{{ number_format($total_dibayarkan['tendik']) }}</td>
                                <td colspan="4"></td>
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