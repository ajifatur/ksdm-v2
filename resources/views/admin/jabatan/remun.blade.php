@extends('faturhelper::layouts/admin/main')

@section('title', 'Jabatan Remun')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Jabatan Remun</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-header d-sm-flex justify-content-end align-items-center">
                <select name="sk" class="form-select form-select-sm">
                    <option value="" disabled>--Pilih SK--</option>
                    @foreach($sk as $s)
                    <option value="{{ $s->id }}" {{ $s->id == $sk_id ? 'selected' : '' }}>{{ $s->nama }} tgl {{ date('d/m/Y', strtotime($s->tanggal)) }}</option>
                    @endforeach
                </select>
            </div>
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
                                <th>Jabatan</th>
                                <th>Sub Nama</th>
                                <th>Jabatan Dasar</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jabatan as $j)
                            <tr>
                                <td>{{ $j->nama }}</td>
                                <td>{{ $j->sub }}</td>
                                <td>{{ $j->jabatan_dasar->nama }}</td>
                                <td>{{ $j->jabatan_dasar->grade }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
		</div>
	</div>
</div>

@endsection

@section('js')

<script type="text/javascript">
    // DataTable
    Spandiv.DataTable("#datatable", {
        orderAll: true,
        buttons: true
    });

    // Select2
    Spandiv.Select2("select[name=sk]");
    
    // Change the select
    $(document).on("change", ".card-header select", function() {
		var sk = $("select[name=sk]").val();
        window.location.href = Spandiv.URL("{{ route('admin.jabatan.remun') }}", {sk: sk});
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
