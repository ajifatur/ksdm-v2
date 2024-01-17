@extends('faturhelper::layouts/admin/main')

@section('title', 'Import Uang Lembur ASN')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Import Uang Lembur ASN</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.uang-lembur.import') }}" enctype="multipart/form-data">
                    @csrf
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