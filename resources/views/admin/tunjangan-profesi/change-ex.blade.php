@extends('faturhelper::layouts/admin/main')

@section('title', 'Perubahan Tunjangan Profesi')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Perubahan Tunjangan Profesi</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th rowspan="2">NIP</th>
                                <th rowspan="2">Nama</th>
                                <th rowspan="2">Unit</th>
                                <th rowspan="2">Jenis</th>
                                <th rowspan="2">Angkatan</th>
                                <th colspan="2">Pangkat</th>
                                <th colspan="2">Gaji Pokok</th>
                                <th colspan="3">Tunjangan Profesi</th>
                            </tr>
                            <tr>
                                <th>Lama</th>
                                <th>Baru</th>
                                <th>Lama</th>
                                <th>Baru</th>
                                <th>Dibayarkan</th>
                                <th>Seharusnya</th>
                                <th>Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $total = 0; ?>
                            @foreach($tunjangan as $t)
                                @if($t->perubahan == true)
                                <?php
                                    $total += $t->selisih;
                                ?>
                                <tr>
                                    <td>{{ $t->pegawai->nip }}</td>
                                    <td>{{ strtoupper($t->pegawai->nama) }}</td>
                                    <td>{{ $t->unit->nama }}</td>
                                    <td>{{ $t->angkatan->jenis->nama }}</td>
                                    <td>{{ $t->angkatan->nama }}</td>
                                    <td>{{ $t->pangkat_lama->nama }}</td>
                                    <td>{{ $t->pangkat_baru ? $t->pangkat_baru->nama : '-' }}</td>
                                    <td align="right">{{ number_format($t->gaji_pokok_lama) }}</td>
                                    <td align="right">{{ number_format($t->gaji_induk->gjpokok) }}</td>
                                    <td align="right">{{ number_format($t->diterimakan) }}</td>
                                    <td align="right">{{ number_format($t->tunjangan_seharusnya) }}</td>
                                    <td align="right">{{ number_format($t->selisih) }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center" colspan="11">Total</td>
                                <td align="right">{{ number_format($total) }}</td>
                            </tr>
                        </tfoot>
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
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
