@extends('faturhelper::layouts/admin/main')

@section('title', 'Jabatan')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Jabatan</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-header d-sm-flex justify-content-end align-items-center">
                <div>
                    <select name="jenis" class="form-select form-select-sm">
                        <option value="0" disabled selected>--Pilih Jenis--</option>
                        <option value="1" {{ $jenis == 1 ? 'selected' : '' }}>Jabatan Fungsional</option>
                        <option value="2" {{ $jenis == 2 ? 'selected' : '' }}>Jabatan Struktural</option>
                    </select>
                </div>
                <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                    <select name="visibilitas" class="form-select form-select-sm">
                        <option value="" disabled>--Visibilitas Pegawai--</option>
                        <option value="1" {{ $visibilitas == '1' ? 'selected' : '' }}>Tampilkan</option>
                        <option value="0" {{ $visibilitas == '0' ? 'selected' : '' }}>Sembunyikan</option>
                    </select>
                </div>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Jabatan</th>
                                <th>Jumlah Pegawai</th>
                                @if($visibilitas == 1)
                                <th>Pegawai</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <?php $total = 0; ?>
                            @foreach($grup as $g)
                            <tr>
                                <td>{{ $g->nama }}</td>
                                <td align="right">{{ $jenis == 1 ? number_format(count($g->pegawai_jabfung)) : number_format(count($g->pegawai_jabstruk)) }}</td>
                                @if($visibilitas == 1)
                                <td>
                                    @if($jenis == 1)
                                        @foreach($g->pegawai_jabfung as $p)
                                            {{ $p->nama }}<br>
                                        @endforeach
                                    @elseif($jenis == 2)
                                        @foreach($g->pegawai_jabstruk as $p)
                                            {{ $p->nama }}<br>
                                        @endforeach
                                    @endif
                                </td>
                                @endif
                            </tr>
                            <?php $total += count($jenis == 1 ? $g->pegawai_jabfung : $g->pegawai_jabstruk); ?>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="right">{{ number_format($grup->count()) }}</td>
                                <td align="right">{{ number_format($total) }}</td>
                                @if($visibilitas == 1)
                                <td>Total</td>
                                @endif
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
        fixedHeader: true
    });
    
    // Change the select
    $(document).on("change", ".card-header select", function() {
		var jenis = $("select[name=jenis]").val();
		var visibilitas = $("select[name=visibilitas]").val();
        window.location.href = Spandiv.URL("{{ route('admin.jabatan.index') }}", {jenis: jenis, visibilitas: visibilitas});
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
</style>

@endsection
