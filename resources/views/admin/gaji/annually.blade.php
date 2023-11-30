@extends('faturhelper::layouts/admin/main')

@section('title', 'Rekap Tahunan '.($jenis ? $jenis->nama : 'Gaji').' PNS')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Rekap Tahunan {{ $jenis ? $jenis->nama : 'Gaji' }} PNS</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="jenis" class="form-select form-select-sm">
                            <option value="" disabled selected>-- Pilih Jenis--</option>
                            @foreach($jenis_gaji as $j)
                            <option value="{{ $j->id }}" {{ $jenis && $jenis->id == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                        <select name="kategori" class="form-select form-select-sm">
                            <option value="" disabled selected>--Pilih Kategori--</option>
                            <option value="1" {{ Request::query('kategori') == 1 ? 'selected' : '' }}>Dosen</option>
                            <option value="2" {{ Request::query('kategori') == 2 ? 'selected' : '' }}>Tendik</option>
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="0" disabled>--Pilih Tahun--</option>
                            @for($y=2024; $y>=2022; $y--)
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
                @if($gaji != [])
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">Bulan</th>
                                <th rowspan="2" width="70">Pegawai</th>
                                <th colspan="{{ count($kategori_gaji) }}">Jenis</th>
                            </tr>
                            <tr>
                                @foreach($kategori_gaji as $k)
                                <th width="70">{{{ $k }}}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for($i=1; $i<=12; $i++)
                            <tr>
                                <td>{{ \Ajifatur\Helpers\DateTimeExt::month($i) }}</td>
                                <td align="right">{{ number_format($gaji->where('tahun','=',$tahun)->where('bulan','=',($i < 10 ? '0'.$i : $i))->where('jenis','=',Request::query('kategori'))->count()) }}</td>
                                @foreach($kategori_gaji as $k)
                                <td align="right">{{ number_format($gaji->where('tahun','=',$tahun)->where('bulan','=',($i < 10 ? '0'.$i : $i))->where('jenis','=',Request::query('kategori'))->sum($k)) }}</td>
                                @endforeach
                            </tr>
                            @endfor
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="2" align="center">Total</td>
                                @foreach($kategori_gaji as $k)
                                <td align="right">{{ number_format($gaji->where('tahun','=',$tahun)->where('jenis','=',Request::query('kategori'))->sum($k)) }}</td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-warning mb-0" role="alert">
                    <div class="alert-message">Silahkan pilih jenis gaji dan kategori terlebih dahulu.</div>
                </div>
                @endif
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
        fixedHeader: true,
        buttons: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr th {text-align: center;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection