@extends('faturhelper::layouts/admin/main')

@section('title', 'Import Uang Makan '.($jenis == 1 ? 'Pegawai Tetap Non ASN' : 'Pegawai Tidak Tetap'))

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Import Uang Makan {{ $jenis == 1 ? 'Pegawai Tetap Non ASN' : 'Pegawai Tidak Tetap' }}</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.uang-makan-non-asn.import') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Unit <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="unit" class="form-select form-select-sm" required>
                                <option value="0" disabled selected>--Pilih Unit--</option>
                                @foreach($unit as $u)
                                <option value="{{ $u->id }}" {{ old('unit') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Kategori <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="kategori" class="form-select form-select-sm" required>
                                <option value="0" disabled selected>--Pilih Kategori--</option>
                                <option value="1" {{ old('kategori') == 1 ? 'selected' : '' }}>Dosen</option>
                                <option value="2" {{ old('kategori') == 2 ? 'selected' : '' }}>Tendik</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Bulan <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="bulan" class="form-select form-select-sm" required>
                                <option value="0" disabled selected>--Pilih Bulan--</option>
                                @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ old('bulan') == $m ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($m) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tahun <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="tahun" class="form-select form-select-sm" required>
                                <option value="0" disabled selected>--Pilih Tahun--</option>
                                @for($y=date('Y'); $y>=2023; $y--)
                                <option value="{{ $y }}" {{ old('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">File <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="file" name="file" class="form-control form-control-sm" accept=".xls, .xlsx" required>
                            <div class="small text-muted">File harus berekstensi .xls atau .xlsx</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-2 col-md-3"></div>
                        <div class="col-lg-10 col-md-9">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi-save me-1"></i> Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
	</div>
</div>

@endsection

@section('js')

<script>
    // Select2
    Spandiv.Select2("select[name=unit]");
    Spandiv.Select2("select[name=kategori]");
    Spandiv.Select2("select[name=bulan]");
    Spandiv.Select2("select[name=tahun]");
</script>

@endsection