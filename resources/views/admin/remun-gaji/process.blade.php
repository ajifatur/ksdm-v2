@extends('faturhelper::layouts/admin/main')

@section('title', 'Proses Remun Gaji')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Proses Remun Gaji</h1>
    <div class="btn-group">
        <a href="#" class="btn btn-sm btn-primary btn-process"><i class="bi-plus me-1"></i> Tambah</a>
    </div>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
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
                                <th>Bulan, Tahun</th>
                                <th>Tanggal Cut-Off</th>
                                <th>Mutasi</th>
                                <th>Pegawai</th>
                                <th>Kelebihan/Kekurangan</th>
                                <th>Remun Gaji Dibayarkan</th>
                                <th width="60">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proses as $p)
                            <tr>
                                <td>
                                    <span class="d-none">{{ $p->tahun }} {{ $p->bulan }}</span>
                                    {{ \Ajifatur\Helpers\DateTimeExt::month($p->bulan) }} {{ $p->tahun }}
                                </td>
                                <td>{{ $p->tanggal }}</td>
                                <td align="right">{{ number_format($p->mutasi) }}</td>
                                <td align="right">{{ number_format($p->pegawai) }}</td>
                                <td align="right">{{ number_format($p->lebih_kurang) }}</td>
                                <td align="right">{{ number_format($p->remun_gaji) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.mutasi.index', ['bulan' => $p->bulan, 'tahun' => $p->tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Lihat Mutasi"><i class="bi-eye"></i></a>
                                        <a href="{{ route('admin.remun-gaji.export.recap', ['bulan' => $p->bulan, 'tahun' => $p->tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download Rekap Excel"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-process" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Proses</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.remun-gaji.process') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal Cut-Off<span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="tanggal" class="form-select form-select-sm {{ $errors->has('tanggal') ? 'border-danger' : '' }}" required>
                                <option value="" disabled selected>--Pilih--</option>
                                @for($t=1; $t<=30; $t++)
                                <option value="{{ $t < 10 ? '0'.$t : $t }}" {{ old('tanggal') == ($t < 10 ? '0'.$t : $t) ? 'selected' : '' }}>{{ $t < 10 ? '0'.$t : $t }}</option>
                                @endfor
                            </select>
                            @if($errors->has('tanggal'))
                            <div class="small text-danger">{{ $errors->first('tanggal') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Bulan<span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="bulan" class="form-select form-select-sm {{ $errors->has('bulan') ? 'border-danger' : '' }}" required>
                                <option value="" disabled selected>--Pilih--</option>
                                @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ old('bulan') == $m ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($m) }}</option>
                                @endfor
                            </select>
                            @if($errors->has('bulan'))
                            <div class="small text-danger">{{ $errors->first('bulan') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tahun<span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="tahun" class="form-select form-select-sm {{ $errors->has('tahun') ? 'border-danger' : '' }}" required>
                                <option value="" disabled selected>--Pilih--</option>
                                @for($y=2023; $y>=2023; $y--)
                                <option value="{{ $y }}" {{ old('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            @if($errors->has('tahun'))
                            <div class="small text-danger">{{ $errors->first('tahun') }}</div>
                            @endif
                        </div>
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

// Button Proses
    $(document).on("click", ".btn-process", function(e) {
        e.preventDefault();
        Spandiv.Modal("#modal-process").show();
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
