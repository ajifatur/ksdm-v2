@extends('faturhelper::layouts/admin/main')

@section('title', 'Edit Mutasi Pegawai')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Mutasi Pegawai</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.mutasi.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $mutasi->id }}">
                    <input type="hidden" name="pegawai_id" value="{{ $pegawai->id }}">
                    <input type="hidden" name="sk_id" value="{{ $sk ? $sk->id : 0 }}">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nama</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="nama" class="form-control form-control-sm" value="{{ $pegawai->nama }} - {{ $pegawai->npu != null ? $pegawai->npu : $pegawai->nip }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jenis <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="jenis_mutasi" class="form-select form-select-sm {{ $errors->has('jenis_mutasi') ? 'border-danger' : '' }}">
                                <option value="" disabled selected>--Pilih--</option>
                                @foreach($jenis_mutasi as $j)
                                <option value="{{ $j->id }}" data-remun="{{ $j->remun }}" data-serdos="{{ $j->serdos }}" data-perubahan="{{ $j->perubahan }}" {{ $mutasi->jenis_id == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('jenis_mutasi'))
                            <div class="small text-danger">{{ $errors->first('jenis_mutasi') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3 {{ $mutasi->jenis_id == 1 ? '' : 'd-none' }}" id="uraian">
                        <label class="col-lg-2 col-md-3 col-form-label">Uraian <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <textarea name="uraian" class="form-control form-control-sm {{ $errors->has('uraian') ? 'border-danger' : '' }}" rows="3">{{ $mutasi->uraian }}</textarea>
                            @if($errors->has('uraian'))
                            <div class="small text-danger">{{ $errors->first('uraian') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3 {{ $mutasi->jenis_id == 1 ? '' : 'd-none' }}" id="jabatan-unit">
                        <label class="col-lg-2 col-md-3 col-form-label">Jabatan dan Unit <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            @foreach($mutasi->detail as $key=>$d)
                            <div class="lists" data-id="{{ $key }}">
                                <input type="hidden" name="detail_id[]" value="{{ $d->id }}">
                                <input type="hidden" name="jabatan_id[]" value="{{ $d->jabatan_id }}">
                                <input type="hidden" name="unit_id[]" value="{{ $d->unit_id }}">
                                <div class="mb-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" value="{{ $d->jabatan->sub != '-' ? $d->jabatan->sub : $d->jabatan->nama }}" disabled>
                                        <input type="text" class="form-control form-control-sm" value="{{ $d->unit->nama }}" disabled>
                                        <button class="btn btn-outline-primary btn-add" title="Tambah"><i class="bi-plus"></i></button>
                                        <button class="btn btn-outline-warning btn-edit" title="Edit" data-id="{{ $key }}" data-detail="{{ $d->id }}" data-jabatan="{{ $d->jabatan_id }}" data-jabatanremun="{{ $d->jabatan_remun }}" data-unit="{{ $d->unit_id }}"><i class="bi-pencil"></i></button>
                                        @if(count($mutasi->detail) <= 1)
                                            <button class="btn btn-outline-danger btn-delete" data-id="{{ $key }}" title="Hapus" disabled><i class="bi-trash"></i></button>
                                        @else
                                            <button class="btn btn-outline-danger btn-delete" data-id="{{ $key }}" title="Hapus"><i class="bi-trash"></i></button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @if($pegawai->status_kepegawaian->golru == 1)
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Golru <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="golru" class="form-select form-select-sm {{ $errors->has('golru') ? 'border-danger' : '' }}">
                                <option value="" disabled selected>--Pilih--</option>
                                @foreach($golru as $g)
                                <option value="{{ $g->id }}" {{ $mutasi->golru_id == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('golru'))
                            <div class="small text-danger">{{ $errors->first('golru') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Gaji Pokok <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="gaji_pokok" class="form-select form-select-sm {{ $errors->has('gaji_pokok') ? 'border-danger' : '' }}">
                                <option value="" disabled selected>--Pilih--</option>
                                @foreach($gaji_pokok as $gp)
                                <option value="{{ $gp->id }}" {{ $mutasi->gaji_pokok_id == $gp->id ? 'selected' : '' }}>{{ $gp->nama }} - Rp {{ number_format($gp->gaji_pokok) }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('gaji_pokok'))
                            <div class="small text-danger">{{ $errors->first('gaji_pokok') }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="{{ $mutasi->jenis->remun == 0 && $mutasi->jenis->serdos == 0 ? '' : 'd-none' }}" id="perubahan">
                        <div class="row mb-3">
                            <label class="col-lg-2 col-md-3 col-form-label">No. SK <span class="text-danger">*</span></label>
                            <div class="col-lg-10 col-md-9">
                                <input type="text" name="no_sk" class="form-control form-control-sm" value="{{ $mutasi->perubahan ? $mutasi->perubahan->no_sk : '' }}">
                                @if($errors->has('no_sk'))
                                <div class="small text-danger">{{ $errors->first('no_sk') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-lg-2 col-md-3 col-form-label">Tanggal SK <span class="text-danger">*</span></label>
                            <div class="col-lg-10 col-md-9">
                                <div class="input-group">
                                    <input type="text" name="tanggal_sk" class="form-control form-control-sm {{ $errors->has('tanggal_sk') ? 'border-danger' : '' }}" value="{{ $mutasi->perubahan && $mutasi->perubahan->tanggal_sk != null ? date('d/m/Y', strtotime($mutasi->perubahan->tanggal_sk)) : '' }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
                                    <span class="input-group-text"><i class="bi-calendar2"></i></span>
                                </div>
                                @if($errors->has('tanggal_sk'))
                                <div class="small text-danger">{{ $errors->first('tanggal_sk') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-lg-2 col-md-3 col-form-label">Masa Kerja Tahun, Bulan <span class="text-danger">*</span></label>
                            <div class="col-lg-10 col-md-9">
                                <div class="input-group">
                                    <select name="mk_tahun" class="form-select form-select-sm {{ $errors->has('mk_tahun') ? 'border-danger' : '' }}">
                                        <option value="" disabled selected>--Pilih Masa Kerja Tahun--</option>
                                        @for($i=0; $i<=50; $i++)
                                        <option value="{{ $i }}" {{ $mutasi->perubahan && $mutasi->perubahan->mk_tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <span class="input-group-text">Tahun</span>
                                    <select name="mk_bulan" class="form-select form-select-sm {{ $errors->has('mk_bulan') ? 'border-danger' : '' }}">
                                        <option value="" disabled selected>--Pilih Masa Kerja Bulan--</option>
                                        @for($i=0; $i<=11; $i++)
                                        <option value="{{ $i }}" {{ $mutasi->perubahan && $mutasi->perubahan->mk_bulan == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <span class="input-group-text">Bulan</span>
                                </div>
                                @if($errors->has('mk_bulan') || $errors->has('mk_tahun'))
                                <div class="small text-danger">{{ $errors->first('mk_bulan') }}</div>
                                <div class="small text-danger">{{ $errors->first('mk_tahun') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-lg-2 col-md-3 col-form-label">Pejabat SK <span class="text-danger">*</span></label>
                            <div class="col-lg-10 col-md-9">
                                <select name="pejabat" class="form-select form-select-sm {{ $errors->has('pejabat') ? 'border-danger' : '' }}">
                                    <option value="" disabled selected>--Pilih Pejabat--</option>
                                    @foreach($pejabat as $p)
                                    <option value="{{ $p->id }}" {{ $mutasi->perubahan && $mutasi->perubahan->pejabat_id == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('pejabat'))
                                <div class="small text-danger">{{ $errors->first('pejabat') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="{{ $mutasi->jenis->remun == 0 && $mutasi->jenis->serdos == 1 ? '' : 'd-none' }}" id="serdos">
                        <div class="row mb-3">
                            <label class="col-lg-2 col-md-3 col-form-label">Angkatan <span class="text-danger">*</span></label>
                            <div class="col-lg-10 col-md-9">
                                <select name="angkatan" class="form-select form-select-sm {{ $errors->has('angkatan') ? 'border-danger' : '' }}">
                                    <option value="" disabled selected>--Pilih--</option>
                                    @for($i=1; $i<=3; $i++)
                                        <?php $label = ['', 'Kehormatan Profesor', 'Profesi GB', 'Profesi Non GB']; ?>
                                        @foreach($angkatan[$i]['data'] as $a)
                                        <option value="{{ $a->id }}" {{ count($mutasi->detail) > 0 && $mutasi->detail()->first()->angkatan_id == $a->id ? 'selected' : '' }}>{{ $label[$i] }} - {{ $a->nama }}</option>
                                        @endforeach
                                    @endfor
                                </select>
                                @if($errors->has('angkatan'))
                                <div class="small text-danger">{{ $errors->first('angkatan') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-lg-2 col-md-3 col-form-label">Nama Supplier <span class="text-danger">*</span></label>
                            <div class="col-lg-10 col-md-9">
                                <input type="text" name="nama_supplier" class="form-control form-control-sm {{ $errors->has('nama_supplier') ? 'border-danger' : '' }}" value="{{ $pegawai->nama_supplier }}">
                                @if($errors->has('nama_supplier'))
                                <div class="small text-danger">{{ $errors->first('nama_supplier') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-lg-2 col-md-3 col-form-label">Nomor Rekening <span class="text-danger">*</span></label>
                            <div class="col-lg-10 col-md-9">
                                <input type="text" name="nomor_rekening" class="form-control form-control-sm {{ $errors->has('nomor_rekening') ? 'border-danger' : '' }}" value="{{ $pegawai->norek_btn }}">
                                @if($errors->has('nomor_rekening'))
                                <div class="small text-danger">{{ $errors->first('nomor_rekening') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-lg-2 col-md-3 col-form-label">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                            <div class="col-lg-10 col-md-9">
                                <input type="text" name="nama_rekening" class="form-control form-control-sm {{ $errors->has('nama_rekening') ? 'border-danger' : '' }}" value="{{ $pegawai->nama_btn }}">
                                @if($errors->has('nama_rekening'))
                                <div class="small text-danger">{{ $errors->first('nama_rekening') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">TMT <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tmt" class="form-control form-control-sm {{ $errors->has('tmt') ? 'border-danger' : '' }}" value="{{ $mutasi->tmt != null ? date('d/m/Y', strtotime($mutasi->tmt)) : '' }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
                                <span class="input-group-text"><i class="bi-calendar2"></i></span>
                            </div>
                            @if($errors->has('tmt'))
                            <div class="small text-danger">{{ $errors->first('tmt') }}</div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-2 col-md-3"></div>
                        <div class="col-lg-10 col-md-9">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi-save me-1"></i> Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
	</div>
</div>

<div class="modal fade" id="modal-jabatan-unit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Jabatan dan Unit</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
				<input type="hidden" name="key" value="">
				<input type="hidden" name="id" value="">
                <div class="row mb-3">
                    <label class="col-lg-2 col-md-3 col-form-label">Jabatan <span class="text-danger">*</span></label>
                    <div class="col-lg-10 col-md-9">
                        <select name="jabatan" class="form-select form-select-sm {{ $errors->has('jabatan') ? 'border-danger' : '' }}">
                            <option value="" disabled selected>--Pilih--</option>
                            @foreach($jabatan as $j)
                            <option value="{{ $j->id }}">{{ $j->nama }}{{ $j->sub != '-' ? ' - '.$j->sub : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-lg-2 col-md-3 col-form-label">Unit <span class="text-danger">*</span></label>
                    <div class="col-lg-10 col-md-9">
                        <select name="unit" class="form-select form-select-sm {{ $errors->has('unit') ? 'border-danger' : '' }}">
                            <option value="" disabled selected>--Pilih--</option>
                            @foreach($unit as $u)
                            <option value="{{ $u->id }}">{{ $u->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-primary btn-submit" type="button">Simpan</button>
                <button class="btn btn-sm btn-danger" type="button" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

<script>
    // Jenis Mutasi
    Spandiv.Select2("select[name=jenis_mutasi]");
    $(document).on("change", "select[name=jenis_mutasi]", function(e) {
        var remun = $("select[name=jenis_mutasi]").find("option[value=" + $(this).val() + "]").data("remun");
        var serdos = $("select[name=jenis_mutasi]").find("option[value=" + $(this).val() + "]").data("serdos");
        var perubahan = $("select[name=jenis_mutasi]").find("option[value=" + $(this).val() + "]").data("perubahan");
        if($(this).val() == 1) {
            $("#jabatan-unit").removeClass("d-none");
            $("#uraian").removeClass("d-none");
            $("#perubahan").addClass("d-none");
            $("#serdos").addClass("d-none");
        }
        else if(perubahan == 1) {
            $("#jabatan-unit").addClass("d-none");
            $("#uraian").addClass("d-none");
            $("#perubahan").removeClass("d-none");
            $("#serdos").addClass("d-none");
        }
        else if(remun == 0 && serdos == 1 && perubahan == 0) {
            $("#jabatan-unit").addClass("d-none");
            $("#uraian").addClass("d-none");
            $("#perubahan").addClass("d-none");
            $("#serdos").removeClass("d-none");
        }
        else {
            $("#jabatan-unit").addClass("d-none");
            $("#uraian").addClass("d-none");
            $("#perubahan").addClass("d-none");
            $("#serdos").addClass("d-none");
        }
    });

    // Golru
    Spandiv.Select2("select[name=golru]");
    $(document).on("change", "select[name=golru]", function() {
        var golru = $(this).val();
        $.ajax({
            type: "get",
            url: Spandiv.URL("{{ route('api.gaji-pokok.index') }}", {golru: golru}),
            success: function(response) {
                var html = '';
                html += '<option value="" selected">--Pilih--</option>';
                for(i=0; i<response.length; i++) {
                    html += '<option value="' + response[i].id + '">' + response[i].nama + ' - Rp ' + Spandiv.NumberFormat(response[i].gaji_pokok) + '</option>';
                }
                $("select[name=gaji_pokok]").html(html);
            }
        })
    });

    // Gaji Pokok
    Spandiv.Select2("select[name=gaji_pokok]");

    // Pejabat
    Spandiv.Select2("select[name=pejabat]");

    // Angkatan
    Spandiv.Select2("select[name=angkatan]");

    // TMT
    Spandiv.DatePicker("input[name=tmt]");
    Spandiv.DatePicker("input[name=tanggal_sk]");

    // Add Jabatan dan Unit
    $(document).on("click", ".btn-add", function(e) {
        e.preventDefault();
		$("#modal-jabatan-unit").find("input[name=key]").val("");
		$("#modal-jabatan-unit").find("input[name=id]").val("");
		$("#modal-jabatan-unit").find("select[name=jabatan]").val(null);
		$("#modal-jabatan-unit").find("select[name=unit]").val(null);
        Spandiv.Select2("select[name=jabatan]", {
            dropdownParent: "#modal-jabatan-unit"
        });
        Spandiv.Select2("select[name=unit]", {
            dropdownParent: "#modal-jabatan-unit"
        });
        Spandiv.Modal("#modal-jabatan-unit").show();
    });

    // Edit Jabatan dan Unit
    $(document).on("click", ".btn-edit", function(e) {
        e.preventDefault();
		$("#modal-jabatan-unit").find("input[name=key]").val($(this).data("id"));
		$("#modal-jabatan-unit").find("input[name=id]").val($(this).data("detail"));
        if($(this).data("jabatanremun") != 0)
            $("#modal-jabatan-unit").find("select[name=jabatan]").val($(this).data("jabatanremun"));
        else
            $("#modal-jabatan-unit").find("select[name=jabatan]").val($(this).data("jabatan"));
		$("#modal-jabatan-unit").find("select[name=unit]").val($(this).data("unit"));
        Spandiv.Select2("select[name=jabatan]", {
            dropdownParent: "#modal-jabatan-unit"
        });
        Spandiv.Select2("select[name=unit]", {
            dropdownParent: "#modal-jabatan-unit"
        });
        Spandiv.Modal("#modal-jabatan-unit").show();
    });

    // Button Submit
    $(document).on("click", ".btn-submit", function(e) {
        e.preventDefault();
        var key = $("#modal-jabatan-unit").find("input[name=key]").val();
        var id = $("#modal-jabatan-unit").find("input[name=id]").val();
        var jabatan = $("#modal-jabatan-unit").find("select[name=jabatan]").val();
        var unit = $("#modal-jabatan-unit").find("select[name=unit]").val();
        var jabatan_nama = $("#modal-jabatan-unit").find("select[name=jabatan]").find("option[value=" + jabatan + "]").text();
        var unit_nama = $("#modal-jabatan-unit").find("select[name=unit]").find("option[value=" + unit + "]").text();

        // Jika jabatan dan unit kosong
        if(jabatan == null || unit == null) {
            Spandiv.Alert("Jabatan dan unit wajib diisi!");
            return;
        }

        // Get jabatan_id
        var jabatan_id = [];
        $("input[name='jabatan_id[]']").each(function(key,elem) {
            jabatan_id.push($(elem).val());
        });
		
		// Kecualikan jabatan jika ada detail ID
		if(id != '') {
			var jab = $("input[name='detail_id[]'][value=" + id + "]").parent(".lists").find("input[name='jabatan_id[]']").val();
			var index = jabatan_id.indexOf(jab);
			if(index !== -1) {
				jabatan_id.splice(index, 1);
			}
		}
        
        // Jika jabatan ada yang sama
        if(jabatan_id.indexOf(jabatan) > -1) {
            Spandiv.Alert("Jabatan sudah ada!");
            return;
        }

        var html = '';
        html += '<input type="hidden" name="detail_id[]" value="' + id + '">';
        html += '<input type="hidden" name="jabatan_id[]" value="' + jabatan + '">';
        html += '<input type="hidden" name="unit_id[]" value="' + unit + '">';
        html += '<div class="mb-2">';
        html += '<div class="input-group">';
        html += '<input type="text" class="form-control form-control-sm" value="' + jabatan_nama + '" disabled>';
        html += '<input type="text" class="form-control form-control-sm" value="' + unit_nama + '" disabled>';
        html += '<button class="btn btn-outline-primary btn-add" title="Tambah"><i class="bi-plus"></i></button>';
		html += '<button class="btn btn-outline-warning btn-edit" title="Edit" data-id="" data-detail="' + id + '" data-jabatan="' + jabatan + '" data-unit="' + unit + '"><i class="bi-pencil"></i></button>';
        html += '<button class="btn btn-outline-danger btn-delete" title="Hapus"><i class="bi-trash"></i></button>';
        html += '</div>';
        html += '</div>';
		
		// Add / update list berdasarkan key
		if(key != '') $("#jabatan-unit .lists[data-id=" + key + "]").html(html);
		else $("#jabatan-unit .lists:last-child").after('<div class="lists">' + html + '</div>');
		
        Spandiv.Modal("#modal-jabatan-unit").hide();
        refreshLists();
    });

    // Button Delete
    $(document).on("click", ".btn-delete", function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        $("#jabatan-unit .lists[data-id=" + id + "]").remove();
        refreshLists();
    });

    function refreshLists() {
        $("#jabatan-unit .lists").each(function(key,elem) {
            // Add / remove disabled attr
            if($("#jabatan-unit .lists").length > 1)
                $(elem).find(".btn-delete").removeAttr("disabled");
            else
                $(elem).find(".btn-delete").attr("disabled","disabled");

            // Update data-id attr
            $(elem).attr("data-id",key);
            $(elem).find(".btn-edit").attr("data-id",key);
            $(elem).find(".btn-delete").attr("data-id",key);
        });
    }
</script>

@endsection