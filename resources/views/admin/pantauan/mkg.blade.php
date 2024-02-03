@extends('faturhelper::layouts/admin/main')

@section('title', 'Pantauan Masa Kerja Golongan '.($tipe == 1 ? 'PNS' : 'Pegawai Tetap Non ASN'))

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Pantauan Masa Kerja Golongan {{ $tipe == 1 ? 'PNS' : 'Pegawai Tetap Non ASN' }}</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="alert alert-warning fade show" role="alert">
                    <div class="alert-message">
                        <div class="fw-bold"><i class="bi-info-circle-fill me-1"></i> Info</div>
                        Masa Kerja Golongan dihitung sampai tanggal <strong>{{ \Ajifatur\Helpers\DateTimeExt::full($tanggal) }}</strong>.
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th width="80">Jenis</th>
                                <th width="100">TMT Golongan</th>
                                <th width="80">MKG Tahun</th>
                                <th width="80">MKG Bulan</th>
                                <th>Mutasi Terakhir</th>
                                <th width="100">Cek TMT Golongan</th>
                                <th width="30">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $p)
                            <tr>
                                <td><a href="{{ route('admin.pegawai.detail', ['id' => $p->id]) }}">`{{ $p->nip }}</a></td>
                                <td>{{ strtoupper($p->nama) }}</td>
                                <td>{{ $p->jenis == 1 ? 'Dosen' : 'Tendik' }}</td>
                                <td>
                                    <span class="d-none">{{ $p->tmt_golongan }}</span>
                                    {{ $p->tmt_golongan != null ? date('d/m/Y', strtotime($p->tmt_golongan)) : '-' }}
                                </td>
                                <td>{{ $p->tmt_golongan != null ? $p->mkg_tahun : '-' }}</td>
                                <td>{{ $p->tmt_golongan != null ? $p->mkg_bulan : '-' }}</td>
                                <td>
                                    @if($p->mutasi_terakhir)
                                        {{ $p->mutasi_terakhir->jenis->nama }} {{ $p->mutasi_terakhir ? $p->mutasi_terakhir->golru->nama : '' }}
                                        <br>
                                        {{ $p->mutasi_terakhir->perubahan ? '('.$p->mutasi_terakhir->perubahan->mk_tahun.' tahun '.$p->mutasi_terakhir->perubahan->mk_bulan.' bulan); TMT: '.date('d/m/Y', strtotime($p->mutasi_terakhir->tmt)) : '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><span class="{{ $p->cek != 'Benar' ? 'text-danger' : 'text-success' }}">{{ $p->cek }}</span></td>
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.pegawai.edit-tmt-golongan', ['id' => $p->id]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit TMT Golongan"><i class="bi-pencil"></i></a>
                                    </div>
                                </td>
                            </tr>
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
        fixedHeader: true,
        buttons: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection