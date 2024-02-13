@extends('faturhelper::layouts/admin/main')

@section('title', 'Dashboard')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Dashboard</h1>
</div>
<div class="alert alert-success d-none" role="alert">
    <div class="alert-message">
        <h4 class="alert-heading">Selamat Datang!</h4>
        <p class="mb-0">Selamat datang kembali <strong>{{ Auth::user()->name }}</strong> di {{ setting('name') }}.</p>
    </div>
</div>
<div class="row mt-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Pegawai</h5>
                    </div>
                    <div class="col-auto">
                        <div class="stat text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
                <h1 class="mt-1 mb-3">{{ number_format($dosen + $tendik) }}</h1>
                <div class="d-flex justify-content-between">
                    <div>
                        <span class="badge badge-primary-light"> <i class="mdi mdi-arrow-bottom-right"></i> {{ number_format($dosen) }} </span>
                        <span class="text-muted">Dosen</span>
                    </div>
                    <div>
                        <span class="badge badge-primary-light"> <i class="mdi mdi-arrow-bottom-right"></i> {{ number_format($tendik) }} </span>
                        <span class="text-muted">Tendik</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Remun Gaji {{ date('Y') }}</h5>
                    </div>
                    <div class="col-auto">
                        <div class="stat text-primary">Rp</div>
                    </div>
                </div>
                <h1 class="mt-1 mb-3">{{ number_format($remun_gaji_total) }}</h1>
                <div>
                    <span class="badge {{ $remun_gaji > 0 ? 'badge-primary-light' : 'badge-danger-light' }}"> <i class="mdi mdi-arrow-bottom-right"></i> {{ number_format($remun_gaji) }} </span>
                    <span class="text-muted">pada bulan ini</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Remun Insentif {{ date('Y') }}</h5>
                    </div>
                    <div class="col-auto">
                        <div class="stat text-primary">Rp</div>
                    </div>
                </div>
                <h1 class="mt-1 mb-3">{{ number_format($remun_insentif_total) }}</h1>
                <div>
                    <span class="badge {{ $remun_insentif > 0 ? 'badge-primary-light' : 'badge-danger-light' }}"> <i class="mdi mdi-arrow-bottom-right"></i> {{ number_format($remun_insentif) }} </span>
                    <span class="text-muted">pada Triwulan {{ $remun_insentif_terakhir->triwulan }}/{{ $remun_insentif_terakhir->tahun }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Gaji ASN {{ date('Y') }}</h5>
                    </div>
                    <div class="col-auto">
                        <div class="stat text-primary">Rp</div>
                    </div>
                </div>
                <h1 class="mt-1 mb-3">{{ number_format($gaji_induk_total) }}</h1>
                <div>
                    <span class="badge {{ $gaji_induk > 0 ? 'badge-primary-light' : 'badge-danger-light' }}"> <i class="mdi mdi-arrow-bottom-right"></i> {{ number_format($gaji_induk) }} </span>
                    <span class="text-muted">pada bulan ini</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Gaji Non ASN {{ date('Y') }}</h5>
                    </div>
                    <div class="col-auto">
                        <div class="stat text-primary">Rp</div>
                    </div>
                </div>
                <h1 class="mt-1 mb-3">{{ number_format($gaji_non_asn_total) }}</h1>
                <div>
                    <span class="badge {{ $gaji_non_asn > 0 ? 'badge-primary-light' : 'badge-danger-light' }}"> <i class="mdi mdi-arrow-bottom-right"></i> {{ number_format($gaji_non_asn) }} </span>
                    <span class="text-muted">pada bulan ini</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Uang Makan ASN {{ date('Y') }}</h5>
                    </div>
                    <div class="col-auto">
                        <div class="stat text-primary">Rp</div>
                    </div>
                </div>
                <h1 class="mt-1 mb-3">{{ number_format($uang_makan_total) }}</h1>
                <div>
                    <span class="badge {{ $uang_makan > 0 ? 'badge-primary-light' : 'badge-danger-light' }}"> <i class="mdi mdi-arrow-bottom-right"></i> {{ number_format($uang_makan) }} </span>
                    <span class="text-muted">pada {{ \Ajifatur\Helpers\DateTimeExt::month((int)$uang_makan_terakhir->bulan) }} {{ $uang_makan_terakhir->tahun }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Tunjangan Kehormatan Profesor {{ date('Y') }}</h5>
                    </div>
                    <div class="col-auto">
                        <div class="stat text-primary">Rp</div>
                    </div>
                </div>
                <h1 class="mt-1 mb-3">{{ number_format($tunjangan_profesor_total) }}</h1>
                <div>
                    <span class="badge {{ $tunjangan_profesor > 0 ? 'badge-primary-light' : 'badge-danger-light' }}"> <i class="mdi mdi-arrow-bottom-right"></i> {{ number_format($tunjangan_profesor) }} </span>
                    <span class="text-muted">pada bulan ini</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Tunjangan Profesi {{ date('Y') }}</h5>
                    </div>
                    <div class="col-auto">
                        <div class="stat text-primary">Rp</div>
                    </div>
                </div>
                <h1 class="mt-1 mb-3">{{ number_format($tunjangan_profesi_total) }}</h1>
                <div>
                    <span class="badge {{ $tunjangan_profesi > 0 ? 'badge-primary-light' : 'badge-danger-light' }}"> <i class="mdi mdi-arrow-bottom-right"></i> {{ number_format($tunjangan_profesi) }} </span>
                    <span class="text-muted">pada bulan ini</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

<script>
    $(document).on("click", ".btn-bypass", function(e) {
        e.preventDefault();
        var provider_id = "{{ Auth::user()->account ? Auth::user()->account->provider_id : '' }}";
        var url = $(this).data("url");
        window.open(url + "auth/google/bypass/" + provider_id, "_blank");
    });
</script>

@endsection