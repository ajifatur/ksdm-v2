@extends('faturhelper::layouts/admin/main')

@section('title', 'List SPKGB')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">List SPKGB</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
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
                            @for($y=(date('Y')+1); $y>=2023; $y--)
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
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama / NIP</th>
                                <th>Jenis</th>
                                <th>Unit</th>
                                <th>Golru</th>
                                <th>Masa Kerja</th>
                                <th>Mutasi Sebelum</th>
								<th width="30">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $peg)
                                @foreach($peg as $p)
                                <tr>
                                    <td>{{ strtoupper($p->nama) }}<br>{{ $p->nip }}</td>
                                    <td>{{ $p->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                    <td>{{ $p->unit ? $p->unit->nama : '-' }}</td>
                                    <td>{{ $p->golru ? $p->golru->nama : '-' }}
                                    <td>
                                        <span class="d-none">{{ $p->tmt_golongan }}</span>
                                        {{ $tahun - date('Y', strtotime($p->tmt_golongan)) }} tahun 0 bulan
                                    </td>
                                    <td>
                                        @if($p->mutasi_sebelum)
                                            {{ $p->mutasi_sebelum->jenis->nama }} {{ $p->mutasi_sebelum ? $p->mutasi_sebelum->golru->nama : '' }}
                                            <br>
                                            {{ $p->mutasi_sebelum->perubahan ? '('.$p->mutasi_sebelum->perubahan->mk_tahun.' tahun '.$p->mutasi_sebelum->perubahan->mk_bulan.' bulan)' : '' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td align="center">
                                        <div class="btn-group">
                                            @if($p->mutasi_spkgb)
                                                <a href="{{ route('admin.kgb.print', ['id' => $p->mutasi_spkgb->spkgb->id]) }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Cetak" target="_blank"><i class="bi-file-pdf"></i></a>
                                            @else
                                                <a href="{{ route('admin.kgb.create', ['id' => $p->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Tambah"><i class="bi-plus"></i></a>
                                            @endif
                                            <!-- <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="bi-pencil"></i></a> -->
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
