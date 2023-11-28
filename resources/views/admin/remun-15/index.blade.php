@extends('faturhelper::layouts/admin/main')

@section('title', 'Remun ke-15')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Remun ke-15</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="unit" class="form-select form-select-sm">
                            <option value="0" disabled selected>--Pilih Unit--</option>
                            @foreach($unit as $u)
                            <option value="{{ $u->id }}" {{ Request::query('unit') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="0" disabled>--Pilih Tahun--</option>
                            @for($y=date('Y'); $y>=2023; $y--)
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
                @if((Request::query('unit') != null && Request::query('unit') != 0))
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama / NIP</th>
                                <th>Status</th>
                                <th>Jenis</th>
                                <th>Gol</th>
                                <th>Jabatan / Sub Jabatan</th>
                                <th width="40">Layer</th>
                                <th width="40">Grade</th>
                                <th width="70">Dibayarkan</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($remun_15 as $r)
                            <tr>
                                <td>{{ strtoupper($r->pegawai->nama) }}<br>{{ $r->pegawai->nip }}</td>
                                <td>{{ $r->pegawai->status_kepegawaian->nama }}</td>
                                <td>
                                    @if($r->kategori == 1) DOSEN
                                    @elseif($r->kategori == 2) TENDIK
                                    @elseif($r->kategori == 3) DT
                                    @endif
                                </td>
                                <td align="center">{{ $r->golongan ? $r->golongan->nama : '-' }}</td>
                                <td>
                                    @if($r->jabatan)
                                        {{ $r->jabatan->nama }}
                                        <br>
                                        {{ $r->jabatan->sub != '-' ? '('.$r->jabatan->sub.')' : '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td align="center">{{ $r->layer->nama }}</td>
                                <td align="center">{{ $r->jabatan_dasar->grade }}</td>
                                <td align="right">{{ number_format($r->remun_insentif) }}</td>
                                <td>{{ $r->keterangan }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="7" align="center">Total</td>
                                <td align="right"><b>{{ number_format($remun_15->sum('remun_insentif')) }}</b></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-warning mb-0" role="alert">
                    <div class="alert-message">Silahkan pilih unit terlebih dahulu.</div>
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
