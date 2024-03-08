@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Tunjangan Profesi')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Tunjangan Profesi</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="jenis" class="form-select form-select-sm">
                            <option value="0">Semua Jenis</option>
                            @foreach($jenis_tunjangan as $j)
                            <option value="{{ $j->id }}" {{ Request::query('jenis') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
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
                            @for($y=(date('n') == 12 ? date('Y')+1 : date('Y')); $y>=2023; $y--)
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
                                <th>Angkatan</th>
                                <th>Jenis</th>
                                <th>Pegawai</th>
                                <th class="notexport">Pegawai Non Aktif</th>
                                <th>Tunjangan</th>
                                <th>PPh Pasal 21</th>
                                <th>Diterimakan</th>
                                <th class="notexport" width="50">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $d)
                                @if($d['pegawai'] > 0)
                                <tr>
                                    <td>{{ $d['angkatan'] }}</td>
                                    <td>{{ $d['jenis'] }}</td>
                                    <td align="right">{{ number_format($d['pegawai']) }}</td>
                                    <td align="right">
                                        @if(count($d['pegawai_non_aktif']) > 0)
                                            <a href="#" class="btn-pegawai-non-aktif text-danger" data-id="{{ $d['id'] }}" data-nama="{{ implode(' - ', $d['pegawai_non_aktif']) }}">{{ count($d['pegawai_non_aktif']) }}</a>
                                        @else
                                            {{ count($d['pegawai_non_aktif']) }}
                                        @endif
                                    </td>
                                    <td align="right">{{ number_format($d['tunjangan']) }}</td>
                                    <td align="right">{{ number_format($d['pph']) }}</td>
                                    <td align="right">{{ number_format($d['diterimakan']) }}</td>
                                    <td align="center">
                                        <div class="btn-group">
                                            @if($d['id'] != '')
                                                <a href="{{ route('admin.tunjangan-profesi.print.single', ['id' => $d['id'], 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Cetak PDF" target="_blank"><i class="bi-file-pdf"></i></a>
                                                <a href="{{ route('admin.tunjangan-profesi.csv.single', ['id' => $d['id'], 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download CSV"><i class="bi-download"></i></a>
                                                <a href="{{ route('admin.tunjangan-profesi.print.sptjm', ['angkatan' => $d['id'], 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Cetak PDF SPTJM" target="_blank"><i class="bi-file-pdf"></i></a>
                                            @else
                                                <a href="{{ route('admin.tunjangan-profesi.print.batch', ['id' => 4, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Cetak PDF" target="_blank"><i class="bi-file-pdf"></i></a>
                                                <a href="{{ route('admin.tunjangan-profesi.csv.non-pns', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download CSV"><i class="bi-download"></i></a>
                                            <a href="{{ route('admin.tunjangan-profesi.print.sptjm', ['jenis' => 4, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Cetak PDF SPTJM" target="_blank"><i class="bi-file-pdf"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center" colspan="2">Total</td>
                                <td align="right">{{ number_format($total['pegawai']) }}</td>
                                <td align="right">{{ number_format($total['pegawai_non_aktif']) }}</td>
                                <td align="right">{{ number_format($total['tunjangan']) }}</td>
                                <td align="right">{{ number_format($total['pph']) }}</td>
                                <td align="right">{{ number_format($total['diterimakan']) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.tunjangan-profesi.export', ['jenis' => Request::query('jenis'), 'bulan' => $bulan, 'tahun' => $tahun, 'kekurangan' => 2]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel"><i class="bi-file-excel"></i></a>
                                        @if(in_array(Request::query('jenis'), [1,2,3]))
                                        <a href="{{ route('admin.tunjangan-profesi.csv.batch', ['id' => Request::query('jenis'), 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Download CSV"><i class="bi-download"></i></a>
                                        @endif
                                        @if(in_array(Request::query('jenis'), [1,2,3,4]))
                                            @if(in_array(Request::query('jenis'), [1,2,3]))
										    <a href="{{ route('admin.tunjangan-profesi.print.batch', ['id' => Request::query('jenis'), 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Cetak PDF Batch" target="_blank"><i class="bi-file-pdf"></i></a>
                                            @endif
										<a href="{{ route('admin.tunjangan-profesi.print.sptjm', ['jenis' => Request::query('jenis'), 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Cetak PDF SPTJM" target="_blank"><i class="bi-file-pdf"></i></a>
										@endif
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

<div class="modal fade" id="modal-pegawai-non-aktif" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pegawai Non Aktif</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-danger" type="button" data-bs-dismiss="modal">Tutup</button>
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
        pageLength: -1,
        fixedHeader: true,
        buttons: true,
    });

    // Button Pegawai Non Aktif
    $(document).on("click", ".btn-pegawai-non-aktif", function(e) {
        e.preventDefault();
        var nama = $(this).data("nama");
        var nama = nama.split(" - ");
        var html = '<ol class="mb-0">';
        for(i=0; i<nama.length; i++) {
            html += '<li>' + nama[i] + '</li>';
        }
        html += '</ol>';
        $("#modal-pegawai-non-aktif .modal-body p").html(html);
        Spandiv.Modal("#modal-pegawai-non-aktif").show();
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection