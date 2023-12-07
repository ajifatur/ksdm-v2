@extends('faturhelper::layouts/admin/main')

@section('title', 'Remun Gaji')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Remun Gaji</h1>
    <!-- <div class="btn-group">
        <a href="#" class="btn btn-sm btn-primary btn-import"><i class="bi-upload me-1"></i> Import</a>
    </div> -->
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <form method="get" action="">
                <div class="card-header d-sm-flex justify-content-center align-items-center">
                    <div>
                        <select name="kategori" class="form-select form-select-sm">
                            <option value="0">Semua Kategori</option>
                            <option value="1" {{ $kategori == 1 ? 'selected' : '' }}>Dosen</option>
                            <option value="2" {{ $kategori == 2 ? 'selected' : '' }}>Tendik</option>
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
                        <select name="unit" class="form-select form-select-sm">
                            <option value="0">Semua Unit</option>
                            @foreach($unit as $u)
                            <option value="{{ $u->id }}" {{ Request::query('unit') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ms-sm-2 ms-0 mt-2 mt-sm-0">
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
                            @for($y=date('Y'); $y>=2023; $y--)
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
                @if(Session::get('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="alert-message">{{ Session::get('message') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if((Request::query('unit') != null && Request::query('unit') != 0) || $kategori != 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama / NIP</th>
                                <th>Status</th>
                                <th width="40">Gol</th>
                                <th>Jabatan / Sub Jabatan</th>
                                <th width="40">Layer</th>
                                <th width="40">Grade</th>
                                <th width="70">Terbayar</th>
                                <th width="70">Seharusnya</th>
                                <th width="70">Selisih</th>
                                <th width="70">Remun Gaji</th>
                                <th width="70">Dibayarkan</th>
                                @if(Request::query('unit') == null || Request::query('unit') == 0)
                                <th class="bg-dark text-white">Unit</th>
                                @endif
                                <th width="40">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $total_terbayar = 0;
                                $total_seharusnya = 0;
                                $total_selisih = 0;
                                $total_dibayarkan = 0;
                            ?>
                            @foreach($remun_gaji as $key=>$r)
                            <?php
                                $lebih_kurang = \App\Models\LebihKurang::where('pegawai_id','=',$r->pegawai->id)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('selisih','!=',0)->get();
                                $dibayarkan = $r->remun_gaji + $lebih_kurang->sum('selisih');

                                // Sum total
                                $total_terbayar += $lebih_kurang->sum('terbayar');
                                $total_seharusnya += $lebih_kurang->sum('seharusnya');
                                $total_selisih += $lebih_kurang->sum('selisih');
                                $total_dibayarkan += $dibayarkan;
                            ?>
                            <tr>
                                <td>{{ strtoupper($r->pegawai->nama) }}<br>{{ $r->pegawai->nip }}</td>
                                <td>{{ $r->pegawai->status_kepegawaian->nama }}</td>
                                <td align="center">{{ $r->golru ? $r->golru->golongan->nama : '-' }}</td>
                                <td>
                                    @if($r->jabatan)
                                        {{ $r->jabatan->nama }}
                                        <br>
                                        {{ $r->jabatan->sub != '-' ? '('.$r->jabatan->sub.')' : '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td align="center">{{ $r->layer->nama }}</td>
                                <td align="center">{{ $r->jabatan_dasar->grade }}</td>
                                <td align="right">{{ number_format($lebih_kurang->sum('terbayar')) }}</td>
                                <td align="right">{{ number_format($lebih_kurang->sum('seharusnya')) }}</td>
                                <td align="right">{{ number_format($lebih_kurang->sum('selisih')) }}</td>
                                <td align="right">{{ number_format($r->remun_gaji) }}</td>
                                <td align="right">{{ number_format($dibayarkan) }}</td>
                                @if(Request::query('unit') == null || Request::query('unit') == 0)
                                <td>{{ $r->unit->nama }}</td>
                                @endif
                                <td align="center">
                                    <div class="btn-group">
                                        <a href="#" class="btn btn-sm btn-primary btn-add-lebih-kurang" data-pegawai="{{ $r->pegawai_id }}" data-npegawai="{{ strtoupper($r->pegawai->nama) }}" data-jseharusnya="{{ $r->jabatan_id }}" data-bs-toggle="tooltip" title="Tambah Kelebihan/Kekurangan"><i class="bi-plus"></i></a>
                                    </div>
                                </td>
                            </tr>
                                @foreach($lebih_kurang as $lk)
                                <tr bgcolor="#e3e3e3">
                                    <td>
                                        {{ strtoupper($r->pegawai->nama) }}<br>{{ $r->pegawai->nip }}
                                        <br>
                                        <span class="text-primary">({{ \Ajifatur\Helpers\DateTimeExt::month($lk->bulan) }} {{ $lk->tahun }})</span>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td align="right">{{ number_format($lk->terbayar) }}</td>
                                    <td align="right">{{ number_format($lk->seharusnya) }}</td>
                                    <td align="right">{{ number_format($lk->selisih) }}</td>
                                    <td></td>
                                    <td></td>
                                    @if(Request::query('unit') == null || Request::query('unit') == 0)
                                    <td>{{ $r->unit->nama }}</td>
                                    @endif
                                    <td align="center">
                                        <div class="btn-group">
                                            <a href="#" class="btn btn-sm btn-warning btn-edit-lebih-kurang" data-id="{{ $lk->id }}" data-pegawai="{{ $lk->pegawai_id }}" data-npegawai="{{ strtoupper($lk->pegawai->nama) }}" data-bulan="{{ $lk->bulan }}" data-tahun="{{ $lk->tahun }}" data-jterbayar="{{ $lk->jabatan_terbayar_id }}" data-jseharusnya="{{ $lk->jabatan_seharusnya_id }}" data-terbayar="{{ number_format($lk->terbayar) }}" data-seharusnya="{{ number_format($lk->seharusnya) }}" data-selisih="{{ number_format($lk->selisih) }}" data-bs-toggle="tooltip" title="Edit"><i class="bi-pencil"></i></a>
                                            <a href="#" class="btn btn-sm btn-danger btn-delete-lebih-kurang" data-id="{{ $lk->id }}" data-bs-toggle="tooltip" title="Hapus"><i class="bi-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="6">Total</td>
                                <td align="right">{{ number_format($total_terbayar) }}</td>
                                <td align="right">{{ number_format($total_seharusnya) }}</td>
                                <td align="right">{{ number_format($total_selisih) }}</td>
                                <td align="right">{{ number_format($remun_gaji->sum('remun_gaji')) }}</td>
                                <td align="right">{{ number_format($total_dibayarkan) }}</td>
                                @if(Request::query('unit') == null || Request::query('unit') == 0)
                                <td></td>
                                @endif
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-warning mb-0" role="alert">
                    <div class="alert-message">Silahkan pilih kategori atau unit terlebih dahulu.</div>
                </div>
                @endif
            </div>
		</div>
	</div>
</div>

<form class="form-delete-lebih-kurang d-none" method="post" action="{{ route('admin.lebih-kurang.delete') }}">
    @csrf
    <input type="hidden" name="id">
    <input type="hidden" name="bulan_proses" value="{{ $bulan }}">
    <input type="hidden" name="tahun_proses" value="{{ $tahun }}">
    <input type="hidden" name="kategori" value="{{ $kategori }}">
    <input type="hidden" name="unit" value="{{ Request::query('unit') }}">
</form>

<div class="modal fade" id="modal-lebih-kurang" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('admin.lebih-kurang.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id">
                <input type="hidden" name="pegawai">
                <input type="hidden" name="bulan_proses" value="{{ $bulan }}">
                <input type="hidden" name="tahun_proses" value="{{ $tahun }}">
                <input type="hidden" name="kategori" value="{{ $kategori }}">
                <input type="hidden" name="unit" value="{{ Request::query('unit') }}">
                <div class="modal-body">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Pegawai</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="npegawai" class="form-control form-control-sm" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jabatan Terbayar</label>
                        <div class="col-lg-10 col-md-9">
                            <select name="jabatan_terbayar" class="form-select form-select-sm {{ $errors->has('jabatan_terbayar') ? 'border-danger' : '' }}">
                                <option value="0">--Pilih--</option>
                                @foreach($jabatan as $j)
                                <option value="{{ $j->id }}" {{ old('jabatan_terbayar') == $j->id ? 'selected' : '' }}>{{ $j->nama }} {{ $j->sub != '-' ? '('.$j->sub.')' : '' }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('jabatan_terbayar'))
                            <div class="small text-danger">{{ $errors->first('jabatan_terbayar') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jabatan Seharusnya</label>
                        <div class="col-lg-10 col-md-9">
                            <select name="jabatan_seharusnya" class="form-select form-select-sm {{ $errors->has('jabatan_seharusnya') ? 'border-danger' : '' }}">
                                <option value="0">--Pilih--</option>
                                @foreach($jabatan as $j)
                                <option value="{{ $j->id }}" {{ old('jabatan_seharusnya') == $j->id ? 'selected' : '' }}>{{ $j->nama }} {{ $j->sub != '-' ? '('.$j->sub.')' : '' }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('jabatan_seharusnya'))
                            <div class="small text-danger">{{ $errors->first('jabatan_seharusnya') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Bulan<span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="bulan" class="form-select form-select-sm {{ $errors->has('bulan') ? 'border-danger' : '' }}" required>
                                <option value="" disabled selected>--Pilih--</option>
                                @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ old('bulan') == $m ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($m) }}</option>
                                @endfor
                            </select>
                            @if($errors->has('bulan'))
                            <div class="small text-danger">{{ $errors->first('bulan') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tahun<span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="tahun" class="form-select form-select-sm {{ $errors->has('tahun') ? 'border-danger' : '' }}" required>
                                <option value="" disabled selected>--Pilih--</option>
                                @for($y=2023; $y>=2023; $y--)
                                <option value="{{ $y }}" {{ old('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            @if($errors->has('tahun'))
                            <div class="small text-danger">{{ $errors->first('tahun') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Terbayar<span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" name="terbayar" class="form-control form-control-sm {{ $errors->has('terbayar') ? 'border-danger' : '' }}" value="{{ old('terbayar') }}" required>
                            </div>
                            @if($errors->has('terbayar'))
                            <div class="small text-danger">{{ $errors->first('terbayar') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Seharusnya<span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" name="seharusnya" class="form-control form-control-sm {{ $errors->has('seharusnya') ? 'border-danger' : '' }}" value="{{ old('seharusnya') }}" required>
                            </div>
                            @if($errors->has('seharusnya'))
                            <div class="small text-danger">{{ $errors->first('seharusnya') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Selisih</label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" name="selisih" class="form-control form-control-sm {{ $errors->has('selisih') ? 'border-danger' : '' }}" value="{{ old('selisih') }}" disabled>
                            </div>
                            @if($errors->has('selisih'))
                            <div class="small text-danger">{{ $errors->first('selisih') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-primary" type="submit">Submit</button>
                    <button class="btn btn-sm btn-danger" type="button" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')

<script type="text/javascript">
    // DataTable
    Spandiv.DataTable("#datatable", {
        pageLength: -1,
        orderAll: true,
        fixedHeader: true
    });

    // Select2
    var jterbayar = Spandiv.Select2("select[name=jabatan_terbayar]", {
        dropdownParent: "#modal-lebih-kurang"
    });
    Spandiv.Select2("select[name=jabatan_seharusnya]", {
        dropdownParent: "#modal-lebih-kurang"
    });

    // Button Add Lebih Kurang
    $(document).on("click", ".btn-add-lebih-kurang", function(e) {
        e.preventDefault();
        $("#modal-lebih-kurang").find(".modal-title").text("Tambah Kelebihan / Kekurangan");
        $("#modal-lebih-kurang").find("input[name=pegawai]").val($(this).data("pegawai"));
        $("#modal-lebih-kurang").find("input[name=npegawai]").val($(this).data("npegawai"));
        $("#modal-lebih-kurang").find("select[name=jabatan_terbayar]").val(null).trigger("change");
        $("#modal-lebih-kurang").find("select[name=jabatan_seharusnya]").val($(this).data("jseharusnya")).trigger("change");
        $("#modal-lebih-kurang").find("select[name=bulan]").val(null);
        $("#modal-lebih-kurang").find("select[name=tahun]").val(null);
        $("#modal-lebih-kurang").find("input[name=terbayar]").val(0);
        $("#modal-lebih-kurang").find("input[name=seharusnya]").val(0);
        $("#modal-lebih-kurang").find("input[name=selisih]").val(0);
        Spandiv.Modal("#modal-lebih-kurang").show();
    });

    // Button Edit Lebih Kurang
    $(document).on("click", ".btn-edit-lebih-kurang", function(e) {
        e.preventDefault();
        $("#modal-lebih-kurang").find(".modal-title").text("Edit Kelebihan / Kekurangan");
        $("#modal-lebih-kurang").find("input[name=id]").val($(this).data("id"));
        $("#modal-lebih-kurang").find("input[name=pegawai]").val($(this).data("pegawai"));
        $("#modal-lebih-kurang").find("input[name=npegawai]").val($(this).data("npegawai"));
        $("#modal-lebih-kurang").find("select[name=jabatan_terbayar]").val($(this).data("jterbayar")).trigger("change");
        $("#modal-lebih-kurang").find("select[name=jabatan_seharusnya]").val($(this).data("jseharusnya")).trigger("change");
        $("#modal-lebih-kurang").find("select[name=bulan]").val($(this).data("bulan"));
        $("#modal-lebih-kurang").find("select[name=tahun]").val($(this).data("tahun"));
        $("#modal-lebih-kurang").find("input[name=terbayar]").val($(this).data("terbayar"));
        $("#modal-lebih-kurang").find("input[name=seharusnya]").val($(this).data("seharusnya"));
        $("#modal-lebih-kurang").find("input[name=selisih]").val($(this).data("selisih"));
        Spandiv.Modal("#modal-lebih-kurang").show();
    });

    // Button Delete
    Spandiv.ButtonDelete(".btn-delete-lebih-kurang", ".form-delete-lebih-kurang");

    // Prevent non-numeric input
    $(document).on("keypress", "input[name=terbayar], input[name=seharusnya]", function(e) {
        e = (e) ? e : window.event;
        var charCode = (e.which) ? e.which : e.keyCode;
        console.log(charCode);
        if(charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    });

    // Keyup terbayar dan seharusnya
    $(document).on("keyup", "input[name=terbayar], input[name=seharusnya]", function(e) {
        e.preventDefault();
        var number = $(this).val().replace(/[^.\d]/g, '');
        number = number != '' ? parseInt(number) : 0;
        $(this).val(number >= 100 ? Spandiv.NumberFormat(number) : number);

        // Count selisih
        var terbayar = $("input[name=terbayar]").val().replace(/[^.\d]/g, '');
        var seharusnya = $("input[name=seharusnya]").val().replace(/[^.\d]/g, '');
        var selisih = seharusnya - terbayar;
        var isNegative = selisih < 0 ? true : false;
        selisih = isNegative ? -1 * selisih : selisih;
        var selisih_text = selisih >= 100 ? Spandiv.NumberFormat(selisih) : selisih;
        $("input[name=selisih]").val(isNegative ? "-" + selisih_text : selisih_text);
    });
</script>

@endsection

@section('css')

<style>
    #datatable tr td {vertical-align: top;}
    div.dt-buttons .dt-button {border: 2px solid #bebebe!important;}
</style>

@endsection
