@extends('faturhelper::layouts/admin/main')

@section('title', 'Referensi Remun')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Referensi Remun</h1>
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
                                <th rowspan="2">Jabatan Dasar</th>
                                <th rowspan="2" width="40">Grade</th>
                                <th rowspan="2" width="60">Nilai Jabatan</th>
                                <th rowspan="2" width="60">Koef. Jabatan</th>
                                <th rowspan="2" width="60">Poin Indeks Rp.</th>
                                <th colspan="3">Layer 1</th>
                                <th colspan="3">Layer 2</th>
                                <th colspan="3">Layer 3</th>
                            </tr>
                            <tr>
                                <th width="70">Remun Standar</th>
                                <th width="70">Remun Gaji</th>
                                <th width="70">Remun Insentif</th>
                                <th width="70">Remun Standar</th>
                                <th width="70">Remun Gaji</th>
                                <th width="70">Remun Insentif</th>
                                <th width="70">Remun Standar</th>
                                <th width="70">Remun Gaji</th>
                                <th width="70">Remun Insentif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referensi as $r)
                            <tr>
                                <td>{{ $r->jabatan_dasar->nama }}</td>
                                <td align="center">{{ $r->jabatan_dasar->grade }}</td>
                                <td align="right">{{ number_format($r->jabatan_dasar->nilai) }}</td>
                                <td align="right">{{ number_format($r->jabatan_dasar->koefisien,2) }}</td>
                                <td align="right">{{ number_format($r->jabatan_dasar->pir) }}</td>
								<td align="right">{{ number_format($r->layer_1->remun_standar) }}</td>
								<td align="right">{{ number_format($r->layer_1->remun_gaji) }}</td>
								<td align="right">{{ number_format($r->layer_1->remun_insentif) }}</td>
								<td align="right">{{ number_format($r->layer_2->remun_standar) }}</td>
								<td align="right">{{ number_format($r->layer_2->remun_gaji) }}</td>
								<td align="right">{{ number_format($r->layer_2->remun_insentif) }}</td>
								<td align="right">{{ number_format($r->layer_3->remun_standar) }}</td>
								<td align="right">{{ number_format($r->layer_3->remun_gaji) }}</td>
								<td align="right">{{ number_format($r->layer_3->remun_insentif) }}</td>
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
    Spandiv.Select2("select[name=sk]");
    
    // Change the select
    $(document).on("change", ".card-header select", function() {
		var sk = $("select[name=sk]").val();
        window.location.href = Spandiv.URL("{{ route('admin.referensi.index') }}", {sk: sk});
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
