@extends('faturhelper::layouts/admin/main')

@section('title', 'Uang Lembur ASN')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Uang Lembur ASN</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
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
                @if($uang_lembur != [])
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th width="5">No</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Unit</th>
                                <th>Jenis</th>
                                <th width="80">Jam Lembur Hari Kerja</th>
                                <th width="80">Jam Lembur Hari Libur</th>
                                <th width="80">Jumlah Hari</th>
                                <th width="80">Total Lembur</th>
                                <th width="80">Total Uang Makan Lembur</th>
                                <th width="80">Nominal Kotor</th>
                                <th width="80">Potongan</th>
                                <th width="80">Nominal Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($uang_lembur as $key=>$ul)
                            <tr>
                                <td align="right">{{ ($key+1) }}</td>
                                <td><a href="{{ route('admin.pegawai.detail', ['id' => $ul->pegawai->id]) }}">'{{ $ul->pegawai->nip }}</a></td>
                                <td>{{ strtoupper($ul->pegawai->nama) }}</td>
                                <td>{{ $ul->unit->nama }}</td>
                                <td>{{ $ul->pegawai->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td align="right">{{ number_format($ul->jamlemburharikerja) }}</td>
                                <td align="right">{{ number_format($ul->jamlemburharilibur) }}</td>
                                <td align="right">{{ number_format($ul->totalhari) }}</td>
                                <td align="right">{{ number_format($ul->totallembur) }}</td>
                                <td align="right">{{ number_format($ul->totalumlembur) }}</td>
                                <td align="right">{{ number_format($ul->kotor) }}</td>
                                <td align="right">{{ number_format($ul->potongan) }}</td>
                                <td align="right">{{ number_format($ul->bersih) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="5" align="center">Total</td>
                                <td align="right">{{ number_format($uang_lembur->sum('jamlemburharikerja')) }}</td>
                                <td align="right">{{ number_format($uang_lembur->sum('jamlemburharilibur')) }}</td>
                                <td align="right">{{ number_format($uang_lembur->sum('totalhari')) }}</td>
                                <td align="right">{{ number_format($uang_lembur->sum('totallembur')) }}</td>
                                <td align="right">{{ number_format($uang_lembur->sum('totalumlembur')) }}</td>
                                <td align="right">{{ number_format($uang_lembur->sum('kotor')) }}</td>
                                <td align="right">{{ number_format($uang_lembur->sum('potongan')) }}</td>
                                <td align="right">{{ number_format($uang_lembur->sum('bersih')) }}</td>
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