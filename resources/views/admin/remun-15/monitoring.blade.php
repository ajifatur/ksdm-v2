@extends('faturhelper::layouts/admin/main')

@section('title', 'Monitoring Remun ke-15')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Monitoring Remun ke-15</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
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
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">Unit</th>
                                <th colspan="2">Pegawai</th>
                                <th rowspan="2" width="90">Remun ke-15</th>
                                <th rowspan="2" width="90">Potongan</th>
                                <th rowspan="2" width="90">Dibayarkan</th>
                                <th colspan="2">Excel Simkeu</th>
                                <th rowspan="2" width="30">Potongan PDF</th>
                            </tr>
                            <tr>
                                <th width="60">Aktif</th>
                                <th width="60">Pensiun / MD</th>
                                <th width="30">Aktif</th>
                                <th width="30">Pensiun / MD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unit as $u)
                            <tr>
                                <td>{{ $u->nama }}</td>
                                <td align="right">{{ number_format($u->pegawai - count($u->pensiunmd)) }}</td>
                                <td align="right">
                                    @if(count($u->pensiunmd) > 0)
                                        <a href="#" class="btn-pegawai-non-aktif text-danger" data-id="{{ $u->id }}" data-nama="{{ implode(' - ', $u->namapensiunmd) }}">
                                            {{ number_format(count($u->pensiunmd)) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td align="right">{{ number_format($u->remun_insentif) }}</td>
                                <td align="right">{{ number_format(abs($u->potongan)) }}</td>
                                <td align="right">{{ number_format($u->dibayarkan) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        @if($u->nama != 'Sekolah Pascasarjana')
                                        <a href="{{ route('admin.remun-15.export.single', ['kategori' => 1, 'unit' => $u->id, 'status' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                        @endif
                                        <a href="{{ route('admin.remun-15.export.single', ['kategori' => 2, 'unit' => $u->id, 'status' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
                                <td align="center">
                                    @if(count($u->pensiunmd) > 0)
                                        <div class="btn-group">
                                            @if($u->nama != 'Sekolah Pascasarjana')
                                            <a href="{{ route('admin.remun-15.export.single', ['kategori' => 1, 'unit' => $u->id, 'status' => 0, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Download Excel Dosen"><i class="bi-file-excel"></i></a>
                                            @endif
                                            <a href="{{ route('admin.remun-15.export.single', ['kategori' => 2, 'unit' => $u->id, 'status' => 0, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td align="center">
                                    @if($u->potongan != 0)
                                        <div class="btn-group">
                                            @if($u->potonganDosen > 0)
                                            <a href="{{ route('admin.remun-15.print-potongan', ['kategori' => 1, 'unit' => $u->id, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" target="_blank" data-bs-toggle="tooltip" title="Download PDF Dosen"><i class="bi-file-pdf"></i></a>
                                            @endif
                                            @if($u->potonganTendik > 0)
                                            <a href="{{ route('admin.remun-15.print-potongan', ['kategori' => 2, 'unit' => $u->id, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" target="_blank" data-bs-toggle="tooltip" title="Download PDF Tendik"><i class="bi-file-pdf"></i></a>
                                            @endif
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <td>Pusat</td>
                                <td align="right">{{ number_format($pegawai_pusat - count($pensiunmd_pusat)) }}</td>
                                <td align="right">
                                    @if(count($pensiunmd_pusat) > 0)
                                        <a href="#" class="btn-pegawai-non-aktif text-danger" data-id="0" data-nama="{{ implode(' - ', $pegawai_pensiunmd_pusat) }}">
                                            {{ number_format(count($pensiunmd_pusat)) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td align="right">{{ number_format($remun_insentif_pusat) }}</td>
                                <td align="right">{{ number_format(abs($potongan_pusat)) }}</td>
                                <td align="right">{{ number_format($remun_insentif_pusat + $potongan_pusat) }}</td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.remun-15.export.pusat', ['status' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                    </div>
                                </td>
                                <td align="center">
                                    @if(count($pensiunmd_pusat) > 0)
                                        <div class="btn-group">
                                            <a href="{{ route('admin.remun-15.export.pusat', ['status' => 0, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download Excel Tendik"><i class="bi-file-excel"></i></a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td align="center">
                                    @if($potongan_pusat != 0)
                                        <div class="btn-group">
                                            @if($potongan_pegawai_pusat['dosen'] > 0)
                                            <a href="{{ route('admin.remun-15.print-potongan', ['kategori' => 1, 'pusat' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-info" target="_blank" data-bs-toggle="tooltip" title="Download PDF Dosen"><i class="bi-file-pdf"></i></a>
                                            @endif
                                            @if($potongan_pegawai_pusat['tendik'] > 0)
                                            <a href="{{ route('admin.remun-15.print-potongan', ['kategori' => 2, 'pusat' => 1, 'tahun' => $tahun]) }}" class="btn btn-sm btn-warning" target="_blank" data-bs-toggle="tooltip" title="Download PDF Tendik"><i class="bi-file-pdf"></i></a>
                                            @endif
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td>Total</td>
                                <td colspan="2" align="center">{{ number_format($total_pegawai) }}</td>
                                <td align="right">{{ number_format($total_remun_insentif) }}</td>
                                <td align="right">{{ number_format(abs($total_potongan)) }}</td>
                                <td align="right">{{ number_format($total_dibayarkan) }}</td>
                                <td colspan="3" align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.remun-15.export.recap', ['tahun' => $tahun]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Download Excel"><i class="bi-file-excel"></i></a>
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
                <h4 class="modal-title">Pegawai Pensiun / MD</h4>
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