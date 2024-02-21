@extends('faturhelper::layouts/admin/main')

@section('title', 'Gaji Pokok')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Gaji Pokok</h1>
    <div class="btn-group">
        <a href="{{ route('admin.gaji-pokok.export', ['sk' => $sk_id]) }}" class="btn btn-sm btn-primary"><i class="bi-file-excel me-1"></i> Export</a>
    </div>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-header d-sm-flex justify-content-end align-items-center">
                <select name="sk" class="form-select form-select-sm">
                    <option value="" disabled>--Pilih SK--</option>
                    @foreach($sk as $s)
                    <option value="{{ $s->id }}" {{ $s->id == $sk_id ? 'selected' : '' }}>{{ $s->nama }} tgl {{ date('d/m/Y', strtotime($s->tanggal)) }}</option>
                    @endforeach
                </select>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">MKG</th>
                                @foreach($golru as $g)
                                <th width="80">{{ $g->nama }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for($i=0;$i<=33;$i++)
                                <tr>
                                    <td align="center"><b>{{ $i }}</b></td>
                                    @foreach($golru as $g)
                                        <?php $gaji_pokok = \App\Models\GajiPokok::where('sk_id','=',$sk_id)->where('golru_id','=',$g->id)->where('mkg','=',($i < 10 ? '0'.$i : $i))->first(); ?>
                                        <td align="center">{{ $gaji_pokok ? number_format($gaji_pokok->gaji_pokok) : '' }}</td>
                                    @endforeach
                                </tr>
                            @endfor
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
    // Select2
    Spandiv.Select2("select[name=sk]");

    // Change the select
    $(document).on("change", ".card-header select", function() {
        var sk = $("select[name=sk]").val();
        window.location.href = Spandiv.URL("{{ route('admin.gaji-pokok.index') }}", {sk: sk});
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr th {text-align: center;}
    #datatable tr td {vertical-align: top;}
</style>

@endsection
