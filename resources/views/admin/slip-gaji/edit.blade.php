@extends('faturhelper::layouts/admin/main')

@section('title', 'Edit Slip Gaji')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Slip Gaji</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.slip-gaji.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $slip_gaji->id }}">
                    <input type="hidden" name="pegawai_id" value="{{ $slip_gaji->pegawai_id }}">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Pegawai</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $slip_gaji->pegawai->nama }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Bulan, Tahun <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <select name="bulan" class="form-select form-select-sm {{ $errors->has('bulan') ? 'border-danger' : '' }}">
                                    <option value="" disabled selected>--Pilih Bulan--</option>
                                    @for($m=1; $m<=12; $m++)
                                    <option value="{{ $m }}" {{ $slip_gaji->bulan == $m ? 'selected' : '' }}>{{ \Ajifatur\Helpers\DateTimeExt::month($m) }}</option>
                                    @endfor
                                </select>
                                <select name="tahun" class="form-select form-select-sm {{ $errors->has('tahun') ? 'border-danger' : '' }}">
                                    <option value="" disabled selected>--Pilih Tahun--</option>
                                    @for($y=date('Y'); $y>=2023; $y--)
                                    <option value="{{ $y }}" {{ $slip_gaji->tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
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
                            <input type="text" name="jabatan" class="form-control form-control-sm" value="{{ $slip_gaji->jabatan }}">
                            @if($errors->has('jabatan'))
                            <div class="small text-danger">{{ $errors->first('jabatan') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jabatan (Bahasa Inggris) <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="position" class="form-control form-control-sm {{ $errors->has('position') ? 'border-danger' : '' }}" value="{{ $slip_gaji->position }}">
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
                                <input type="text" name="additional_allowance" class="form-control form-control-sm {{ $errors->has('additional_allowance') ? 'border-danger' : '' }}" value="{{ number_format($slip_gaji->additional_allowance,0,',',',') }}" required>
                            </div>
                            @if($errors->has('additional_allowance'))
                            <div class="small text-danger">{{ $errors->first('additional_allowance') }}</div>
                            @endif
							<div class="alert alert-warning mt-3 mb-0" role="alert">
								<div class="alert-message">
									<div class="fw-bold"><i class="bi-info-circle-fill me-1"></i> Info</div>
                                    <ol class="mb-0 ps-3">
                                        <li>Remun Gaji a.n. <span id="remun-gaji-pegawai">{{ $slip_gaji->pegawai->nama }}</span> pada bulan tersebut yaitu <strong>Rp <span id="remun-gaji-nominal">{{ number_format($remun_gaji,0,',',',') }}</span></strong>.</li>
                                        <li>Uang Makan a.n. <span id="uang-makan-pegawai">{{ $slip_gaji->pegawai->nama }}</span> pada bulan tersebut yaitu <strong>Rp <span id="uang-makan-nominal">{{ number_format($uang_makan,0,',',',') }}</span></strong>.</li>
                                    </ol>
                                    <div>Total yaitu <strong>Rp <span id="total-nominal">{{ number_format($remun_gaji + $uang_makan,0,',',',') }}</span></strong>.</div>
								</div>
							</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tanggal" class="form-control form-control-sm {{ $errors->has('tanggal') ? 'border-danger' : '' }}" value="{{ date('d/m/Y', strtotime($slip_gaji->tanggal)) }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
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
    Spandiv.DatePicker("input[name=tanggal]");
	
	// Change tahun and bulan
    $(document).on("change", "select[name=tahun], select[name=bulan]", function() {
		var pegawai = $("input[name=pegawai_id]").val(); 
		var tahun = $("select[name=tahun]").val();
		var bulan = $("select[name=bulan]").val();
		if(pegawai != null && tahun != null && bulan != null) {
			$.ajax({
				type: "get",
				url: Spandiv.URL("{{ route('api.slip-gaji.additional') }}", {pegawai: pegawai, tahun: tahun, bulan: bulan}),
				success: function(response) {
					if(response.message == undefined) {
						$(".alert-warning").removeClass("d-none");
						$("#remun-gaji-pegawai").text(response.pegawai.nama);
						$("#remun-gaji-nominal").text(response.remun_gaji);
						$("#uang-makan-pegawai").text(response.pegawai.nama);
						$("#uang-makan-nominal").text(response.uang_makan);
						$("#total-nominal").text(response.total);
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