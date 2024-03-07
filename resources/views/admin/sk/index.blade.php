@extends('faturhelper::layouts/admin/main')

@section('title', 'SK')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">SK</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-header d-sm-flex justify-content-end align-items-center">
                <select name="jenis" class="form-select form-select-sm">
                    <option value="" disabled>--Pilih Jenis--</option>
                    @foreach($jenis_sk as $j)
                    <option value="{{ $j->id }}" {{ $j->id == $jenis ? 'selected' : '' }}>{{ $j->nama }}</option>
                    @endforeach
                </select>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama / Deskripsi</th>
                                <th width="80">Tanggal SK</th>
                                <th width="80">TMT SK</th>
                                <th width="80">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sk as $s)
                            <tr>
                                <td>
                                    {{ $s->nama }}
                                    <br>
                                    <span class="text-muted">{{ $s->deskripsi }}</span>
                                </td>
                                <td>
                                    <span class="d-none">{{ $s->tanggal }}</span>
                                    {{ date('d/m/Y', strtotime($s->tanggal)) }}
                                </td>
                                <td>
                                    <span class="d-none">{{ $s->tmt }}</span>
                                    {{ date('d/m/Y', strtotime($s->tmt)) }}
                                </td>
                                <td><span class="{{ $s->status == 1 ? 'text-success' : 'text-danger' }}">{{ $s->status == 1 ? 'Aktif' : 'Tidak Aktif' }}</span></td>
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
        orderAll: true
    });

    // Select2
    Spandiv.Select2("select[name=jenis]");
    
    // Change the select
    $(document).on("change", ".card-header select", function() {
		var jenis = $("select[name=jenis]").val();
        window.location.href = Spandiv.URL("{{ route('admin.sk.index') }}", {jenis: jenis});
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
