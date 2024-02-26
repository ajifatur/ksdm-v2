@extends('faturhelper::layouts/admin/main')

@section('title', 'List Mutasi Sync')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">List Mutasi Sync</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered mt-3" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Jenis / Deskripsi</th>
                                <th>Status Kepeg.</th>
                                <th>Golru</th>
                                <th>MKG</th>
                                <th>Jabatan</th>
                                <th>Unit</th>
                                <th>TMT</th>
                                <th>Diproses</th>
                                <th>Proses Remun</th>
                                <th>Proses Serdos</th>
                                <th>Proses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mutasi as $key=>$m)
                                <tr>
                                    <td>'{{ nip_baru($m->pegawai) }}</td>
                                    <td>{{ $m->pegawai->nama }}</td>
                                    <td>
                                        {{ $m->jenis->nama }}
                                        @if($m->kolektif == 1)
                                            <span class="badge bg-info">Kolektif</span>
                                        @endif
                                    </td>
                                    <td>{{ $m->status_kepegawaian ? $m->status_kepegawaian->nama : '-' }}</td>
                                    <td>{{ $m->golru ? $m->golru->nama : '-' }}</td>
                                    <td>{{ $m->gaji_pokok ? $m->gaji_pokok->nama : '-' }}</td>
                                    <td>
                                        @foreach($m->detail()->get() as $key2=>$d)
                                            {{ jabatan($d->jabatan) }}
                                            @if($key2 != count($m->detail()->get())-1)<hr class="my-0">@endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($m->detail()->get() as $key2=>$d)
                                            {{ $d->unit ? $d->unit->nama : '-' }}
                                            @if($key2 != count($m->detail()->get())-1)<hr class="my-0">@endif
                                        @endforeach
                                    </td>
                                    <td>{{ $m->tmt != null ? date('d/m/Y', strtotime($m->tmt)) : '-' }}</td>
                                    @if($m->jenis->remun == 1 && ($m->bulan != 0 || $m->tahun != 0))
                                        <td>{{ $m->tahun }}-{{ $m->bulan < 10 ? '0'.$m->bulan : $m->bulan }}-01</td>
                                    @else
                                        <td>-</td>
                                    @endif
                                    <td>{{ $m->proses_remun }}</td>
                                    <td>{{ $m->proses_serdos }}</td>
                                    <td>{{ $m->proses }}</td>
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
        buttons: true,
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
