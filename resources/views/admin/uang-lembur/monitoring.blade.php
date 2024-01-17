@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Uang Lembur ASN')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Uang Lembur ASN</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="0" disabled>--Pilih Bulan--</option>
                            @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($m) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
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
                                <th rowspan="2">Anak Satker</th>
                                <th colspan="3">Total</th>
                                <th rowspan="2" width="60">Opsi</th>
                            </tr>
                            <tr>
                                <th width="80">Pegawai</th>
                                <th width="80">Nominal Kotor</th>
                                <th width="80">Nominal Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                            <tr>
                                <td>{{ $d['anak_satker']->kode }} - {{ $d['anak_satker']->nama }}</td>
                                <td align="right">{{ number_format($d['jumlah']) }}</td>
                                <td align="right">{{ number_format($d['kotor']) }}</td>
                                <td align="right">{{ number_format($d['bersih']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.uang-lembur.index', ['id' => $d['anak_satker']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat List"><i class="bi-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total['jumlah']) }}</td>
                                <td align="right">{{ number_format($total['kotor']) }}</td>
                                <td align="right">{{ number_format($total['bersih']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                    </div>
                                </td>
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
        pageLength: -1
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection