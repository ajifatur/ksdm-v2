@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring '.($jenis ? $jenis->nama : 'Gaji').' PNS')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring {{ $jenis ? $jenis->nama : 'Gaji' }} PNS</h1>
    @if($jenis)
    <a href="#" class="btn btn-sm btn-primary btn-import"><i class="bi-upload me-1"></i> Import File</a>
    @endif
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="jenis" class="form-select form-select-sm">
                            <option value="0">Semua Jenis</option>
                            @foreach($jenis_gaji as $j)
                            <option value="{{ $j->id }}" {{ $jenis && $jenis->id == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                            @endforeach
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
                            @for($y=date('Y'); $y>=2022; $y--)
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
                                        <a href="{{ route('admin.gaji.recap', ['id' => $d['anak_satker']->id, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Lihat Rekap Bulanan"><i class="bi-calendar-check"></i></a>
                                        @if($jenis)
                                        <a href="{{ route('admin.gaji.excel', ['id' => $d['anak_satker']->id, 'jenis' => $jenis->id, 'kategori' => 1, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.gaji.excel', ['id' => $d['anak_satker']->id, 'jenis' => $jenis->id, 'kategori' => 2, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total['dosen_jumlah']) }}</td>
                                <td align="right">{{ number_format($total['dosen_nominal']) }}</td>
                                <td align="right">{{ number_format($total['dosen_potongan']) }}</td>
                                <td align="right">{{ number_format($total['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($total['tendik_nominal']) }}</td>
                                <td align="right">{{ number_format($total['tendik_potongan']) }}</td>
                                <td align="right">{{ number_format($total['dosen_jumlah'] + $total['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($total['dosen_nominal'] + $total['tendik_nominal']) }}</td>
                                <td align="right">{{ number_format($total['dosen_potongan'] + $total['tendik_potongan']) }}</td>
                                <td align="center">
                                    @if($jenis)
                                    <div class="btn-group">
                                        <a href="{{ route('admin.gaji.excel', ['bulan' => $bulan, 'jenis' => $jenis->id, 'kategori' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.gaji.excel', ['bulan' => $bulan, 'jenis' => $jenis->id, 'kategori' => 2, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
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

<div class="modal fade" id="modal-import" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import File</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.gaji.import') }}" enctype="multipart/form-data">
                @csrf
                @if($jenis)
                <input type="hidden" name="jenis" value="{{ $jenis->id }}">
                @endif
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Kode Satker:</label>
                        <select name="satker" class="form-select form-select-sm" required>
                            <option value="0" disabled>--Pilih Satker--</option>
                            <!-- <option value="690645" {{ $tahun >= 2023 ? 'selected' : '' }}>690645</option> -->
                            <!-- <option value="677507" {{ $tahun < 2023 ? 'selected' : '' }}>677507</option> -->
                            <option value="690645">690645</option>
                        </select>
                    </div>
                    <div>
                        <label>File:</label>
                        <input type="file" name="file" class="form-control form-control-sm {{ $errors->has('file') ? 'border-danger' : '' }}" accept=".xls, .xlsx">
                        <div class="small text-muted">File harus berekstensi .xls atau .xlsx</div>
                        @if($errors->has('file'))
                        <div class="small text-danger">{{ $errors->first('file') }}</div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-primary" type="submit">Submit</button>
                    <button class="btn btn-sm btn-danger" type="button" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')

<script type="text/javascript">
    // DataTable
    Spandiv.DataTable("#datatable", {
        orderAll: true,
        pageLength: -1
    });

    // Button Import
    $(document).on("click", ".btn-import", function(e) {
        e.preventDefault();
        Spandiv.Modal("#modal-import").show();
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection