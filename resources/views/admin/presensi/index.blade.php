@extends('faturhelper::layouts/admin/main')

@section('title', 'Presensi')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Presensi</h1>
    <a href="#" class="btn btn-sm btn-primary btn-import"><i class="bi-upload me-1"></i> Import File</a>
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
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th width="30" class="notexport"><input type="checkbox" class="form-check-input checkbox-all"></th>
                                <th>File</th>
                                <th width="100" class="notexport">Waktu Upload</th>
                                <th width="40" class="notexport">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($files as $file)
                            <tr>
                                <td align="center"><input type="checkbox" class="form-check-input checkbox-one" data-id="{{ \Ajifatur\Helpers\FileExt::info($file)['nameWithoutExtension'].'.txt' }}"></td>
                                <td>
                                    <a href="{{ route('admin.presensi.detail', ['file' => \Ajifatur\Helpers\FileExt::info($file)['nameWithoutExtension'].'.txt' ]) }}">    
                                        {{ \Ajifatur\Helpers\FileExt::info($file)['nameWithoutExtension'].'.txt' }}
                                    </a>
                                </td>
                                <td>
                                    <span class="d-none">{{ substr(\Ajifatur\Helpers\FileExt::info($file)['nameWithoutExtension'],0,19) }}</span>
                                    {{ date('d/m/Y', strtotime(substr(\Ajifatur\Helpers\FileExt::info($file)['nameWithoutExtension'],0,10))) }}
                                    <br>
                                    <small class="text-muted">{{ str_replace('-', ':', substr(\Ajifatur\Helpers\FileExt::info($file)['nameWithoutExtension'],11,5)) }} WIB</small>
                                </td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.presensi.delete') }}" class="btn btn-sm btn-danger btn-delete" data-id="{{ \Ajifatur\Helpers\FileExt::info($file)['nameWithoutExtension'].'.txt' }}" data-bs-toggle="tooltip" title="Hapus"><i class="bi-trash"></i></a>
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

<form class="form-delete d-none" method="post" action="{{ route('admin.presensi.delete') }}">
    @csrf
    <input type="hidden" name="id">
</form>

<form class="form-delete-bulk d-none" method="post" action="{{ route('admin.presensi.delete-bulk') }}">
    @csrf
    <input type="hidden" name="ids">
</form>

<div class="modal fade" id="modal-import" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import File</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.presensi.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="file" name="file" class="form-control form-control-sm {{ $errors->has('file') ? 'border-danger' : '' }}" accept=".txt">
                    <div class="small text-muted">File harus berekstensi .txt</div>
                    @if($errors->has('file'))
                    <div class="small text-danger">{{ $errors->first('file') }}</div>
                    @endif
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
        buttons: true,
        deleteBulk: true
    });

    // Button Delete
    Spandiv.ButtonDelete(".btn-delete", ".form-delete");

    // Button Delete Bulk
    Spandiv.ButtonDeleteBulk(".btn-delete-bulk", ".form-delete-bulk");

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