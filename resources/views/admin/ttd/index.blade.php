@extends('faturhelper::layouts/admin/main')

@section('title', 'Pejabat Penandatangan')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Pejabat Penandatangan</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama / NIP</th>
                                <th>Jabatan</th>
                                <th width="80">Tanggal Mulai</th>
                                <th width="80">Tanggal Selesai</th>
                                <th width="80">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ttd as $t)
                            <tr>
                                <td>{{ strtoupper($t->pegawai->nama) }}<br>{{ $t->pegawai->nip }}</td>
                                <td>{{ $t->nama }}<br>({{ $t->kode }})</td>
                                <td>
                                    <span class="d-none">{{ $t->tanggal_mulai }}</span>
                                    {{ date('d/m/Y', strtotime($t->tanggal_mulai)) }}
                                </td>
                                <td>
                                    <span class="d-none">{{ $t->tanggal_selesai }}</span>
                                    {{ date('d/m/Y', strtotime($t->tanggal_selesai)) }}
                                </td>
                                <td><span class="{{ $t->status == 'Aktif' ? 'text-success' : 'text-danger' }}">{{ $t->status }}</span></td>
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
        pageLength: -1,
        orderAll: true
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
