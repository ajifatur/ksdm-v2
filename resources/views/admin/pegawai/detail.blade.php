@extends('faturhelper::layouts/admin/main')

@section('title', 'Profil Pegawai')

@section('content')

<div class="d-sm-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-2 mb-sm-0">Profil Pegawai</h1>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
            <div class="card-header d-sm-flex justify-content-between">
                <div><i class="bi-person me-1"></i> {{ title_name($pegawai->nama, $pegawai->gelar_depan, $pegawai->gelar_belakang) }} ({{ $pegawai->npu != null ? $pegawai->npu : $pegawai->nip }})</div>
                <!-- <div class="btn-group">
                    <a href="#" class="btn btn-sm btn-primary"><i class="bi-file-pdf me-1"></i> Cetak DRH</a>
                </div> -->
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ Request::query('mutasi') == null && Request::query('tunjangan_profesi') == null ? 'active' : '' }}" id="profil-tab" data-bs-toggle="tab" data-bs-target="#profil" type="button" role="tab" aria-controls="profil" aria-selected="true">Profil</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ Request::query('mutasi') == 1 ? 'active' : '' }}" id="mutasi-tab" data-bs-toggle="tab" data-bs-target="#mutasi" type="button" role="tab" aria-controls="mutasi" aria-selected="false">Mutasi</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="remun-gaji-tab" data-bs-toggle="tab" data-bs-target="#remun-gaji" type="button" role="tab" aria-controls="remun-gaji" aria-selected="false">Remun Gaji</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="remun-insentif-tab" data-bs-toggle="tab" data-bs-target="#remun-insentif" type="button" role="tab" aria-controls="remun-insentif" aria-selected="false">Remun Insentif</button>
                    </li>
                    @if(count($pegawai->tunjangan_profesi) > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ Request::query('tunjangan_profesi') == 1 ? 'active' : '' }}" id="tunjangan-tab" data-bs-toggle="tab" data-bs-target="#tunjangan" type="button" role="tab" aria-controls="tunjangan" aria-selected="false">Tunjangan Profesi</button>
                    </li>
                    @endif
                    @if(count($pegawai->gaji) > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="gaji-pns-tab" data-bs-toggle="tab" data-bs-target="#gaji-pns" type="button" role="tab" aria-controls="gaji-pns" aria-selected="false">Gaji ASN</button>
                    </li>
                    @endif
                    @if(count($pegawai->uang_makan) > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="uang-makan-pns-tab" data-bs-toggle="tab" data-bs-target="#uang-makan-pns" type="button" role="tab" aria-controls="uang-makan-pns" aria-selected="false">Uang Makan PNS</button>
                    </li>
                    @endif
                    @if(count($pegawai->slks) > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tanda-jasa-tab" data-bs-toggle="tab" data-bs-target="#tanda-jasa" type="button" role="tab" aria-controls="tanda-jasa" aria-selected="false">Tanda Jasa</button>
                    </li>
                    @endif
                </ul>
                <div class="tab-content py-3">
                    <div class="tab-pane fade {{ Request::query('mutasi') == null && Request::query('tunjangan_profesi') == null ? 'show active' : '' }}" id="profil" role="tabpanel" aria-labelledby="profil-tab">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped mt-3">
                                <tr>
                                    <td width="20%"><strong>NIP / NPU</strong></td>
                                    <td width="5">:</td>
                                    @if($pegawai->npu != null)
                                        <td>{{ $pegawai->npu }} ({{ $pegawai->nip}})</td>
                                    @else
                                        <td>{{ $pegawai->nip }}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td><strong>Nama</strong></td>
                                    <td>:</td>
                                    <td>{{ title_name($pegawai->nama, $pegawai->gelar_depan, $pegawai->gelar_belakang) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Unit Kerja</strong></td>
                                    <td>:</td>
                                    <td>{{ $unit }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jabatan</strong></td>
                                    <td>:</td>
                                    <td>{{ $jabatan }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Golru</strong></td>
                                    <td>:</td>
                                    <td>{{ $golru }}</td>
                                </tr>
                                <tr>
                                    <td><strong>MKG</strong></td>
                                    <td>:</td>
                                    <td>{{ $mkg }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status Kepegawaian</strong></td>
                                    <td>:</td>
                                    <td>{{ $pegawai->jenis == 1 ? 'Dosen' : 'Tendik' }} {{ $pegawai->status_kepegawaian->nama }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status Kerja</strong></td>
                                    <td>:</td>
                                    <td>{{ $pegawai->status_kerja->nama }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade {{ Request::query('mutasi') == 1 ? 'show active' : '' }}" id="mutasi" role="tabpanel" aria-labelledby="mutasi-tab">
                        @if(Session::get('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="alert-message">{{ Session::get('message') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif
                        <a href="{{ route('admin.mutasi.create', ['id' => $pegawai->id]) }}" class="btn btn-sm btn-primary"><i class="bi-plus me-1"></i> Tambah</a>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered mt-3">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5">No</th>
                                        <th>Jenis / Deskripsi</th>
                                        <th>Status Kepeg.</th>
                                        <th>Golru</th>
                                        <th>MKG</th>
                                        <th>Jabatan</th>
                                        <th>Unit</th>
                                        <th>TMT</th>
                                        <th>Diproses</th>
                                        <th width="20">Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->mutasi as $key=>$m)
                                        <tr>
                                            <td>{{ ($key+1) }}</td>
                                            <td>
                                                {{ $m->jenis->nama }}
                                                <br>
                                                {{ $m->uraian != '' ? '('.$m->uraian.')' : '' }}
                                            </td>
                                            <td>{{ $m->status_kepegawaian ? $m->status_kepegawaian->nama : '-' }}</td>
                                            <td>{{ $m->golru ? $m->golru->nama : '-' }}</td>
                                            <td>{{ $m->gaji_pokok ? $m->gaji_pokok->nama : '-' }}</td>
                                            <td>
                                                @foreach($m->detail()->get() as $key2=>$d)
                                                    {{ $d->jabatan ? $d->jabatan->nama : '-' }}
                                                    @if($key2 != count($m->detail()->get())-1)<hr class="my-0">@endif
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach($m->detail()->get() as $key2=>$d)
                                                    {{ $d->unit ? $d->unit->nama : '-' }}
                                                    @if($key2 != count($m->detail()->get())-1)<hr class="my-0">@endif
                                                @endforeach
                                            </td>
                                            <td>{{ $m->tmt != null ? date('d/m/Y', strtotime($m->tmt)) : '-' }}</td>
                                            @if($m->jenis->remun == 1 && ($m->bulan != 0 || $m->tahun != 0))
                                                <td>{{ \Ajifatur\Helpers\DateTimeExt::month($m->bulan) }} {{ $m->tahun }}</td>
                                            @else
                                                <td>-</td>
                                            @endif
                                            <td align="center">
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.mutasi.edit', ['id' => $pegawai->id, 'mutasi_id' => $m->id]) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="bi-pencil"></i></a>
                                                    <a href="#" class="btn btn-sm btn-danger btn-delete-mutasi" data-id="{{ $m->id }}" data-bs-toggle="tooltip" title="Hapus"><i class="bi-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="remun-gaji" role="tabpanel" aria-labelledby="remun-gaji-tab">
                        <div class="alert alert-warning fade show" role="alert">
                            <div class="alert-message">
                                <div class="fw-bold"><i class="bi-info-circle-fill me-1"></i> Info</div>
                                *) Jumlah Dibayarkan belum termasuk PPh Pasal 21 dan potongan pihak ke-3.
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5">No</th>
                                        <th>Bulan, Tahun</th>
                                        <th width="130">Status</th>
                                        @if($remun_gaji_expand == true)
                                            <th>Jabatan / Unit Terbayar</th>
                                            <th>Jabatan / Unit Seharusnya</th>
                                            <th width="130">Terbayar</th>
                                            <th width="130">Seharusnya</th>
                                            <th width="130">Selisih</th>
                                        @else
                                            <th>Jabatan / Unit</th>
                                        @endif
                                        <th width="130">Remun Gaji</th>
                                        <th width="130">Dibayarkan<span class="text-danger">*</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($remun_gaji as $key=>$r)
                                        <tr>
                                            <td align="right">{{ ($key+1) }}</td>
                                            <td>
                                                {{ $r['nama_bulan'] }} {{ $r['tahun'] }}
                                                @if($r['kekurangan'] == true)
                                                <div class="text-danger">(Kekurangan)</div>
                                                @endif
                                            </td>
                                            <td>{{ $r['remun_gaji']->status_kepegawaian->nama }}</td>
                                            <td colspan="{{ $remun_gaji_expand == true ? 2 : 1 }}">
                                                @if($r['kekurangan'] == true)
                                                    {{ $r['lebih_kurang'][0]->jabatan_seharusnya ? $r['lebih_kurang'][0]->jabatan_seharusnya->nama : '-' }}
                                                    <br>
                                                    {{ $r['remun_gaji']->unit ? '('.$r['remun_gaji']->unit->nama.')' : '' }}
                                                @else
                                                    {{ $r['remun_gaji']->jabatan ? $r['remun_gaji']->jabatan->nama : '-' }}
                                                    <br>
                                                    {{ $r['remun_gaji']->unit ? '('.$r['remun_gaji']->unit->nama.')' : '' }}
                                                @endif
                                            </td>
                                            @if($remun_gaji_expand == true)
                                            <td align="right">{{ $r['remun_gaji']->remun_gaji != $r['dibayarkan'] ? number_format($r['lebih_kurang']->sum('terbayar')) : '' }}</td>
                                            <td align="right">{{ $r['remun_gaji']->remun_gaji != $r['dibayarkan'] ? number_format($r['lebih_kurang']->sum('seharusnya')) : '' }}</td>
                                            <td align="right">{{ $r['remun_gaji']->remun_gaji != $r['dibayarkan'] ? number_format($r['lebih_kurang']->sum('selisih')) : '' }}</td>
                                            @endif
                                            <td align="right">{{ $r['kekurangan'] == false ? number_format($r['remun_gaji']->remun_gaji) : '-' }}</td>
                                            <td align="right">{{ number_format($r['dibayarkan']) }}</td>
                                        </tr>
                                        @foreach($r['lebih_kurang'] as $lk)
                                            <tr bgcolor="#efefef">
                                                <td></td>
                                                <td colspan="2"><em>Lebih / kurang pada {{ \Ajifatur\Helpers\DateTimeExt::month($lk->bulan) }} {{ $lk->tahun }}</em></td>
                                                <td>
                                                    {{ $lk->jabatan_terbayar ? $lk->jabatan_terbayar->nama : '-' }}
                                                    <br>
                                                    {{ array_key_exists($key+1, $remun_gaji) && $remun_gaji[$key+1]['remun_gaji']->unit ? '('.$remun_gaji[$key+1]['remun_gaji']->unit->nama.')' : '' }}
                                                </td>
                                                <td>
                                                    {{ $lk->jabatan_seharusnya ? $lk->jabatan_seharusnya->nama : '-' }}
                                                    <br>
                                                    {{ $r['remun_gaji']->unit ? '('.$r['remun_gaji']->unit->nama.')' : '' }}
                                                </td>
                                                <td align="right">{{ number_format($lk->terbayar) }}</td>
                                                <td align="right">{{ number_format($lk->seharusnya) }}</td>
                                                <td align="right">{{ number_format($lk->selisih) }}</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="{{ $remun_gaji_expand == true ? 5 : 4 }}" align="center">Total</td>
                                        @if($remun_gaji_expand == true)
                                        <td align="right"><b>{{ number_format($remun_gaji_total['terbayar']) }}</b></td>
                                        <td align="right"><b>{{ number_format($remun_gaji_total['seharusnya']) }}</b></td>
                                        <td align="right"><b>{{ number_format($remun_gaji_total['selisih']) }}</b></td>
                                        @endif
                                        <td align="right"><b>{{ number_format($pegawai->remun_gaji()->sum('remun_gaji')) }}</b></td>
                                        <td align="right"><b>{{ number_format($remun_gaji_total['dibayarkan']) }}</b></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="remun-insentif" role="tabpanel" aria-labelledby="remun-insentif-tab">
                        <div class="alert alert-warning fade show" role="alert">
                            <div class="alert-message">
                                <div class="fw-bold"><i class="bi-info-circle-fill me-1"></i> Info</div>
                                *) Jumlah Dibayarkan belum termasuk PPh Pasal 21 dan potongan pihak ke-3.
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5">No</th>
                                        <th>Periode</th>
                                        <th width="130">Status</th>
                                        <th>Jabatan / Unit</th>
                                        <th width="80">Poin</th>
                                        <th width="130">Remun Insentif</th>
                                        <th width="130">Potongan</th>
                                        <th width="130">Dibayarkan<span class="text-danger">*</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $total_potongan = 0;
                                        $total_dibayarkan = 0;
                                    ?>
                                    @foreach($pegawai->remun_insentif as $key=>$r)
                                        <?php
                                            $potongan = \App\Models\LebihKurang::where('pegawai_id','=',$r->pegawai->id)->where('triwulan_proses','=',$r->triwulan)->where('tahun_proses','=',$r->tahun)->sum('selisih');
                                            $dibayarkan = $r->remun_insentif + $potongan;
                                            
                                            // Sum total
                                            $total_potongan += $potongan;
                                            $total_dibayarkan += $dibayarkan;
                                        ?>
                                        <tr>
                                            <td align="right">{{ ($key+1) }}</td>
                                            <td>
                                                @if($r->triwulan != 15)
                                                    Remun Triwulan {{ $r->triwulan }} Tahun {{ $r->tahun }}
                                                @else
                                                    Remun ke-15 Tahun {{ $r->tahun }}
                                                @endif
                                            </td>
                                            <td>{{ $r->status_kepegawaian ? $r->status_kepegawaian->nama : '-' }}</td>
                                            <td>
                                                {{ $r->jabatan ? $r->jabatan->nama : '-' }}
                                                <br>
                                                {{ $r->unit ? '('.$r->unit->nama.')' : '' }}
                                            </td>
                                            <td align="right">{{ $r->triwulan != 15 ? number_format($r->poin, 2) : '-' }}</td>
                                            <td align="right">{{ number_format($r->remun_insentif) }}</td>
                                            <td align="right">{{ number_format(abs($potongan)) }}</td>
                                            <td align="right">{{ number_format($dibayarkan) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="5" align="center">Total</td>
                                        <td align="right"><b>{{ number_format($pegawai->remun_insentif()->sum('remun_insentif')) }}</b></td>
                                        <td align="right"><b>{{ number_format(abs($total_potongan)) }}</b></td>
                                        <td align="right"><b>{{ number_format($total_dibayarkan) }}</b></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @if(count($pegawai->tunjangan_profesi) > 0)
                    <div class="tab-pane fade {{ Request::query('tunjangan_profesi') == 1 ? 'show active' : '' }}" id="tunjangan" role="tabpanel" aria-labelledby="tunjangan-tab">
                        @if(Session::get('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="alert-message">{{ Session::get('message') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5">No</th>
                                        <th>Bulan, Tahun</th>
                                        <th>Jenis</th>
                                        <th>Angkatan</th>
                                        <th>Unit</th>
                                        <th>Tunjangan</th>
                                        <th>PPh</th>
                                        <th>Diterimakan</th>
                                        <th width="20">Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->tunjangan_profesi as $key=>$t)
                                    <tr>
                                        <td>{{ ($key+1) }}</td>
                                        <td>{{ \Ajifatur\Helpers\DateTimeExt::month($t->bulan) }} {{ $t->tahun }}</td>
                                        <td>{{ $t->angkatan->jenis->nama }}</td>
                                        <td>{{ $t->angkatan->nama }}</td>
                                        <td>{{ $t->unit->nama }}</td>
                                        <td align="right">{{ number_format($t->tunjangan) }}</td>
                                        <td align="right">{{ number_format($t->pph) }}</td>
                                        <td align="right">{{ number_format($t->diterimakan) }}</td>
                                        <td align="center">
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-sm btn-danger btn-delete-tunjangan-profesi" data-id="{{ $t->id }}" data-bs-toggle="tooltip" title="Hapus"><i class="bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="5" align="center">Total</td>
                                        <td align="right">{{ number_format($pegawai->tunjangan_profesi()->sum('tunjangan')) }}</td>
                                        <td align="right">{{ number_format($pegawai->tunjangan_profesi()->sum('pph')) }}</td>
                                        <td align="right">{{ number_format($pegawai->tunjangan_profesi()->sum('diterimakan')) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif
                    @if(count($pegawai->gaji) > 0)
                    <div class="tab-pane fade" id="gaji-pns" role="tabpanel" aria-labelledby="gaji-pns-tab">
                        @if(Session::get('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="alert-message">{{ Session::get('message') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th rowspan="2" width="5">No</th>
                                        <th rowspan="2">Bulan, Tahun</th>
                                        <th rowspan="2">Anak Satker,<br>Unit</th>
                                        <th colspan="4">Penghasilan</th>
                                        <th colspan="3">Potongan</th>
                                        <th rowspan="2" width="130">Gaji Bersih</th>
                                    </tr>
                                    <tr>
                                        <th width="130">Gaji Pokok,<br>Tunj. Istri,<br>Tunj. Anak</th>
                                        <th width="130">Tunj. Fungsional,<br>Tunj. Struktural,<br>Tunj. Umum</th>
                                        <th width="130">Tunj. Beras,<br>Tunj. Kh. Pajak,<br>Pembulatan</th>
                                        <th width="130">Jumlah Penghasilan Kotor</th>
                                        <th width="130">IWP,<br>BPJS,<br>BPJS2</th>
                                        <th width="130">Pajak Penghasilan</th>
                                        <th width="130">Jumlah Potongan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->gaji as $key=>$g)
                                    <tr>
                                        <td>{{ ($key+1) }}</td>
                                        <td>
                                            {{ \Ajifatur\Helpers\DateTimeExt::month((int)$g->bulan) }} {{ $g->tahun }}
                                            <br>
                                            <span class="text-success">({{ $g->jenis_gaji->nama }})</span>
                                        </td>
										<td>
											{{ $g->anak_satker ? $g->anak_satker->nama.' ('.$g->anak_satker->kode.')' : '' }}
											<br>
											{{ $g->unit ? $g->unit->nama : '-' }}
										</td>
                                        <td align="right">{{ number_format($g->gjpokok) }}<br>{{ number_format($g->tjistri) }}<br>{{ number_format($g->tjanak) }}</td>
                                        <td align="right">{{ number_format($g->tjfungs) }}<br>{{ number_format($g->tjstruk) }}<br>{{ number_format($g->tjupns) }}</td>
                                        <td align="right">{{ number_format($g->tjberas) }}<br>{{ number_format($g->tjpph) }}<br>{{ number_format($g->pembul) }}</td>
                                        <td align="right">{{ number_format($g->nominal) }}</td>
                                        <td align="right">{{ number_format($g->potpfk10) }}<br>{{ number_format($g->bpjs) }}<br>{{ number_format($g->bpjs2) }}</td>
                                        <td align="right">{{ number_format($g->potpph) }}</td>
                                        <td align="right">{{ number_format($g->potongan) }}</td>
                                        <td align="right">{{ number_format($g->nominal - $g->potongan) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="3" align="center">Total</td>
                                        <td align="right">{{ number_format($pegawai->gaji()->sum('gjpokok') + $pegawai->gaji()->sum('tjistri') + $pegawai->gaji()->sum('tjanak')) }}</td>
                                        <td align="right">{{ number_format($pegawai->gaji()->sum('tjfungs') + $pegawai->gaji()->sum('tjstruk') + $pegawai->gaji()->sum('tjupns')) }}</td>
                                        <td align="right">{{ number_format($pegawai->gaji()->sum('tjberas') + $pegawai->gaji()->sum('tjpph') + $pegawai->gaji()->sum('pembul')) }}</td>
                                        <td align="right">{{ number_format($pegawai->gaji()->sum('nominal')) }}</td>
                                        <td align="right">{{ number_format($pegawai->gaji()->sum('potpfk10') + $pegawai->gaji()->sum('bpjs') + $pegawai->gaji()->sum('bpjs2')) }}</td>
                                        <td align="right">{{ number_format($pegawai->gaji()->sum('potpph')) }}</td>
                                        <td align="right">{{ number_format($pegawai->gaji()->sum('potongan')) }}</td>
                                        <td align="right">{{ number_format($pegawai->gaji()->sum('nominal') - $pegawai->gaji()->sum('potongan')) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif
                    @if(count($pegawai->uang_makan) > 0)
                    <div class="tab-pane fade" id="uang-makan-pns" role="tabpanel" aria-labelledby="uang-makan-pns-tab">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5">No</th>
                                        <th>Bulan, Tahun</th>
                                        <th>Anak Satker,<br>Unit</th>
                                        <th width="130">Jumlah Hari</th>
                                        <th width="130">Nominal Kotor</th>
                                        <th width="130">Potongan</th>
                                        <th width="130">Nominal Bersih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->uang_makan as $key=>$um)
                                    <tr>
                                        <td>{{ ($key+1) }}</td>
                                        <td>{{ \Ajifatur\Helpers\DateTimeExt::month((int)$um->bulan) }} {{ $um->tahun }}</td>
										<td>
											{{ $um->anak_satker ? $um->anak_satker->nama.' ('.$um->anak_satker->kode.')' : '' }}
											<br>
											{{ $um->unit ? $um->unit->nama : '-' }}
										</td>
                                        <td align="right">{{ number_format($um->jmlhari) }}</td>
                                        <td align="right">{{ number_format($um->kotor) }}</td>
                                        <td align="right">{{ number_format($um->potongan) }}</td>
                                        <td align="right">{{ number_format($um->bersih) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="3" align="center">Total</td>
                                        <td align="right">{{ number_format($pegawai->uang_makan()->sum('jmlhari')) }}</td>
                                        <td align="right">{{ number_format($pegawai->uang_makan()->sum('kotor')) }}</td>
                                        <td align="right">{{ number_format($pegawai->uang_makan()->sum('potongan')) }}</td>
                                        <td align="right">{{ number_format($pegawai->uang_makan()->sum('bersih')) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif
                    @if(count($pegawai->slks) > 0)
                    <div class="tab-pane fade" id="tanda-jasa" role="tabpanel" aria-labelledby="tanda-jasa-tab">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5">No</th>
                                        <th>Penghargaan</th>
                                        <th>Periode</th>
                                        <th>No. Keppres</th>
                                        <th>Tgl. Keppres</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pegawai->slks as $key=>$s)
                                    <tr>
                                        <td>{{ ($key+1) }}</td>
                                        <td>Satyalancana Karya Satya {{ $s->tahun }} Tahun</td>
                                        <td>{{ $s->slks->periode }}</td>
                                        <td>{{ $s->slks->no_keppres }}</td>
                                        <td>{{ date('d/m/Y', strtotime($s->slks->tanggal_keppres)) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
		</div>
	</div>
</div>

<form class="form-delete-mutasi d-none" method="post" action="{{ route('admin.mutasi.delete') }}">
    @csrf
    <input type="hidden" name="id">
	<input type="hidden" name="redirect" value="{{ Request::url() }}">
</form>

<form class="form-delete-tunjangan-profesi d-none" method="post" action="{{ route('admin.tunjangan-profesi.delete') }}">
    @csrf
    <input type="hidden" name="id">
</form>

@endsection

@section('js')

<script>
    // Button Delete Mutasi
    Spandiv.ButtonDelete(".btn-delete-mutasi", ".form-delete-mutasi");

    // Button Delete Tunjangan Profesi
    Spandiv.ButtonDelete(".btn-delete-tunjangan-profesi", ".form-delete-tunjangan-profesi");
</script>

@endsection

@section('css')

<style>
    .table tr th {text-align: center!important; vertical-align: middle!important;}
    .table tr td {vertical-align: top!important;}
</style>

@endsection