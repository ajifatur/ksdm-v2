@extends('faturhelper::layouts/admin/main')

@section('title', 'Detail Presensi')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Detail Presensi</h1>
    <a href="{{ route('admin.presensi.index') }}" class="btn btn-sm btn-secondary"><i class="bi-arrow-left me-1"></i> Kembali ke Presensi</a>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th width="20">No.</th>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Kehadiran</th>
                                <th>Dobel Tanggal</th>
                                <th>Akhir Pekan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $n = 1; ?>
                            @foreach($data as $nip=>$d)
                                <tr>
                                    <td>{{ $n }}</td>
                                    <?php
                                        $pegawai = \App\Models\Pegawai::where('nip','=',$nip)->first();
                                    ?>
                                    <td>{{ $pegawai ? $pegawai->nama : '-' }}</td>
                                    <td>{{ $nip }}</td>
                                    <td>{{ count($d) }}</td>
									<?php
										// Menghitung dobel tanggal
										$count_array = array_count_values($d);
										$temp = [];
										foreach($count_array as $key=>$c) {
											if($c > 1)
												array_push($temp, $key);
										}
									?>
									<td><pre class="mb-0" style="font-size: 100%;">{{ count($temp) > 0 ? implode("\n", $temp) : '' }}</pre></td>
									<?php
										// Menghitung presensi di akhir pekan
										$temp = [];
										foreach($d as $key=>$dd) {
											if(date('N', strtotime($dd)) >= 6)
												array_push($temp, $dd);
										}
									?>
									<td><pre class="mb-0" style="font-size: 100%;">{{ count($temp) > 0 ? implode("\n", $temp) : '' }}</pre></td>
                                </tr>
                                <?php $n++ ?>
                            @endforeach
                        </tbody>
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
        pageLength: -1,
        orderAll: true,
        fixedHeader: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
