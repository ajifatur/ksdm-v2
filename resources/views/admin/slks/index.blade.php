@extends('faturhelper::layouts/admin/main')

@section('title', 'List Satyalancana Karya Satya')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">List Satyalancana Karya Satya</h1>
    <div class="btn-group">
        <a href="{{ route('admin.slks.create') }}" class="btn btn-sm btn-primary"><i class="bi-plus me-1"></i> Tambah</a>
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
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th width="100">Periode</th>
                                <th width="100">No. Keppres</th>
                                <th width="80">Tanggal Keppres</th>
                                <th width="100">No. Kep. Rektor</th>
                                <th width="80">Tanggal Kep. Rektor</th>
                                <th>XXX Tahun</th>
                                <th>XX Tahun</th>
                                <th>X Tahun</th>
                                <th width="40">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($slks as $key=>$s)
                            <tr>
                                <td>{{ $s->periode }}</td>
                                <td>{{ $s->no_keppres }}</td>
                                <td>{{ date('d/m/Y', strtotime($s->tanggal_keppres)) }}</td>
                                <td>{{ $s->no_keprektor }}</td>
                                <td>{{ date('d/m/Y', strtotime($s->tanggal_keprektor)) }}</td>
                                <td>
                                    <a href="#" class="btn-pegawai" data-id="{{ $s->id }}" data-tahun="XXX">{{ count($s->detail()->where('tahun','=','XXX')->get()) }} Pegawai</a>
                                    <div class="d-none list-pegawai" data-id="{{ $s->id }}" data-tahun="XXX">
                                    @foreach($s->detail()->where('tahun','=','XXX')->get() as $key=>$d)
                                        {{ strtoupper($d->pegawai->nama) }}
                                        @if(($key+1) < count($s->detail()->where('tahun','=','XXX')->get()))<hr class="my-0">@endif
                                    @endforeach
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="btn-pegawai" data-id="{{ $s->id }}" data-tahun="XX">{{ count($s->detail()->where('tahun','=','XX')->get()) }} Pegawai</a>
                                    <div class="d-none list-pegawai" data-id="{{ $s->id }}" data-tahun="XX">
                                    @foreach($s->detail()->where('tahun','=','XX')->get() as $key=>$d)
                                        {{ strtoupper($d->pegawai->nama) }}
                                        @if(($key+1) < count($s->detail()->where('tahun','=','XX')->get()))<hr class="my-0">@endif
                                    @endforeach
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="btn-pegawai" data-id="{{ $s->id }}" data-tahun="X">{{ count($s->detail()->where('tahun','=','X')->get()) }} Pegawai</a>
                                    <div class="d-none list-pegawai" data-id="{{ $s->id }}" data-tahun="X">
                                    @foreach($s->detail()->where('tahun','=','X')->get() as $key=>$d)
                                        {{ strtoupper($d->pegawai->nama) }}
                                        @if(($key+1) < count($s->detail()->where('tahun','=','X')->get()))<hr class="my-0">@endif
                                    @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="#" class="btn btn-sm btn-secondary btn-add" data-id="{{ $s->id }}" data-tahun="XXX" data-bs-toggle="tooltip" title="Tambah XXX Tahun">XXX</a>
                                        <a href="#" class="btn btn-sm btn-info btn-add" data-id="{{ $s->id }}" data-tahun="XX" data-bs-toggle="tooltip" title="Tambah XX Tahun">XX</a>
                                        <a href="#" class="btn btn-sm btn-warning btn-add" data-id="{{ $s->id }}" data-tahun="X" data-bs-toggle="tooltip" title="Tambah X Tahun">X</a>
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

<div class="modal fade" id="modal-add" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Pegawai</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.slks.add') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tahun<span class="text-danger">*</span></label>
                        <input type="text" name="tahun" class="form-control form-control-sm" readonly>
                    </div>
                    <div>
                        <label>Pegawai:</label>
                        <select name="pegawai[]" class="pegawai form-select form-select-sm" multiple="multiple">
                            <option value="" disabled>--Pilih--</option>
                            @foreach($pegawai as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} - {{ $p->nip }}</option>
                            @endforeach
                        </select>
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
        fixedHeader: true,
    });

    // Select2
    Spandiv.Select2("select.pegawai", {
        dropdownParent: "#modal-add"
    });

    // Button Add
    $(document).on("click", ".btn-add", function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        var tahun = $(this).data("tahun");
        $("#modal-add").find("input[name=id]").val(id);
        $("#modal-add").find("input[name=tahun]").val(tahun);
        Spandiv.Modal("#modal-add").show();
    });

    // Button Add
    $(document).on("click", ".btn-pegawai", function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        var tahun = $(this).data("tahun");
        if($(".list-pegawai[data-id="+id+"][data-tahun="+tahun+"]").hasClass("d-none"))
            $(".list-pegawai[data-id="+id+"][data-tahun="+tahun+"]").removeClass("d-none");
        else
            $(".list-pegawai[data-id="+id+"][data-tahun="+tahun+"]").addClass("d-none");
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
