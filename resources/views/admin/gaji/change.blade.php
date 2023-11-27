@extends('faturhelper::layouts/admin/main')

@section('title', 'Perubahan Gaji Induk')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Perubahan Gaji Induk</h1>
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
                @if(count($gaji_bulan_ini) > 0 && count($gaji_bulan_sebelumnya) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                            <thead class="bg-light">
                                <tr>
                                    <th>NIP</th>
                                    <th>Nama</th>
                                    <th>Jenis</th>
                                    <th>Unit</th>
                                    <th>Keterangan</th>
                                    <th>Sebelum</th>
                                    <th>Sesudah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pegawai_on as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p->id]) }}">'{{ $p->nip }}</a></td>
                                        <td>{{ strtoupper($p->nama) }}</td>
										<td>{{ $p->jenis == 1 ? 'Dosen' : 'Tendik' }}
                                        <td>{{ $p->unit ? $p->unit->nama : '-' }}</td>
                                        <td>Pegawai Masuk</td>
										<td>-</td>
										<td>-</td>
                                    </tr>
                                @endforeach
                                @foreach($pegawai_off as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p->id]) }}">'{{ $p->nip }}</a></td>
                                        <td>{{ strtoupper($p->nama) }}</td>
										<td>{{ $p->jenis == 1 ? 'Dosen' : 'Tendik' }}
                                        <td>{{ $p->unit ? $p->unit->nama : '-' }}</td>
                                        <td>{{ $p->status_kerja->nama }}</td>
										<td>-</td>
										<td>-</td>
                                    </tr>
                                @endforeach
                                @foreach($perubahan_gjpokok as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p['pegawai']->id]) }}">'{{ $p['pegawai']->nip }}</a></td>
                                        <td>{{ strtoupper($p['pegawai']->nama) }}</td>
										<td>{{ $p['pegawai']->jenis == 1 ? 'Dosen' : 'Tendik' }}
                                        <td>{{ $p['pegawai']->unit ? $p['pegawai']->unit->nama : '-' }}</td>
                                        <td>Perubahan Gaji Pokok</td>
										<td>{{ number_format($p['sebelum'],0,',',',') }}</td>
										<td>{{ number_format($p['sesudah'],0,',',',') }}</td>
                                    </tr>
                                @endforeach
                                @foreach($perubahan_tjfungs as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p['pegawai']->id]) }}">'{{ $p['pegawai']->nip }}</a></td>
                                        <td>{{ strtoupper($p['pegawai']->nama) }}</td>
										<td>{{ $p['pegawai']->jenis == 1 ? 'Dosen' : 'Tendik' }}
                                        <td>{{ $p['pegawai']->unit ? $p['pegawai']->unit->nama : '-' }}</td>
                                        <td>Perubahan Tunjangan Fungsional</td>
										<td>{{ number_format($p['sebelum'],0,',',',') }}</td>
										<td>{{ number_format($p['sesudah'],0,',',',') }}</td>
                                    </tr>
                                @endforeach
                                @foreach($perubahan_tjistri as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p['pegawai']->id]) }}">'{{ $p['pegawai']->nip }}</a></td>
                                        <td>{{ strtoupper($p['pegawai']->nama) }}</td>
										<td>{{ $p['pegawai']->jenis == 1 ? 'Dosen' : 'Tendik' }}
                                        <td>{{ $p['pegawai']->unit ? $p['pegawai']->unit->nama : '-' }}</td>
                                        <td>Perubahan Status Kawin (Istri / Suami)</td>
										<td>{{ number_format($p['sebelum'],0,',',',') }}</td>
										<td>{{ number_format($p['sesudah'],0,',',',') }}</td>
                                    </tr>
                                @endforeach
                                @foreach($perubahan_tjanak as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p['pegawai']->id]) }}">'{{ $p['pegawai']->nip }}</a></td>
                                        <td>{{ strtoupper($p['pegawai']->nama) }}</td>
										<td>{{ $p['pegawai']->jenis == 1 ? 'Dosen' : 'Tendik' }}
                                        <td>{{ $p['pegawai']->unit ? $p['pegawai']->unit->nama : '-' }}</td>
                                        <td>Perubahan Status Kawin (Anak)</td>
										<td>{{ number_format($p['sebelum'],0,',',',') }}</td>
										<td>{{ number_format($p['sesudah'],0,',',',') }}</td>
                                    </tr>
                                @endforeach
                                @foreach($perubahan_unit as $p)
                                    <tr>
                                        <td><a href="{{ route('admin.pegawai.detail', ['id' => $p['pegawai']->id]) }}">'{{ $p['pegawai']->nip }}</a></td>
                                        <td>{{ strtoupper($p['pegawai']->nama) }}</td>
										<td>{{ $p['pegawai']->jenis == 1 ? 'Dosen' : 'Tendik' }}
                                        <td>{{ $p['pegawai']->unit ? $p['pegawai']->unit->nama : '-' }}</td>
                                        <td>Perubahan Unit Kerja</td>
										<td>{{ $p['sebelum'] ? $p['sebelum']->nama : '-' }}</td>
										<td>{{ $p['sesudah'] ? $p['sesudah']->nama : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-danger mb-0" role="alert">
                        <div class="alert-message">Belum ada data.</div>
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
