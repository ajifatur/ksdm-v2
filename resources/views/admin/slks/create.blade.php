@extends('faturhelper::layouts/admin/main')

@section('title', 'Tambah Satyalancana Karya Satya')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Tambah Satyalancana Karya Satya</h1>
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
                <form method="post" action="{{ route('admin.slks.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nomor Keppres <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="no_keppres" class="form-control form-control-sm {{ $errors->has('no_keppres') ? 'border-danger' : '' }}" value="{{ old('no_keppres') }}">
                            @if($errors->has('no_keppres'))
                            <div class="small text-danger">{{ $errors->first('no_keppres') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal Keppres <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tanggal_keppres" class="form-control form-control-sm {{ $errors->has('tanggal_keppres') ? 'border-danger' : '' }}" value="{{ old('tanggal_keppres') }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
                                <span class="input-group-text"><i class="bi-calendar2"></i></span>
                            </div>
                            @if($errors->has('tanggal_keppres'))
                            <div class="small text-danger">{{ $errors->first('tanggal_keppres') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nomor Keputusan Rektor <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="no_keprektor" class="form-control form-control-sm {{ $errors->has('no_keprektor') ? 'border-danger' : '' }}" value="{{ old('no_keprektor') }}">
                            @if($errors->has('no_keprektor'))
                            <div class="small text-danger">{{ $errors->first('no_keprektor') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal Keputusan Rektor <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tanggal_keprektor" class="form-control form-control-sm {{ $errors->has('tanggal_keprektor') ? 'border-danger' : '' }}" value="{{ old('tanggal_keprektor') }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
                                <span class="input-group-text"><i class="bi-calendar2"></i></span>
                            </div>
                            @if($errors->has('tanggal_keprektor'))
                            <div class="small text-danger">{{ $errors->first('tanggal_keprektor') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Periode <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="periode" class="form-control form-control-sm {{ $errors->has('periode') ? 'border-danger' : '' }}" value="{{ old('periode') }}">
                            @if($errors->has('periode'))
                            <div class="small text-danger">{{ $errors->first('periode') }}</div>
                            @endif
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
    // TMT
    Spandiv.DatePicker("input[name=tanggal_keppres]");
    Spandiv.DatePicker("input[name=tanggal_keprektor]");
</script>

@endsection