@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Kekurangan Remun Gaji')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Kekurangan Remun Gaji</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">Unit</th>
                                <th colspan="2">Dosen</th>
                                <th colspan="2">Tendik</th>
                                <th rowspan="2" width="30">Excel Simkeu</th>
                                <th rowspan="2" width="30">Laporan PDF</th>
                            </tr>
                            <tr>
                                <th width="90">Pegawai</th>
                                <th width="90">Nominal</th>
                                <th width="90">Pegawai</th>
                                <th width="90">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $total_pegawai['dosen'] = 0;
                                $total_kekurangan['dosen'] = 0;
                                $total_pegawai['tendik'] = 0;
                                $total_kekurangan['tendik'] = 0;
                            ?>
                            @foreach($data as $d)
                            <tr>
                                <td>{{ is_object($d['unit']) ? $d['unit']->pusat == 1 ? 'Pusat - '.$d['unit']->nama : $d['unit']->nama : $d['unit'] }}</td>
                                @foreach($d['nominal'] as $key=>$n)
                                <td align="right">{{ number_format($n['pegawai']) }}</td>
                                <td align="right">{{ number_format($n['kekurangan']) }}</td>
                                <?php
                                    if((is_object($d['unit']) && $d['unit']->pusat == 0) || !is_object($d['unit'])) {
                                        if($key == 0) {
                                            $total_pegawai['dosen'] += $n['pegawai'];
                                            $total_kekurangan['dosen'] += $n['kekurangan'];
                                        }
                                        elseif($key == 1) {
                                            $total_pegawai['tendik'] += $n['pegawai'];
                                            $total_kekurangan['tendik'] += $n['kekurangan'];
                                        }
                                    }
                                ?>
                                @endforeach
                                <td align="center">
                                    @if(is_object($d['unit']))
                                        @if($d['unit']->pusat == 0)
                                            <div class="btn-group">
                                                <a href="{{ route('admin.remun-gaji.kekurangan.export.single', ['kategori' => 1, 'unit' => $d['unit']->id, 'id' => 1]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                                <a href="{{ route('admin.remun-gaji.kekurangan.export.single', ['kategori' => 2, 'unit' => $d['unit']->id, 'id' => 1]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    @else
                                        <div class="btn-group">
                                            <a href="{{ route('admin.remun-gaji.kekurangan.export.pusat', ['kategori' => 1, 'id' => 1]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download"><i class="bi-file-excel"></i></a>
                                            <a href="{{ route('admin.remun-gaji.kekurangan.export.pusat', ['kategori' => 2, 'id' => 1]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download"><i class="bi-file-excel"></i></a>
                                        </div>
                                    @endif
                                </td>
                                <td align="center">
                                    @if(is_object($d['unit']))
                                        <div class="btn-group">
                                            <a href="{{ route('admin.remun-gaji.kekurangan.print', ['kategori' => 1, 'unit' => $d['unit']->id, 'id' => 1]) }}" class="btn btn-sm btn-info" target="_blank" data-bs-toggle="tooltip" title="Download PDF Dosen"><i class="bi-file-pdf"></i></a>
                                            <a href="{{ route('admin.remun-gaji.kekurangan.print', ['kategori' => 2, 'unit' => $d['unit']->id, 'id' => 1]) }}" class="btn btn-sm btn-warning" target="_blank" data-bs-toggle="tooltip" title="Download PDF Tendik"><i class="bi-file-pdf"></i></a>
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
                                <td align="right">{{ number_format($total_pegawai['dosen']) }}</td>
                                <td align="right">{{ number_format($total_kekurangan['dosen']) }}</td>
                                <td align="right">{{ number_format($total_pegawai['tendik']) }}</td>
                                <td align="right">{{ number_format($total_kekurangan['tendik']) }}</td>
                                <td colspan="2"></td>
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