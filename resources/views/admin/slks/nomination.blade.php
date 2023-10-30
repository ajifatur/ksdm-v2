@extends('faturhelper::layouts/admin/main')

@section('title', 'Nominasi Penerima Satyalancana Karya Satya')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Nominasi Penerima Satyalancana Karya Satya</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">NIP</th>
                                <th rowspan="2">Nama</th>
                                <th rowspan="2">Unit</th>
                                <th rowspan="2" width="80">TMT CPNS</th>
                                <th rowspan="2" width="80">MK Pada Mei 2024 (Tahun)</th>
                                <th rowspan="2" width="80">MK Pada Ags. 2024 (Tahun)</th>
                                <th colspan="3">Pernah Menerima</th>
                                <th colspan="2">Rekomendasi</th>
                            </tr>
                            <tr>
                                <th width="80">X Tahun</th>
                                <th width="80">XX Tahun</th>
                                <th width="80">XXX Tahun</th>
                                <th width="80">Mei</th>
                                <th width="80">Ags.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $key=>$p)
                                @if($p->rekomendasi_mei != '' || $p->rekomendasi_agustus != '')
                                <tr>
                                    <td><a href="{{ route('admin.pegawai.detail', ['id' => $p->id]) }}">'{{ $p->nip }}</a></td>
                                    <td>{{ title_name($p->nama, $p->gelar_depan, $p->gelar_belakang) }}</td>
                                    <td>{{ $p->unit ? $p->unit->nama : '-' }}</td>
                                    <td>
                                        <span class="d-none">{{ $p->tmt_cpns }}</span>
                                        {{ date('d/m/Y', strtotime($p->tmt_cpns)) }}
                                    </td>
                                    <td align="right">{{ $p->mk_mei }}</td>
                                    <td align="right">{{ $p->mk_agustus }}</td>
                                    <td align="center"><span class="{{ $p->sudah_x ? '' : 'text-danger' }}">{{ $p->sudah_x ? 'Ya' : 'Belum' }}</span></td>
                                    <td align="center"><span class="{{ $p->sudah_xx ? '' : 'text-danger' }}">{{ $p->sudah_xx ? 'Ya' : 'Belum' }}</span></td>
                                    <td align="center"><span class="{{ $p->sudah_xxx ? '' : 'text-danger' }}">{{ $p->sudah_xxx ? 'Ya' : 'Belum' }}</span></td>
                                    <td>{{ $p->rekomendasi_mei }}</td>
                                    <td>{{ $p->rekomendasi_agustus }}</td>
                                </tr>
                                @endif
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
