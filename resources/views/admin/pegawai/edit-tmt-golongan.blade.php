@extends('faturhelper::layouts/admin/main')

@section('title', 'Edit TMT Golongan Pegawai')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit TMT Golongan Pegawai</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.pegawai.update-tmt-golongan') }}" enctype="multipart/form-data">
                    @csrf
					<input type="hidden" name="id" value="{{ $pegawai->id }}">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nama / NIP</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $pegawai->nama }} - {{ $pegawai->nip }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">TMT Golongan <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tmt_golongan" class="form-control form-control-sm {{ $errors->has('tmt_golongan') ? 'border-danger' : '' }}" value="{{ $pegawai->tmt_golongan != null ? date('d/m/Y', strtotime($pegawai->tmt_golongan)) : '' }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
                                <span class="input-group-text"><i class="bi-calendar2"></i></span>
                            </div>
                            @if($errors->has('tmt_golongan'))
                            <div class="small text-danger">{{ $errors->first('tmt_golongan') }}</div>
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
    Spandiv.DatePicker("input[name=tmt_golongan]");
</script>

@endsection