@extends('faturhelper::layouts/admin/main')

@section('title', 'Tunjangan '.$jenis->nama)

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Tunjangan {{ $jenis->nama }}</h1>
    <!-- <div class="btn-group">
        <a href="#" class="btn btn-sm btn-primary btn-import"><i class="bi-upload me-1"></i> Import</a>
    </div> -->
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <input type="hidden" name="jenis" value="{{ $jenis->id }}">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="angkatan" class="form-select form-select-sm">
                            <option value="0">--Pilih Angkatan--</option>
                            @foreach($angkatan as $a)
                            <option value="{{ $a->id }}" {{ Request::query('angkatan') == $a->id ? 'selected' : '' }}>{{ $a->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
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
                @if(Request::query('angkatan') != null && Request::query('angkatan') != 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Gol</th>
                                <th>Unit</th>
                                <th>Tunjangan</th>
                                <th>PPh Pasal 21</th>
                                <th>Diterimakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tunjangan as $key=>$t)
                            <tr>
                                <td><a href="{{ route('admin.pegawai.detail', ['id' => $t->pegawai->id]) }}">'{{ nip_baru($t->pegawai) }}</a></td>
                                <td>{{ strtoupper($t->pegawai->nama) }}</td>
                                <td align="center">{{ $jenis->id != 4 ? $t->golongan->nama : $t->golongan->id }}</td>
                                <td>{{ $t->unit->nama }}</td>
                                <td align="right">{{ number_format($t->tunjangan) }}</td>
                                <td align="right">{{ number_format($t->pph) }}</td>
                                <td align="right">{{ number_format($t->diterimakan) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center" colspan="3">Total</td>
                                <td align="right">{{ number_format($tunjangan->sum('tunjangan')) }}</td>
                                <td align="right">{{ number_format($tunjangan->sum('pph')) }}</td>
                                <td align="right">{{ number_format($tunjangan->sum('diterimakan')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-warning mb-0" role="alert">
                    <div class="alert-message">Silahkan pilih angkatan terlebih dahulu.</div>
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
