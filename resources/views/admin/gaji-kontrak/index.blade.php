@extends('faturhelper::layouts/admin/main')

@section('title', 'List '.$jenis->nama.' Pegawai Tidak Tetap')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">List {{ $jenis->nama }} Pegawai Tidak Tetap</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <input type="hidden" name="jenis" value="{{ $jenis->id }}">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="kategori" class="form-select form-select-sm">
                            <option value="0">--Pilih Kategori--</option>
                            @foreach($kategori_kontrak as $k)
                            <option value="{{ $k->id }}" {{ Request::query('kategori') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
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
                                <th rowspan="2" width="5">No</th>
                                <th rowspan="2">NIP / NPU</th>
                                <th rowspan="2">Nama</th>
                                <th rowspan="2">Unit</th>
                                <th colspan="{{ $kategori && $kategori->kategori == 1 ? 5 : 4 }}">Penghasilan</th>
                                <th colspan="2">Potongan</th>
                                <th rowspan="2" width="80">Gaji Bersih</th>
                            </tr>
                            <tr>
                                <th width="80">Gaji Pokok</th>
                                @if($kategori && $kategori->kategori == 1)
                                <th width="80">Tunj. Dosen NIDK</th>
                                @endif
                                <th width="80">Tunj. Lainnya</th>
                                <th width="80">Tunj. BPJS Kes. (4%),<br>Tunj. BPJS Ket.</th>
                                <th width="80">Jumlah Penghasilan Kotor</th>
                                <th width="80">Iuran BPJS Kes. (1%),<br>Iuran BPJS Ket. (3%)</th>
                                <th width="80">Jumlah BPJS Kes.,<br>Jumlah BPJS Ket.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gaji as $key=>$g)
                            <tr>
                                <td align="right">{{ ($key+1) }}</td>
                                <td><a href="{{ route('admin.pegawai.detail', ['id' => $g->pegawai->id]) }}">'{{ nip_baru($g->pegawai) }}</a></td>
                                <td>{{ strtoupper($g->pegawai->nama) }}</td>
                                <td>{{ $g->unit ? $g->unit->nama : ($g->kategori->nama == 'Tendik Labschool' ? 'LP2M' : '-') }}</td>
                                <td align="right">{{ number_format($g->gjpokok) }}</td>
                                @if($kategori && $kategori->kategori == 1)
                                <td align="right">{{ number_format($g->tjdosen) }}</td>
                                @endif
                                <td align="right">{{ number_format($g->tjlain) }}</td>
                                <td align="right">{{ number_format($g->tjbpjskes4) }}<br>{{ number_format($g->tjbpjsket) }}</td>
                                <td align="right">{{ number_format($g->kotor) }}</td>
                                <td align="right">{{ number_format($g->iuranbpjskes1) }}<br>{{ number_format($g->iuranbpjsket3) }}</td>
                                <td align="right">{{ number_format($g->jmlbpjskes) }}<br>{{ number_format($g->jmlbpjsket) }}</td>
                                <td align="right">{{ number_format($g->bersih) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="4" align="center">Total</td>
                                <td align="right" valign="top">{{ number_format($gaji->sum('gjpokok')) }}</td>
                                @if($kategori && $kategori->kategori == 1)
                                <td align="right" valign="top">{{ number_format($gaji->sum('tjdosen')) }}</td>
                                @endif
                                <td align="right" valign="top">{{ number_format($gaji->sum('tjlain')) }}</td>
                                <td align="right" valign="top">{{ number_format($gaji->sum('tjbpjskes4')) }}<br>{{ number_format($gaji->sum('tjbpjsket')) }}</td>
                                <td align="right" valign="top">{{ number_format($gaji->sum('kotor')) }}</td>
                                <td align="right" valign="top">{{ number_format($gaji->sum('iuranbpjskes1')) }}<br>{{ number_format($gaji->sum('iuranbpjsket3')) }}</td>
                                <td align="right" valign="top">{{ number_format($gaji->sum('jmlbpjskes')) }}<br>{{ number_format($gaji->sum('jmlbpjsket')) }}</td>
                                <td align="right" valign="top">{{ number_format($gaji->sum('bersih')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-warning mb-0" role="alert">
                    <div class="alert-message">Silahkan pilih kategori terlebih dahulu.</div>
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
    #datatable tr th {text-align: center;}
    #datatable tr td {vertical-align: top;}
</style>

@endsection