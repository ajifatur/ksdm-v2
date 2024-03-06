@extends('faturhelper::layouts/admin/main')

@section('title', 'Edit SPKGB '.(in_array($spkgb->pegawai->status_kepegawaian->nama, ['CPNS','PNS']) ? 'PNS' : 'Pegawai Tetap Non ASN'))

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit SPKGB {{ in_array($spkgb->pegawai->status_kepegawaian->nama, ['CPNS','PNS']) ? 'PNS' : 'Pegawai Tetap Non ASN' }}</h1>
</div>
<div class="row">
	<div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.spkgb.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $spkgb->id }}">
                    <input type="hidden" name="mutasi_id" value="{{ $spkgb->mutasi_id }}">
                    <input type="hidden" name="mutasi_sebelum_id" value="{{ $spkgb->mutasi_sebelum_id }}">
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">No. SK <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="no_sk_baru" class="form-control form-control-sm {{ $errors->has('no_sk_baru') ? 'border-danger' : '' }}" value="{{ $spkgb->mutasi->perubahan->no_sk }}" autofocus>
                            @if($errors->has('no_sk_baru'))
                            <div class="small text-danger">{{ $errors->first('no_sk_baru') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal SK <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tanggal_sk_baru" class="form-control form-control-sm {{ $errors->has('tanggal_sk_baru') ? 'border-danger' : '' }}" value="{{ date('d/m/Y', strtotime($spkgb->mutasi->perubahan->tanggal_sk)) }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
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
                            <input type="text" class="form-control form-control-sm" value="{{ $spkgb->nama }} - {{ $spkgb->pegawai->nip }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Pangkat</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $spkgb->mutasi->golru->indonesia }} - {{ $spkgb->mutasi->golru->nama }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jabatan</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $spkgb->jabfung->nama }} {{ $spkgb->jabstruk ? '('.$spkgb->jabstruk->nama.')' : '' }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Unit Kerja</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $spkgb->unit->full }}" disabled>
                        </div>
                    </div>
                    <hr>
                    <h5 class="fw-bold mb-3">Gaji Pokok Lama</h5>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Jenis <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <select name="jenis_mutasi" class="form-select form-select-sm {{ $errors->has('jenis_mutasi') ? 'border-danger' : '' }}">
                                <option value="" disabled selected>--Pilih--</option>
                                @foreach($jenis_mutasi as $j)
                                <option value="{{ $j->id }}" {{ $spkgb->mutasi_sebelum->jenis_id == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
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
                                <option value="{{ $g->id }}" {{ $spkgb->mutasi_sebelum && $spkgb->mutasi_sebelum->golru_id == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
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
                            <div class="input-group">
                                <select name="sk_gapok_pns" class="form-select form-select-sm {{ $errors->has('sk_gapok_pns') ? 'border-danger' : '' }}" style="width: 50%;">
                                    <option value="" disabled selected>--Pilih--</option>
                                    @foreach($sk_gapok_pns as $s)
                                    <option value="{{ $s->id }}" {{ $spkgb->mutasi_sebelum && $spkgb->mutasi_sebelum->gaji_pokok->sk_id == $s->id ? 'selected' : '' }}>{{ $s->awal_tahun }}</option>
                                    @endforeach
                                </select>
                                <select name="gaji_pokok" class="form-select form-select-sm {{ $errors->has('gaji_pokok') ? 'border-danger' : '' }}" style="width: 50%;">
                                    <option value="" disabled selected>--Pilih--</option>
                                    @foreach($gaji_pokok as $gp)
                                    <option value="{{ $gp->id }}" {{ $spkgb->mutasi_sebelum && $spkgb->mutasi_sebelum->gaji_pokok_id == $gp->id ? 'selected' : '' }}>{{ $gp->nama }} - Rp {{ number_format($gp->gaji_pokok) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($errors->has('gaji_pokok'))
                            <div class="small text-danger">{{ $errors->first('gaji_pokok') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">No. SK <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="no_sk" class="form-control form-control-sm {{ $errors->has('no_sk') ? 'border-danger' : '' }}" value="{{ $spkgb->mutasi_sebelum->perubahan ? $spkgb->mutasi_sebelum->perubahan->no_sk : '' }}">
                            @if($errors->has('no_sk'))
                            <div class="small text-danger">{{ $errors->first('no_sk') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Tanggal SK <span class="text-danger">*</span></label>
                        <div class="col-lg-10 col-md-9">
                            <div class="input-group">
                                <input type="text" name="tanggal_sk" class="form-control form-control-sm {{ $errors->has('tanggal_sk') ? 'border-danger' : '' }}" value="{{ $spkgb->mutasi_sebelum->perubahan ? date('d/m/Y', strtotime($spkgb->mutasi_sebelum->perubahan->tanggal_sk)) : '' }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
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
                                <input type="text" name="tmt_sebelum" class="form-control form-control-sm {{ $errors->has('tmt_sebelum') ? 'border-danger' : '' }}" value="{{ date('d/m/Y', strtotime($spkgb->mutasi_sebelum->tmt)) }}" autocomplete="off" placeholder="Format: dd/mm/yyyy">
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
                            <div class="input-group">
                                <select name="mk_tahun" class="form-select form-select-sm {{ $errors->has('mk_tahun') ? 'border-danger' : '' }}" style="width: 45%;">
                                    <option value="" disabled selected>--Pilih Masa Kerja Tahun--</option>
                                    @for($i=0; $i<=50; $i++)
                                    <option value="{{ $i }}" {{ $spkgb->mutasi_sebelum->perubahan && $spkgb->mutasi_sebelum->perubahan->mk_tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                <span class="input-group-text" style="width: 5%;">Tahun</span>
                                <select name="mk_bulan" class="form-select form-select-sm {{ $errors->has('mk_bulan') ? 'border-danger' : '' }}" style="width: 45%;">
                                    <option value="" disabled selected>--Pilih Masa Kerja Bulan--</option>
                                    @for($i=0; $i<=11; $i++)
                                    <option value="{{ $i }}" {{ $spkgb->mutasi_sebelum->perubahan && $spkgb->mutasi_sebelum->perubahan->mk_bulan == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                <span class="input-group-text" style="width: 5%;">Bulan</span>
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
                                <option value="{{ $p->id }}" {{ $spkgb->mutasi_sebelum->perubahan && $spkgb->mutasi_sebelum->perubahan->pejabat_id == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('pejabat'))
                            <div class="small text-danger">{{ $errors->first('pejabat') }}</div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <h5 class="fw-bold mb-3">Gaji Pokok Baru</h5>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Gaji Pokok</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $spkgb->mutasi->gaji_pokok->nama }} - {{ 'Rp '.number_format($spkgb->mutasi->gaji_pokok->gaji_pokok) }}" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">Masa Kerja</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" class="form-control form-control-sm" value="{{ $spkgb->mutasi->perubahan->mk_tahun }} tahun 0 bulan" disabled>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-2 col-md-3 col-form-label">TMT KGB</label>
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="tmt" class="form-control form-control-sm {{ $errors->has('tmt') ? 'border-danger' : '' }}" value="{{ \Ajifatur\Helpers\DateTimeExt::full($spkgb->mutasi->tmt) }}" disabled>
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
    Spandiv.Select2("select[name=golru]");
    Spandiv.Select2("select[name=jenis_mutasi]");
    Spandiv.Select2("select[name=sk_gapok_pns]");
    Spandiv.Select2("select[name=gaji_pokok]");
    Spandiv.Select2("select[name=pejabat]");
    Spandiv.Select2("select[name=mk_tahun]");
    Spandiv.Select2("select[name=mk_bulan]");

    // Golru
    $(document).on("change", "select[name=sk_gapok_pns], select[name=golru]", function() {
        var golru = $("select[name=golru]").val();
        var sk = $("select[name=sk_gapok_pns]").val();
        $.ajax({
            type: "get",
            url: Spandiv.URL("{{ route('api.gaji-pokok.index') }}", {golru: golru, sk: sk}),
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

    // DatePicker
    Spandiv.DatePicker("input[name=tanggal_sk_baru]");
    Spandiv.DatePicker("input[name=tanggal_sk]");
    Spandiv.DatePicker("input[name=tmt_sebelum]");
</script>

@endsection