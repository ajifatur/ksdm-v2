@extends('faturhelper::layouts/admin/main')

@section('title', 'Unit')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Unit</h1>
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
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama</th>
                                <th>Nama Lengkap</th>
                                <th>Keterangan</th>
                                <th>Layer</th>
                                <th>Pimpinan</th>
                                <th width="100">TMT Non Aktif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unit as $u)
                            <tr>
                                <td>{{ $u->nama }}</td>
                                <td>{{ $u->full }}</td>
                                <td>{{ $u->pusat == 1 ? 'Pusat' : '-' }}</td>
                                <td>{{ $u->layer->nama }}</td>
                                <td>{{ $u->pimpinan }}</td>
                                <td>
                                    <span class="d-none">{{ $u->end_date }}</span>
                                    {{ $u->end_date != null ? date('d/m/Y', strtotime($u->end_date)) : '' }}
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

@endsection

@section('js')

<script type="text/javascript">
    // DataTable
    Spandiv.DataTable("#datatable", {
        pageLength: -1,
        orderAll: true,
        fixedHeader: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
