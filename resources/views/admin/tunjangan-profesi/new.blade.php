@extends('faturhelper::layouts/admin/main')

@section('title', 'Profesor / Guru Besar Baru')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Profesor / Guru Besar Baru</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="alert alert-warning fade show" role="alert">
                    <div class="alert-message">
                        <div class="fw-bold"><i class="bi-info-circle-fill me-1"></i> Info</div>
                        Berikut adalah Profesor / Guru Besar baru yang belum mendapatkan Tunjangan Kehormatan Profesor.
                    </div>
                </div>
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
                            @foreach($new as $n)
                            <tr>
								<td>{{ $n['pegawai']->nip }}</td>
                                <td>{{ title_name($n['pegawai']->nama, $n['pegawai']->gelar_depan, $n['pegawai']->gelar_belakang) }}</td>
                                <td>{{ $n['unit']->nama }}</td>
                                <td>{{ $n['angkatan']->nama }}</td>
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
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
