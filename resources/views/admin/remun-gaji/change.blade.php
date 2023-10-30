@extends('faturhelper::layouts/admin/main')

@section('title', 'Rekapitulasi Mutasi Pegawai')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Rekapitulasi Mutasi Pegawai</h1>
    <a href="{{ route('admin.remun-gaji.monitoring', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary"><i class="bi-arrow-left me-1"></i> Kembali ke Monitoring</a>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="unit" class="form-select form-select-sm">
                            <option value="" disabled selected>--Pilih Unit--</option>
                            @foreach($unit_list as $u)
                            <option value="{{ $u->id }}" {{ Request::query('unit') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                            @endforeach
                            <option value="0" {{ Request::query('unit') == "0" ? 'selected' : '' }}>Pusat</option>
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
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
                <h4 class="mb-4"># Rekapitulasi Dosen:</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable-dosen">
                        <thead class="bg-light">
                            <tr>
                                <th width="200">Uraian</th>
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
                            @foreach($jenis_dosen as $j)
                            <?php
                                $total_bulan_lalu += count($remun_gaji_bulan_sebelumnya['dosen'][$j['key']]);
                                $total_mutasi += (count($dosen[$j['key']]['masuk']) + count($dosen[$j['key']]['keluar']));
                                $total_bulan_ini += count($remun_gaji_bulan_ini['dosen'][$j['key']]);
                            ?>
                            <tr>
                                <td>{{ $j['name'] }} </td>
                                <td align="right">{{ number_format(count($remun_gaji_bulan_sebelumnya['dosen'][$j['key']])) }}</td>
                                <td align="right">{{ number_format(count($dosen[$j['key']]['masuk']) + count($dosen[$j['key']]['keluar'])) }}</td>
                                <td align="right">{{ number_format(count($remun_gaji_bulan_ini['dosen'][$j['key']])) }}</td>
                                <td>{!! $dosen[$j['key']]['keterangan'] !!}</td>
                                <td>{!! $dosen[$j['key']]['pegawai_masuk'] !!}</td>
                                <td>{!! $dosen[$j['key']]['pegawai_keluar'] !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
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
                <hr class="my-2">
                <h4 class="my-4"># Rekapitulasi Tendik:</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable-tendik">
                        <thead class="bg-light">
                            <tr>
                                <th width="200">Uraian</th>
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
                            @foreach($jenis_tendik as $j)
                            <?php
                                $total_bulan_lalu += count($remun_gaji_bulan_sebelumnya['tendik'][$j['key']]);
                                $total_mutasi += (count($tendik[$j['key']]['masuk']) + count($tendik[$j['key']]['keluar']));
                                $total_bulan_ini += count($remun_gaji_bulan_ini['tendik'][$j['key']]);
                            ?>
                            <tr>
                                <td>{{ $j['name'] }} </td>
                                <td align="right">{{ number_format(count($remun_gaji_bulan_sebelumnya['tendik'][$j['key']])) }}</td>
                                <td align="right">{{ number_format(count($tendik[$j['key']]['masuk']) + count($tendik[$j['key']]['keluar'])) }}</td>
                                <td align="right">{{ number_format(count($remun_gaji_bulan_ini['tendik'][$j['key']])) }}</td>
                                <td>{!! $tendik[$j['key']]['keterangan'] !!}</td>
                                <td>{!! $tendik[$j['key']]['pegawai_masuk'] !!}</td>
                                <td>{!! $tendik[$j['key']]['pegawai_keluar'] !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
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
    Spandiv.DataTable("#datatable-dosen", {
        orderAll: true,
        fixedHeader: true,
        buttons: true,
    });
    Spandiv.DataTable("#datatable-tendik", {
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
