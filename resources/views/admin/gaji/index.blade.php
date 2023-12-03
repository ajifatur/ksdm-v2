@extends('faturhelper::layouts/admin/main')

@section('title', $jenis->nama.' PNS')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">{{ $jenis->nama }} PNS</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <input type="hidden" name="jenis" value="{{ $jenis->id }}">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="id" class="form-select form-select-sm">
                            <option value="0">--Pilih Anak Satker--</option>
                            @foreach($anak_satker as $a)
                            <option value="{{ $a->id }}" {{ Request::query('id') == $a->id ? 'selected' : '' }}>{{ $a->kode }} - {{ $a->nama }}</option>
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
                                <th rowspan="2">Nama / NIP</th>
                                <th rowspan="2">Jenis</th>
                                <th colspan="4">Penghasilan</th>
                                <th colspan="3">Potongan</th>
                                <th rowspan="2" width="80">Gaji Bersih</th>
                            </tr>
                            <tr>
                                <th width="80">Gaji Pokok,<br>Tunj. Istri,<br>Tunj. Anak</th>
                                <th width="80">Tunj. Fungsional,<br>Tunj. Struktural,<br>Tunj. Umum</th>
                                <th width="80">Tunj. Beras,<br>Tunj. Kh. Pajak,<br>Pembulatan</th>
                                <th width="80">Jumlah Penghasilan Kotor</th>
                                <th width="80">IWP,<br>BPJS,<br>BPJS2</th>
                                <th width="80">Pajak Penghasilan</th>
                                <th width="80">Jumlah Potongan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gaji as $key=>$g)
                            <tr>
                                <td align="right">{{ ($key+1) }}</td>
                                <td>{{ strtoupper($g->pegawai->nama) }}<br>{{ $g->pegawai->nip }}</td>
                                <td>{{ $g->pegawai->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td align="right">{{ number_format($g->gjpokok) }}<br>{{ number_format($g->tjistri) }}<br>{{ number_format($g->tjanak) }}</td>
                                <td align="right">{{ number_format($g->tjfungs) }}<br>{{ number_format($g->tjstruk) }}<br>{{ number_format($g->tjupns) }}</td>
                                <td align="right">{{ number_format($g->tjberas) }}<br>{{ number_format($g->tjpph) }}<br>{{ number_format($g->pembul) }}</td>
                                <td align="right">{{ number_format($g->nominal) }}</td>
                                <td align="right">{{ number_format($g->potpfk10) }}<br>{{ number_format($g->bpjs) }}<br>{{ number_format($g->bpjs2) }}</td>
                                <td align="right">{{ number_format($g->potpph) }}</td>
                                <td align="right">{{ number_format($g->potongan) }}</td>
                                <td align="right">{{ number_format($g->nominal - $g->potongan) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="6" align="center">Total</td>
                                <td align="right">{{ number_format($gaji->sum('nominal')) }}</td>
                                <td colspan="2"></td>
                                <td align="right">{{ number_format($gaji->sum('potongan')) }}</td>
                                <td align="right">{{ number_format($gaji->sum('nominal') - $gaji->sum('potongan')) }}</td>
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
    #datatable tr td {vertical-align: top;}
</style>

@endsection