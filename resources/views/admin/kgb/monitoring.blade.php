@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Mutasi KGB')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Mutasi KGB</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>TMT / Periode</th>
                                <th width="80">Dosen</th>
                                <th width="80">Tendik</th>
                                <th width="80">Jumlah</th>
                                <th width="30">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                            <tr>
                                <td><span class="d-none">{{ $d['tmt'] }}</span>{{ $d['nama'] }}</td>
                                <td align="right">{{ number_format($d['dosen']) }}</td>
                                <td align="right">{{ number_format($d['tendik']) }}</td>
                                <td align="right">{{ number_format($d['total']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.kgb.index', ['tmt' => $d['tmt']]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="List Mutasi KGB"><i class="bi-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
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
        pageLength: -1,
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection