@extends('faturhelper::layouts/admin/main')

@section('title', 'Uang Makan '.($jenis == 1 ? 'PNS' : 'PPPK'))

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Uang Makan {{ $jenis == 1 ? 'PNS' : 'PPPK' }}</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <input type="hidden" name="jenis" value="{{ $jenis }}">
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
                @if($uang_makan != [])
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th width="5">No</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                @if($jenis == 2)
                                <th>Unit</th>
                                @endif
                                <th>Jenis</th>
                                <th width="80">Jumlah Hari</th>
                                <th width="80">Nominal Kotor</th>
                                <th width="80">Potongan</th>
                                <th width="80">Nominal Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($uang_makan as $key=>$um)
                            <tr>
                                <td align="right">{{ ($key+1) }}</td>
                                <td><a href="{{ route('admin.pegawai.detail', ['id' => $um->pegawai->id]) }}">'{{ $um->pegawai->nip }}</a></td>
                                <td>{{ strtoupper($um->pegawai->nama) }}</td>
                                @if($jenis == 2)
                                <td>{{ $um->unit->nama }}</td>
                                @endif
                                <td>{{ $um->pegawai->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td align="right">{{ number_format($um->jmlhari) }}</td>
                                <td align="right">{{ number_format($um->kotor) }}</td>
                                <td align="right">{{ number_format($um->potongan) }}</td>
                                <td align="right">{{ number_format($um->bersih) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="{{ $jenis == 2 ? 5 : 4 }}" align="center">Total</td>
                                <td align="right">{{ number_format($uang_makan->sum('jmlhari')) }}</td>
                                <td align="right">{{ number_format($uang_makan->sum('kotor')) }}</td>
                                <td align="right">{{ number_format($uang_makan->sum('potongan')) }}</td>
                                <td align="right">{{ number_format($uang_makan->sum('bersih')) }}</td>
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