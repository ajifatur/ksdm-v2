@extends('faturhelper::layouts/admin/main')

@section('title', 'Edit Mutasi Serdos')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Mutasi Serdos</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.tunjangan-profesi.mutasi.update') }}" enctype="multipart/form-data">
                    @csrf
					<input type="hidden" name="id" value="{{ $mutasi_serdos->id }}">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nama / NIP</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ strtoupper($mutasi_serdos->pegawai->nama) }} - {{ $mutasi_serdos->pegawai->nip }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Gaji Pokok</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="Rp {{ number_format($mutasi_serdos->gaji_pokok->gaji_pokok) }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Unit</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $mutasi_serdos->unit->nama }}" disabled>
                        </div>
                    </div>
					<hr>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nama Supplier <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="nama_supplier" class="form-control form-control-sm {{ $errors->has('nama_supplier') ? 'border-danger' : '' }}" value="{{ $mutasi_serdos->nama_supplier }}">
                            @if($errors->has('nama_supplier'))
                            <div class="small text-danger">{{ $errors->first('nama_supplier') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nomor Rekening <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="nomor_rekening" class="form-control form-control-sm {{ $errors->has('nomor_rekening') ? 'border-danger' : '' }}" value="{{ $mutasi_serdos->nomor_rekening }}">
                            @if($errors->has('nomor_rekening'))
                            <div class="small text-danger">{{ $errors->first('nomor_rekening') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="nama_rekening" class="form-control form-control-sm {{ $errors->has('nama_rekening') ? 'border-danger' : '' }}" value="{{ $mutasi_serdos->nama_rekening }}">
                            @if($errors->has('nama_rekening'))
                            <div class="small text-danger">{{ $errors->first('nama_rekening') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Angkatan <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="angkatan" class="form-select form-select-sm {{ $errors->has('angkatan') ? 'border-danger' : '' }}">
                                <option value="" disabled selected>--Pilih--</option>
                                @for($i=1; $i<=3; $i++)
                                    <?php $label = ['', 'Kehormatan Profesor', 'Profesi GB', 'Profesi Non GB']; ?>
                                    <optgroup label="{{ $label[$i] }}">
                                        @foreach($angkatan[$i]['data'] as $a)
                                        <option value="{{ $a->id }}" {{ $mutasi_serdos->angkatan_id == $a->id ? 'selected' : '' }}>{{ $a->nama }}</option>
                                        @endforeach
                                    <optgroup>
                                @endfor
                            </select>
                            @if($errors->has('angkatan'))
                            <div class="small text-danger">{{ $errors->first('angkatan') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jenis <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="jenis" class="form-select form-select-sm {{ $errors->has('jenis') ? 'border-danger' : '' }}">
                                <option value="" disabled selected>--Pilih--</option>
								@foreach($jenis as $j)
                                <option value="{{ $j->id }}" {{ $mutasi_serdos->jenis_id == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('jenis'))
                            <div class="small text-danger">{{ $errors->first('jenis') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">TMT <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tmt" class="form-control form-control-sm {{ $errors->has('tmt') ? 'border-danger' : '' }}" value="{{ date('d/m/Y', strtotime($mutasi_serdos->tmt)) }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
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

@endsection

@section('js')
<script>
	// Select2
    Spandiv.Select2("select[name=pegawai]");
    Spandiv.Select2("select[name=angkatan]");
    Spandiv.Select2("select[name=jenis]");

    $(document).on("change", "select[name=pegawai]", function() {
        var pegawai = $(this).val();
		var unit_id = $("select[name=pegawai]").find("option[value="+pegawai+"]").data("unitid");
		var unit_nama = $("select[name=pegawai]").find("option[value="+pegawai+"]").data("unitnama");
		$("input[name=unit_id]").val(unit_id);
		$("input[name=unit]").val(unit_nama);
		
        $.ajax({
            type: "get",
            url: Spandiv.URL("{{ route('api.gaji-pokok.index') }}", {pegawai: pegawai}),
            success: function(response) {
                var html = '';
                html += '<option value="" selected">--Pilih--</option>';
                for(i=0; i<response.gaji_pokok.length; i++) {
                    html += '<option value="' + response.gaji_pokok[i].id + '">' + response.gaji_pokok[i].nama + ' - Rp ' + Spandiv.NumberFormat(response.gaji_pokok[i].gaji_pokok) + '</option>';
                }
                $("select[name=gaji_pokok]").html(html);
                Spandiv.Select2("select[name=gaji_pokok]");
                $("select[name=gaji_pokok]").val(response.id);
                $("#gaji-pokok").removeClass("d-none");
            }
        })
    });

    // TMT
    Spandiv.DatePicker("input[name=tmt]");
</script>

@endsection