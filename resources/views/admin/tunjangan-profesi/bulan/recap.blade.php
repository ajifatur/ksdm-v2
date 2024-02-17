@extends('faturhelper::layouts/admin/main')

@section('title', 'Rekap Tunjangan Profesi Per Bulan')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Rekap Tunjangan Profesi Per Bulan</h1>
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
                                <th rowspan="2">Bulan</th>
                                @foreach($jenis as $j)
                                <th colspan="2">{{ $j->nama }}</th>
                                @endforeach
                                <th colspan="2">Total</th>
                                <th rowspan="2" width="50">Opsi</th>
                            </tr>
							<tr>
                                @foreach($jenis as $j)
                                <th width="80">Pegawai</th>
                                <th width="80">Nominal</th>
                                @endforeach
                                <th width="80">Pegawai</th>
                                <th width="80">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach($jenis as $key=>$j) {
                                    $pegawai[$key] = 0;
                                }
                            ?>
                            @foreach($data as $d)
                            <tr>
								<td>
                                    <span class="d-none">{{ $d['bulan'] < 10 ? '0'.$d['bulan'] : $d['bulan'] }}</span>
                                    {{ $d['bulan_nama'] }}
                                </td>
								<?php $total_pegawai = 0; ?>
								<?php $total_nominal = 0; ?>
                                <?php $i = 0; ?>
								@foreach($d['tunjangan_profesi'] as $tp)
								<td align="right">{{ number_format($tp['pegawai']) }}</td>
								<td align="right">{{ number_format($tp['tunjangan']) }}</td>
								<?php if($tp['jenis']->id != 1) $total_pegawai += $tp['pegawai']; ?>
								<?php if($tp['jenis']->id != 1) $pegawai[$i] += $tp['pegawai']; ?>
								<?php $total_nominal += $tp['tunjangan']; ?>
                                <?php $i++; ?>
								@endforeach
								<td align="right">{{ number_format($total_pegawai) }}</td>
								<td align="right">{{ number_format($total_nominal) }}</td>
                                <td align="center">
									<div class="btn-group">
										@foreach($d['tunjangan_profesi'] as $tp)
                                        <a href="{{ route('admin.tunjangan-profesi.export', ['jenis' => $tp['jenis']->id, 'bulan' => $d['bulan'], 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Excel Tunjangan {{ $tp['jenis']->nama }}"><i class="bi-file-excel"></i></a>
										@endforeach
                                        <a href="{{ route('admin.tunjangan-profesi.export', ['bulan' => $d['bulan'], 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Semua"><i class="bi-file-excel"></i></a>
									</div>
								</td>
							</tr>
							@endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
								@foreach($total_tunjangan as $key=>$t)
								<td align="right">{{ $key == 0 ? number_format($pegawai[1]) : number_format($pegawai[$key]) }}</td>
								<td align="right">{{ number_format($t) }}</td>
								@endforeach
								<td align="right">{{ number_format(array_sum($pegawai)) }}</td>
								<td align="right">{{ number_format(array_sum($total_tunjangan)) }}</td>
                                <td align="center"></td>
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
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection