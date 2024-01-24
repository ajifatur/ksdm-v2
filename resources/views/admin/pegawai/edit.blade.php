@extends('faturhelper::layouts/admin/main')

@section('title', 'Edit Pegawai')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Pegawai</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.pegawai.update') }}" enctype="multipart/form-data">
                    @csrf
					<input type="hidden" name="id" value="{{ $pegawai->id }}">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">NIP</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $pegawai->nip }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nama <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
							<input type="text" name="nama" class="form-control form-control-sm {{ $errors->has('nama') ? 'border-danger' : '' }}" value="{{ $pegawai->nama }}">
                            @if($errors->has('nama'))
                            <div class="small text-danger">{{ $errors->first('nama') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Gelar Depan <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
							<input type="text" name="gelar_depan" class="form-control form-control-sm {{ $errors->has('gelar_depan') ? 'border-danger' : '' }}" value="{{ $pegawai->gelar_depan }}">
                            @if($errors->has('gelar_depan'))
                            <div class="small text-danger">{{ $errors->first('gelar_depan') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Gelar Belakang <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
							<input type="text" name="gelar_belakang" class="form-control form-control-sm {{ $errors->has('gelar_belakang') ? 'border-danger' : '' }}" value="{{ $pegawai->gelar_belakang }}">
                            @if($errors->has('gelar_belakang'))
                            <div class="small text-danger">{{ $errors->first('gelar_belakang') }}</div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nama Supplier</label>
                        <div class="col-lg-10 col-md-9">
							<input type="text" name="nama_supplier" class="form-control form-control-sm {{ $errors->has('nama_supplier') ? 'border-danger' : '' }}" value="{{ $pegawai->nama_supplier }}">
                            @if($errors->has('nama_supplier'))
                            <div class="small text-danger">{{ $errors->first('nama_supplier') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nama Pemilik Rekening BTN</label>
                        <div class="col-lg-10 col-md-9">
							<input type="text" name="nama_btn" class="form-control form-control-sm {{ $errors->has('nama_btn') ? 'border-danger' : '' }}" value="{{ $pegawai->nama_btn }}">
                            @if($errors->has('nama_btn'))
                            <div class="small text-danger">{{ $errors->first('nama_btn') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nomor Rekening BTN</label>
                        <div class="col-lg-10 col-md-9">
							<input type="text" name="norek_btn" class="form-control form-control-sm {{ $errors->has('norek_btn') ? 'border-danger' : '' }}" value="{{ $pegawai->norek_btn }}">
                            @if($errors->has('norek_btn'))
                            <div class="small text-danger">{{ $errors->first('norek_btn') }}</div>
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