@extends('faturhelper::layouts/admin/main')

@section('title', 'Tambah SPKGB')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Tambah SPKGB</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.spkgb.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pegawai_id" value="{{ $pegawai->id }}">
                    <input type="hidden" name="mutasi_sebelum_id" value="{{ $mutasi_sebelum ? $mutasi_sebelum->id : 0 }}">
                    <input type="hidden" name="nama" value="{{ title_name($pegawai->nama, $pegawai->gelar_depan, $pegawai->gelar_belakang) }}">
                    <input type="hidden" name="gaji_pokok_baru" value="{{ $gaji_pokok_baru->id }}">
                    <input type="hidden" name="mk_tahun_baru" value="{{ $mk_baru }}">
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">No. SK <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="no_sk_baru" class="form-control form-control-sm {{ $errors->has('no_sk_baru') ? 'border-danger' : '' }}" value="{{ old('no_sk_baru') }}" autofocus>
                            @if($errors->has('no_sk_baru'))
                            <div class="small text-danger">{{ $errors->first('no_sk_baru') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal SK <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tanggal_sk_baru" class="form-control form-control-sm {{ $errors->has('tanggal_sk_baru') ? 'border-danger' : '' }}" value="{{ date('d/m/Y') }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
                                <span class="input-group-text"><i class="bi-calendar2"></i></span>
                            </div>
                            @if($errors->has('tanggal_sk_baru'))
                            <div class="small text-danger">{{ $errors->first('tanggal_sk_baru') }}</div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Nama dan NIP</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ title_name($pegawai->nama, $pegawai->gelar_depan, $pegawai->gelar_belakang) }} - {{ $pegawai->nip }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Pangkat</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $mutasi->golru->indonesia }} - {{ $mutasi->golru->nama }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jabatan</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $pegawai->jabfung->nama }} {{ $pegawai->jabstruk ? '('.$pegawai->jabstruk->nama.')' : '' }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Unit Kerja</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $pegawai->unit->full }}" disabled>
                        </div>
                    </div>
                    <hr>
                    <h5 class="fw-bold mb-3">Gaji Pokok Lama</h5>
                    @if($mutasi_sebelum)
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Gaji Pokok</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $mutasi_sebelum->gaji_pokok->nama }} - {{ 'Rp '.number_format($mutasi_sebelum->gaji_pokok->gaji_pokok) }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Oleh Pejabat</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $mutasi_sebelum->perubahan ? $mutasi_sebelum->perubahan->pejabat->nama : '-' }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal dan Nomor SK</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $mutasi_sebelum->perubahan ? \Ajifatur\Helpers\DateTimeExt::full($mutasi_sebelum->perubahan->tanggal_sk) : '-' }}; {{ $mutasi_sebelum->perubahan ? $mutasi_sebelum->perubahan->no_sk : '-' }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">TMT</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $mutasi_sebelum->perubahan ? \Ajifatur\Helpers\DateTimeExt::full($mutasi_sebelum->perubahan->tmt) : '-' }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Masa Kerja</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $mutasi_sebelum->perubahan ? $mutasi_sebelum->perubahan->mk_tahun : '-' }} tahun {{ $mutasi_sebelum->perubahan ? $mutasi_sebelum->perubahan->mk_bulan : '-' }} bulan" disabled>
                        </div>
                    </div>
                    @else
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jenis <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="jenis_mutasi" class="form-select form-select-sm {{ $errors->has('jenis_mutasi') ? 'border-danger' : '' }}">
                                <option value="" disabled selected>--Pilih--</option>
                                @foreach($jenis_mutasi as $j)
                                <option value="{{ $j->id }}" {{ old('jenis') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('jenis_mutasi'))
                            <div class="small text-danger">{{ $errors->first('jenis_mutasi') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Golru <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="golru" class="form-select form-select-sm {{ $errors->has('golru') ? 'border-danger' : '' }}">
                                <option value="" disabled selected>--Pilih--</option>
                                @foreach($golru as $g)
                                <option value="{{ $g->id }}" {{ $mutasi && $mutasi->golru_id == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
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
                                <option value="{{ $gp->id }}" {{ $mutasi && $mutasi->gaji_pokok_id == $gp->id ? 'selected' : '' }}>{{ $gp->nama }} - Rp {{ number_format($gp->gaji_pokok) }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('gaji_pokok'))
                            <div class="small text-danger">{{ $errors->first('gaji_pokok') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">No. SK <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="no_sk" class="form-control form-control-sm {{ $errors->has('no_sk') ? 'border-danger' : '' }}" value="{{ old('no_sk') }}">
                            @if($errors->has('no_sk'))
                            <div class="small text-danger">{{ $errors->first('no_sk') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal SK <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tanggal_sk" class="form-control form-control-sm {{ $errors->has('tanggal_sk') ? 'border-danger' : '' }}" value="{{ old('tanggal_sk') }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
                                <span class="input-group-text"><i class="bi-calendar2"></i></span>
                            </div>
                            @if($errors->has('tanggal_sk'))
                            <div class="small text-danger">{{ $errors->first('tanggal_sk') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">TMT <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tmt_sebelum" class="form-control form-control-sm {{ $errors->has('tmt_sebelum') ? 'border-danger' : '' }}" value="{{ old('tmt_sebelum') }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
                                <span class="input-group-text"><i class="bi-calendar2"></i></span>
                            </div>
                            @if($errors->has('tmt_sebelum'))
                            <div class="small text-danger">{{ $errors->first('tmt_sebelum') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Masa Kerja Tahun, Bulan <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <select name="mk_tahun" class="form-select form-select-sm {{ $errors->has('mk_tahun') ? 'border-danger' : '' }}">
                                            <option value="" disabled selected>--Pilih Masa Kerja Tahun--</option>
                                            @for($i=0; $i<=50; $i++)
                                            <option value="{{ $i }}" {{ old('mk_tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <span class="input-group-text">Tahun</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <select name="mk_bulan" class="form-select form-select-sm {{ $errors->has('mk_bulan') ? 'border-danger' : '' }}">
                                            <option value="" disabled selected>--Pilih Masa Kerja Bulan--</option>
                                            @for($i=0; $i<=11; $i++)
                                            <option value="{{ $i }}" {{ old('mk_bulan') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <span class="input-group-text">Bulan</span>
                                    </div>
                                </div>
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
                                <option value="{{ $p->id }}" {{ old('pejabat') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('pejabat'))
                            <div class="small text-danger">{{ $errors->first('pejabat') }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                    <hr>
                    <h5 class="fw-bold mb-3">Gaji Pokok Baru</h5>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Gaji Pokok</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $gaji_pokok_baru->nama }} - {{ 'Rp '.number_format($gaji_pokok_baru->gaji_pokok) }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Masa Kerja</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $mk_baru }} tahun 0 bulan" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">TMT KGB</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="tmt" class="form-control form-control-sm {{ $errors->has('tmt') ? 'border-danger' : '' }}" value="{{ \Ajifatur\Helpers\DateTimeExt::full($tanggal) }}" disabled>
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

    // Select2
    Spandiv.Select2("select[name=jenis_mutasi]");
    Spandiv.Select2("select[name=gaji_pokok]");
    Spandiv.Select2("select[name=pejabat]");
    Spandiv.Select2("select[name=mk_tahun]");
    Spandiv.Select2("select[name=mk_bulan]");

    // DatePicker
    Spandiv.DatePicker("input[name=tanggal_sk_baru]");
    Spandiv.DatePicker("input[name=tanggal_sk]");
    Spandiv.DatePicker("input[name=tmt_sebelum]");
</script>

@endsection