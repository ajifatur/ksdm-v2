@extends('faturhelper::layouts/admin/main')

@section('title', 'Gaji Pokok')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Gaji Pokok</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
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
                                        <?php $gaji_pokok = \App\Models\GajiPokok::where('sk_id','=',14)->where('golru_id','=',$g->id)->where('mkg','=',($i < 10 ? '0'.$i : $i))->first(); ?>
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
    // DataTable
    // Spandiv.DataTable("#datatable", {
    //     orderAll: true
    // });
</script>

@endsection

@section('css')

<style>
    #datatable tr th {text-align: center;}
    #datatable tr td {vertical-align: top;}
</style>

@endsection
