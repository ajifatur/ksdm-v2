@extends('faturhelper::layouts/admin/main')

@section('title', 'Rekap Bulanan '.($jenis ? $jenis->nama : 'Gaji').' PNS')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Rekap Bulanan {{ $jenis ? $jenis->nama : 'Gaji' }} PNS</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="jenis" class="form-select form-select-sm">
                            <option value="0">Semua Jenis</option>
                            @foreach($jenis_gaji as $j)
                            <option value="{{ $j->id }}" {{ $jenis && $jenis->id == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                        <select name="id" class="form-select form-select-sm">
                            <option value="0">--Pilih Anak Satker--</option>
                            @foreach($anak_satker_all as $a)
                            <option value="{{ $a->id }}" {{ $anak_satker && $anak_satker->id == $a->id ? 'selected' : '' }}>{{ $a->kode }} - {{ $a->nama }}</option>
                            @endforeach
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
                @if($anak_satker)
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">Jenis</th>
                                <th colspan="12">Bulan</th>
                                <th rowspan="2" width="80">Total</th>
                            </tr>
                            <tr>
                                @for($i=1; $i<=12; $i++)
                                <th width="70">{{ \Ajifatur\Helpers\DateTimeExt::month($i) }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kategori_gaji as $k)
                            <tr>
                                <td>{{ $k }}</td>
                                @for($i=1; $i<=12; $i++)
                                <td align="right">{{ number_format($gaji->where('tahun','=',$tahun)->where('bulan','=',($i < 10 ? '0'.$i : $i))->sum($k)) }}</td>
                                @endfor
                                <td align="right" class="fw-bold">{{ number_format($gaji->where('tahun','=',$tahun)->sum($k)) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                @for($i=1; $i<=12; $i++)
                                <td align="right">{{ number_format($gaji->where('tahun','=',$tahun)->where('bulan','=',($i < 10 ? '0'.$i : $i))->sum('nominal') - $gaji->where('tahun','=',$tahun)->where('bulan','=',($i < 10 ? '0'.$i : $i))->sum('potongan')) }}</td>
                                @endfor
                                <td align="right">{{ number_format($gaji->where('tahun','=',$tahun)->sum('nominal') - $gaji->where('tahun','=',$tahun)->sum('potongan')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-warning mb-0" role="alert">
                    <div class="alert-message">Silahkan pilih anak satker terlebih dahulu.</div>
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
        fixedHeader: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr th {text-align: center;}
</style>

@endsection