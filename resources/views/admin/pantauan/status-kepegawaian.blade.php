@extends('faturhelper::layouts/admin/main')

@section('title', 'Pantauan Status Kepegawaian')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Pantauan Status Kepegawaian</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Status Kepegawaian</th>
                                <th width="150">Dosen</th>
                                <th width="150">Tendik</th>
                                <th width="150">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $total_dosen = 0;
                                $total_tendik = 0;
                            ?>
                            @foreach($status_kepegawaian as $s)
                            <?php
                                $total_dosen += $s->dosen;
                                $total_tendik += $s->tendik;
                            ?>
                            <tr>
                                <td>{{ $s->nama }}</td>
                                <td align="right">{{ number_format($s->dosen) }}</td>
                                <td align="right">{{ number_format($s->tendik) }}</td>
                                <td align="right">{{ number_format($s->dosen + $s->tendik) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td align="center">Total</td>
                                <td align="right">{{ number_format($total_dosen) }}</td>
                                <td align="right">{{ number_format($total_tendik) }}</td>
                                <td align="right">{{ number_format($total_dosen + $total_tendik) }}</td>
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
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection