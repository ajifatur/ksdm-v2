@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Uang Makan PNS')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Uang Makan PNS</h1>
    <div class="btn-group d-none">
        <a href="#" class="btn btn-sm btn-primary btn-import"><i class="bi-upload me-1"></i> Import File</a>
        <!-- <a href="#" class="btn btn-sm btn-secondary btn-import-old"><i class="bi-upload me-1"></i> Import File (Format Lama)</a> -->
    </div>
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
                                <th width="80">Nominal Kotor</th>
                                <th width="80">Nominal Bersih</th>
                                <th width="80">Pegawai</th>
                                <th width="80">Nominal Kotor</th>
                                <th width="80">Nominal Bersih</th>
                                <th width="80">Pegawai</th>
                                <th width="80">Nominal Kotor</th>
                                <th width="80">Nominal Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                            <tr>
                                <td>{{ $d['anak_satker']->kode }} - {{ $d['anak_satker']->nama }}</td>
                                <td align="right">{{ number_format($d['dosen_jumlah']) }}</td>
                                <td align="right">{{ number_format($d['dosen_kotor']) }}</td>
                                <td align="right">{{ number_format($d['dosen_bersih']) }}</td>
                                <td align="right">{{ number_format($d['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($d['tendik_kotor']) }}</td>
                                <td align="right">{{ number_format($d['tendik_bersih']) }}</td>
                                <td align="right">{{ number_format($d['dosen_jumlah'] + $d['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($d['dosen_kotor'] + $d['tendik_kotor']) }}</td>
                                <td align="right">{{ number_format($d['dosen_bersih'] + $d['tendik_bersih']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.uang-makan.index', ['id' => $d['anak_satker']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat List"><i class="bi-eye"></i></a>
                                        <a href="{{ route('admin.uang-makan.export', ['id' => $d['anak_satker']->id, 'kategori' => 1, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.uang-makan.export', ['id' => $d['anak_satker']->id, 'kategori' => 2, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total['dosen_jumlah']) }}</td>
                                <td align="right">{{ number_format($total['dosen_kotor']) }}</td>
                                <td align="right">{{ number_format($total['dosen_bersih']) }}</td>
                                <td align="right">{{ number_format($total['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($total['tendik_kotor']) }}</td>
                                <td align="right">{{ number_format($total['tendik_bersih']) }}</td>
                                <td align="right">{{ number_format($total['dosen_jumlah'] + $total['tendik_jumlah']) }}</td>
                                <td align="right">{{ number_format($total['dosen_kotor'] + $total['tendik_kotor']) }}</td>
                                <td align="right">{{ number_format($total['dosen_bersih'] + $total['tendik_bersih']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.uang-makan.export', ['kategori' => 1, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.uang-makan.export', ['kategori' => 2, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                        <a href="{{ route('admin.uang-makan.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Semua"><i class="bi-file-excel"></i></a>
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

<div class="modal fade" id="modal-import-old" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import File</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.uang-makan.import-old') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Anak Satker:</label>
                        <select name="anak_satker" class="form-select form-select-sm" required>
                            <option value="0" disabled selected>--Pilih Anak Satker--</option>
                            @foreach($anak_satker as $a)
                            <option value="{{ $a->kode }}">{{ $a->kode }} - {{ $a->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Bulan:</label>
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="0" disabled selected>--Pilih--</option>
                            @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($m) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Tahun:</label>
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="0" disabled>--Pilih--</option>
                            @for($y=date('Y'); $y>=2022; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
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
    $(document).on("click", ".btn-import-old", function(e) {
        e.preventDefault();
        Spandiv.Modal("#modal-import-old").show();
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection