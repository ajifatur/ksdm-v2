@extends('faturhelper::layouts/admin/main')

@section('title', 'Import Uang Makan '.$jenis)

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Import Uang Makan {{ $jenis }}</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route($route) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Anak Satker <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="anak_satker" class="form-select form-select-sm" required>
                                <option value="0" disabled selected>--Pilih Anak Satker--</option>
                                @foreach($anak_satker as $a)
                                <option value="{{ $a->kode }}">{{ $a->kode }} - {{ $a->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if($jenis == 'PPPK')
                    <div class="row mb-3 d-none">
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
                    <div class="row mb-3 d-none">
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
                    @endif
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