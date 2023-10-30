@extends('faturhelper::layouts/admin/main')

@section('title', 'Tambah Slip Gaji')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Tambah Slip Gaji</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.slip-gaji.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Pegawai <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="pegawai" class="form-select form-select-sm {{ $errors->has('pegawai') ? 'border-danger' : '' }}">
                                <option value="" disabled selected>--Pilih--</option>
                                @foreach($pegawai as $p)
                                <option value="{{ $p->id }}" data-jabatan="{{ $p->jabstruk ? $p->jabfung->nama.' ('.$p->jabstruk->nama.')' : $p->jabfung->nama }}">{{ $p->nama }} - {{ $p->nip }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('pegawai'))
                            <div class="small text-danger">{{ $errors->first('pegawai') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Bulan, Tahun <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <select name="bulan" class="form-select form-select-sm {{ $errors->has('bulan') ? 'border-danger' : '' }}">
                                    <option value="" disabled selected>--Pilih Bulan--</option>
                                    @for($m=1; $m<=12; $m++)
                                    <option value="{{ $m }}" {{ old('bulan') == $m ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($m) }}</option>
                                    @endfor
                                </select>
                                <select name="tahun" class="form-select form-select-sm {{ $errors->has('tahun') ? 'border-danger' : '' }}">
                                    <option value="" disabled selected>--Pilih Tahun--</option>
                                    @for($y=date('Y'); $y>=2023; $y--)
                                    <option value="{{ $y }}" {{ old('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            @if($errors->has('bulan') || $errors->has('tahun'))
                            <div class="small text-danger">{{ $errors->first('bulan') }}</div>
                            <div class="small text-danger">{{ $errors->first('tahun') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jabatan (Bahasa Indonesia) <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="jabatan" class="form-control form-control-sm" value="{{ old('jabatan') }}">
                            @if($errors->has('jabatan'))
                            <div class="small text-danger">{{ $errors->first('jabatan') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jabatan (Bahasa Inggris) <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="position" class="form-control form-control-sm {{ $errors->has('position') ? 'border-danger' : '' }}" value="{{ old('position') }}">
                            @if($errors->has('position'))
                            <div class="small text-danger">{{ $errors->first('position') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tunjangan Lain-Lain <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" name="additional_allowance" class="form-control form-control-sm {{ $errors->has('additional_allowance') ? 'border-danger' : '' }}" value="{{ old('additional_allowance') }}" required>
                            </div>
                            @if($errors->has('additional_allowance'))
                            <div class="small text-danger">{{ $errors->first('additional_allowance') }}</div>
                            @endif
							<div class="alert alert-warning mt-3 d-none" role="alert">
								<div class="alert-message">
									<div class="fw-bold"><i class="bi-info-circle-fill me-1"></i> Info</div>
									Remun Gaji a.n. <span id="remun-gaji-pegawai"></span> pada bulan tersebut yaitu <strong>Rp <span id="remun-gaji-nominal"></span></strong>.
								</div>
							</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tanggal" class="form-control form-control-sm {{ $errors->has('tanggal') ? 'border-danger' : '' }}" value="{{ date('d/m/Y') }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
                                <span class="input-group-text"><i class="bi-calendar2"></i></span>
                            </div>
                            @if($errors->has('tanggal'))
                            <div class="small text-danger">{{ $errors->first('tanggal') }}</div>
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
    Spandiv.Select2("select[name=pegawai]");
    Spandiv.DatePicker("input[name=tanggal]");

    // Change Pegawai
    $(document).on("change", "select[name=pegawai]", function(e) {
        var jabatan = $("select[name=pegawai]").find("option[value=" + $(this).val() + "]").data("jabatan");
        $("input[name=jabatan]").val(jabatan);
    });
	
	// Change pegawai, tahun and bulan
    $(document).on("change", "select[name=pegawai], select[name=tahun], select[name=bulan]", function() {
		var pegawai = $("select[name=pegawai]").val(); 
		var tahun = $("select[name=tahun]").val();
		var bulan = $("select[name=bulan]").val();
		if(pegawai != null && tahun != null && bulan != null) {
			$.ajax({
				type: "get",
				url: Spandiv.URL("{{ route('api.slip-gaji.remun-gaji') }}", {pegawai: pegawai, tahun: tahun, bulan: bulan}),
				success: function(response) {
					if(response.message == undefined) {
						$(".alert-warning").removeClass("d-none");
						$("#remun-gaji-pegawai").text(response.pegawai.nama);
						$("#remun-gaji-nominal").text(response.remun_gaji);
					}
					else
						$(".alert-warning").addClass("d-none");
				}
			});
		}
    });

    // Keyup additional allowance
    $(document).on("keyup", "input[name=additional_allowance]", function(e) {
        e.preventDefault();
        var number = $(this).val().replace(/[^.\d]/g, '');
        number = number != '' ? parseInt(number) : 0;
        $(this).val(number >= 100 ? Spandiv.NumberFormat(number) : number);
    });
</script>

@endsection