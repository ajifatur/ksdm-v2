@extends('faturhelper::layouts/admin/main')

@section('title', 'Rekap Uang Lembur ASN')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Rekap Uang Lembur ASN</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="0" disabled>--Pilih Tahun--</option>
                            @for($y=date('Y'); $y>=2022; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                        <button type="submit" class="btn btn-sm btn-info"><i class="bi-filter me-1"></i> Filter</button>
                    </div>
                </div>
            </form>
            <hr class="my-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Bulan</th>
                                <th>Pegawai</th>
                                <th>Nominal Kotor</th>
                                <th>Nominal Bersih</th>
                                <th width="30">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($uang_lembur as $key=>$u)
                            <tr>
                                <td>{{ $u['bulan'] }}</td>
                                <td align="right">{{ number_format($u['pegawai']) }}</td>
                                <td align="right">{{ number_format($u['nominal_kotor']) }}</td>
                                <td align="right">{{ number_format($u['nominal_bersih']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total_pegawai) }}</td>
                                <td align="right">{{ number_format($total_nominal_kotor) }}</td>
                                <td align="right">{{ number_format($total_nominal_bersih) }}</td>
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
        pageLength: -1,
        orderAll: true,
        fixedHeader: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr th {text-align: center;}
</style>

@endsection