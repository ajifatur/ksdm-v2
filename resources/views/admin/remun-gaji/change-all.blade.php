@extends('faturhelper::layouts/admin/main')

@section('title', 'Perubahan Remunerasi Gaji')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Perubahan Remunerasi Gaji</h1>
    <a href="{{ route('admin.remun-gaji.monitoring', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary"><i class="bi-arrow-left me-1"></i> Kembali ke Monitoring</a>
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
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th width="80">Bulan Lalu</th>
                                <th width="80">Mutasi</th>
                                <th width="80">Bulan Ini</th>
                                <th>Keterangan</th>
                                <th class="notexport">Pegawai Masuk</th>
                                <th class="notexport">Pegawai Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $total_bulan_lalu = 0;
                                $total_mutasi = 0;
                                $total_bulan_ini = 0;
                            ?>
                            <?php
                                $total_bulan_lalu += count($remun_gaji_bulan_sebelumnya);
                                $total_mutasi += (count($compare['masuk']) + count($compare['keluar']));
                                $total_bulan_ini += count($remun_gaji_bulan_ini);
                            ?>
                            <tr>
                                <td align="right">{{ number_format(count($remun_gaji_bulan_sebelumnya)) }}</td>
                                <td align="right">{{ number_format(count($compare['masuk']) + count($compare['keluar'])) }}</td>
                                <td align="right">{{ number_format(count($remun_gaji_bulan_ini)) }}</td>
                                <td>{!! $compare['keterangan'] !!}</td>
                                <td>{!! $compare['pegawai_masuk'] !!}</td>
                                <td>{!! $compare['pegawai_keluar'] !!}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="right">{{ number_format($total_bulan_lalu) }}</td>
                                <td align="right">{{ number_format($total_mutasi) }}</td>
                                <td align="right">{{ number_format($total_bulan_ini) }}</td>
                                <td></td>
                                <td></td>
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
        fixedHeader: true,
        buttons: true,
    });
</script>

@endsection

@section('css')

<style>
    .table tr td {vertical-align: top!important;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
