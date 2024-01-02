@extends('faturhelper::layouts/admin/main')

@section('title', 'No Supplier')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">No Supplier</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Unit</th>
                                <th>Angkatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tunjangan as $t)
                            <tr>
								<td>'{{ $t->pegawai->nip }}</td>
                                <td>{{ $t->pegawai->nama }}</td>
                                <td>{{ $t->unit->nama }}</td>
                                <td>{{ $t->angkatan->nama }}</td>
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
        fixedHeader: true,
        buttons: true,
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
