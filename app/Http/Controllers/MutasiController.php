<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\ByStartRowImport;
use App\Models\Mutasi;
use App\Models\MutasiDetail;
use App\Models\MutasiKoorprodi;
use App\Models\Angkatan;
use App\Models\GajiPokok;
use App\Models\Golongan;
use App\Models\Golru;
use App\Models\Jabatan;
use App\Models\JenisMutasi;
use App\Models\LebihKurang;
use App\Models\Pegawai;
use App\Models\Perubahan;
use App\Models\Pejabat;
use App\Models\Prodi;
use App\Models\Referensi;
use App\Models\RemunGaji;
use App\Models\SK;
use App\Models\StatusKepegawaian;
use App\Models\TunjanganProfesi;
use App\Models\Unit;

class MutasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get new dan jenis
        $new = $request->query('new');
        $jenis = $request->query('jenis') ?: 'remun';

        // Get bulan dan tahun
        if($new == 1) {
            $bulan = 0;
            $tahun = 0;
            $tanggal = null;
        }
        else {
            $bulan = $request->query('bulan') ?: date('n');
            $tahun = $request->query('tahun') ?: date('Y');
            $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
        }

        // Get mutasi
        if($jenis == 'remun') {
            $mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->where('proses','=',$tanggal)->orderBy('tmt','desc')->orderBy('remun_penerimaan','desc')->get();
        }
        elseif($jenis == 'serdos') {
            $mutasi = Mutasi::where(function($query) {
                $query->where(function($query) {
                    $query->whereHas('jenis', function(Builder $query) {
                        return $query->where('status','=',1)->where('serdos','=',1)->where('perubahan','=',1);
                    })->whereHas('pegawai', function(Builder $query) {
                        return $query->where('jenis','=',1);
                    })->whereIn('pegawai_id',TunjanganProfesi::groupBy('pegawai_id')->pluck('pegawai_id')->toArray());
                })->orWhere(function($query) {
                    $query->whereHas('jenis', function(Builder $query) {
                        return $query->where('status','=',0)->where('serdos','=',1)->where('perubahan','=',0);
                    })->whereHas('pegawai', function(Builder $query) {
                        return $query->where('jenis','=',1);
                    })->whereIn('pegawai_id',TunjanganProfesi::groupBy('pegawai_id')->pluck('pegawai_id')->toArray());
                })->orWhere(function($query) {
                    $query->whereHas('jenis', function(Builder $query) {
                        return $query->where('status','=',1)->where('serdos','=',1)->where('perubahan','=',0);
                    })->whereHas('pegawai', function(Builder $query) {
                        return $query->where('jenis','=',1);
                    });
                });
            })->whereHas('status_kepegawaian', function(Builder $query) {
                return $query->whereIn('nama',['PNS','Pegawai Tetap Non ASN']);
            })->where('proses','=',$tanggal)->orderBy('tmt','desc')->get();

            foreach($mutasi as $key=>$m) {
                // Get tunjangan profesi terakhir
                $mutasi[$key]->tunjangan_profesi = $m->pegawai->tunjangan_profesi()->whereHas('angkatan', function(Builder $query) {
                    return $query->whereHas('jenis', function(Builder $query) {
                        return $query->where('nama','!=','Kehormatan Profesor');
                    });
                })->first();
            }
        }

        // View
        return view('admin/mutasi/index', [
            'mutasi' => $mutasi,
            'new' => $new,
            'jenis' => $jenis,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    /**
     * Show the form for creating/updating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function form(Request $request, $id)
    {
        // Get pegawai
        $pegawai = Pegawai::findOrFail($id);

        // Get mutasi
        $mutasi = Mutasi::find($request->query('mutasi'));
        if(!$mutasi) $mutasi = $pegawai->mutasi()->first();

        // Get SK remun
        $sk = SK::whereHas('jenis', function(Builder $query) {
            return $query->where('nama','=','Remunerasi Gaji');
        })->orderBy('tanggal','desc')->first();

        // Get SK Gaji Pokok PNS
        $sk_gapok_pns = SK::whereHas('jenis', function(Builder $query) {
            return $query->where('nama','=','Gaji Pokok PNS');
        })->orderBy('tanggal','desc')->get();

        // Get jabatan remun
        if($mutasi->jenis->remun == 0) {
            foreach($mutasi->detail as $key=>$d) {
                // Get grup
                $grup = $d->jabatan->grup()->first();
                $mutasi->detail[$key]->jabatan_remun = $grup->jabatan()->where('sk_id','=',$sk->id)->first() ? $grup->jabatan()->where('sk_id','=',$sk->id)->first()->id : 0;
            }
        }

        // Get jenis mutasi
        $jenis_mutasi = JenisMutasi::orderBy('num_order','asc')->get();

        // Get golru
        $golru = Golru::all();

        // Get gaji pokok
        if($pegawai->status_kepegawaian->golru == 1)
            $gaji_pokok = Golru::find($pegawai->golru_id)->gaji_pokok()->where('sk_id','=',$mutasi->gaji_pokok->sk_id)->get();
        else
            $gaji_pokok = [];

        // Get jabatan
        $jabatan = Jabatan::where('sk_id','=',$sk->id)->groupBy('grup_id')->orderBy('nama','asc')->get();

        // Get unit
        $unit = Unit::orderBy('num_order','asc')->get();

        // Get pejabat
        $pejabat = Pejabat::orderBy('num_order','asc')->get();

        // Get angkatan
        $angkatan = Angkatan::whereHas('jenis', function(Builder $query) {
            return $query->whereIn('nama',['Kehormatan Profesor', 'Profesi GB', 'Profesi Non GB']);
        })->orderBy('jenis_id','asc')->orderBy('nama','asc')->get();

        // View
        return view('admin/mutasi/form', [
            'pegawai' => $pegawai,
            'mutasi' => $mutasi,
            'sk' => $sk,
            'sk_gapok_pns' => $sk_gapok_pns,
            'jenis_mutasi' => $jenis_mutasi,
            'golru' => $golru,
            'gaji_pokok' => $gaji_pokok,
            'jabatan' => $jabatan,
            'unit' => $unit,
            'pejabat' => $pejabat,
            'angkatan' => $angkatan
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get jenis mutasi
        $jenis_mutasi = JenisMutasi::find($request->jenis_mutasi);
        if(!$jenis_mutasi)
            return redirect()->back()->withErrors([
                'jenis_mutasi' => 'Wajib diisi!'
            ])->withInput();

        // Get pegawai
        $pegawai = Pegawai::find($request->pegawai_id);

        // Get mutasi
        $mutasi = Mutasi::find($request->id);

        // Validation
        $validator = Validator::make($request->all(), [
            // 'uraian' => $jenis_mutasi->nama == 'Mutasi Jabatan' ? 'required' : '',
            'jenis_mutasi' => 'required',
            'golru' => $pegawai->status_kepegawaian->golru == 1 ? 'required' : '',
            'gaji_pokok' => $pegawai->status_kepegawaian->golru == 1 ? 'required' : '',
            'tmt' => ($mutasi && $mutasi->kolektif == 1) ? '' : 'required',
            'no_sk' => ($jenis_mutasi->perubahan == 1) ? 'required' : '',
            'tanggal_sk' => ($jenis_mutasi->perubahan == 1) ? 'required' : '',
            'mk_tahun' => ($jenis_mutasi->perubahan == 1) ? 'required' : '',
            'mk_bulan' => ($jenis_mutasi->perubahan == 1) ? 'required' : '',
            'pejabat' => ($jenis_mutasi->perubahan == 1) ? 'required' : '',
            'angkatan' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1 && $jenis_mutasi->perubahan == 0) ? 'required' : '',
            'nama_supplier' => ($jenis_mutasi->status == 1 && $jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1 && $jenis_mutasi->perubahan == 0) ? 'required' : '',
            'nomor_rekening' => ($jenis_mutasi->status == 1 && $jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1 && $jenis_mutasi->perubahan == 0) ? 'required' : '',
            'nama_rekening' => ($jenis_mutasi->status == 1 && $jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1 && $jenis_mutasi->perubahan == 0) ? 'required' : '',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Jika mutasi jabatan
            if($jenis_mutasi->nama == 'Mutasi Jabatan') {
                $jabatan_tertinggi = Jabatan::find($request->jabatan_id[0]);
                $index = 0;
                if(count($request->jabatan_id) > 1) {
                    foreach($request->jabatan_id as $key=>$jabatan_id) {
                        // Get jabatan
                        $jabatan = Jabatan::find($jabatan_id);

                        // Compare
                        if($jabatan_tertinggi->jabatan_dasar->nilai * $jabatan_tertinggi->jabatan_dasar->koefisien * $jabatan_tertinggi->jabatan_dasar->pir < $jabatan->jabatan_dasar->nilai * $jabatan->jabatan_dasar->koefisien * $jabatan->jabatan_dasar->pir) {
                            $jabatan_tertinggi = $jabatan;
                            $index = $key;
                        }
                    }
                }

                // Get unit pada jabatan tertinggi
                $unit = Unit::find($request->unit_id[$index]);

                // Get referensi
                $referensi = Referensi::where('sk_id','=',$request->sk_id)->where('jabatan_dasar_id','=',$jabatan_tertinggi->jabatan_dasar_id)->where('layer_id','=',$unit->layer_id)->first();

                // Simpan / update mutasi
                if(!$mutasi || $request->id == 0) $mutasi = new Mutasi;
                $mutasi->pegawai_id = $request->pegawai_id;
                $mutasi->sk_id = $request->sk_id;
                $mutasi->jenis_id = $request->jenis_mutasi;
                $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                $mutasi->golru_id = $request->golru;
                $mutasi->gaji_pokok_id = $request->gaji_pokok;
                $mutasi->bulan = $request->id == 0 ? 0 : $mutasi->bulan;
                $mutasi->tahun = $request->id == 0 ? 0 : $mutasi->tahun;
                $mutasi->tmt = $request->tmt != '' ? DateTimeExt::change($request->tmt) : null;
                $mutasi->remun_penerimaan = mround(($pegawai->status_kepegawaian->persentase / 100) * $referensi->remun_standar, 1);
                $mutasi->remun_gaji = mround((30 / 100) * $mutasi->remun_penerimaan, 1);
                $mutasi->remun_insentif = mround((70 / 100) * $mutasi->remun_penerimaan, 1);
                $mutasi->kolektif = $request->id == 0 ? 0 : $mutasi->kolektif;
                $mutasi->save();

                // Delete mutasi detail
                if($request->id == 0) {
                    foreach($mutasi->detail()->pluck('id')->toArray() as $detail_id) {
                        if(!in_array($detail_id, array_filter($request->detail_id))) {
                            $mutasi_detail = MutasiDetail::find($detail_id);
                            $mutasi_detail->delete();
                        }
                    }
                }

                foreach($request->jabatan_id as $key=>$jabatan_id) {
                    // Get jabatan dan unit
                    $jabatan_mutasi = Jabatan::find($jabatan_id);
                    $unit_mutasi = Unit::find($request->unit_id[$key]);

                    // Simpan mutasi detail
                    $mutasi_detail = MutasiDetail::where('mutasi_id','=',$mutasi->id)->where('jabatan_id','=',$jabatan_mutasi->id)->where('unit_id','=',$unit_mutasi->id)->first();
                    if(!$mutasi_detail) $mutasi_detail = new MutasiDetail;
                    $mutasi_detail->mutasi_id = $mutasi->id;
                    $mutasi_detail->jabatan_dasar_id = $jabatan_mutasi->jabatan_dasar_id;
                    $mutasi_detail->jabatan_id = $jabatan_mutasi->id;
                    $mutasi_detail->unit_id = $unit_mutasi->id;
                    $mutasi_detail->layer_id = $unit_mutasi->layer_id;
                    $mutasi_detail->status = $key == $index ? 1 : 0;
                    $mutasi_detail->save();
					
					// Update jabfung_id dan unit_id pada pegawai
					if($jabatan_mutasi->jenis_id == 1) {
						$pegawai = Pegawai::find($request->pegawai_id);
						$pegawai->jabfung_id = $jabatan_mutasi->grup_id;
						$pegawai->unit_id = $unit_mutasi->id;
						$pegawai->save();
					}
					
					// Update jabstruk_id pada pegawai
					if($jabatan_mutasi->jenis_id == 2) {
						$pegawai = Pegawai::find($request->pegawai_id);
						$pegawai->jabstruk_id = $jabatan_mutasi->grup_id;
						$pegawai->save();
					}
                }
            }
            // Jika mutasi CPNS ke PNS
            elseif($jenis_mutasi->nama == 'Mutasi CPNS ke PNS') {
                // Get status kepegawaian
                $status_kepegawaian = StatusKepegawaian::find(1);

                // Get mutasi pegawai
                $m = $pegawai->mutasi()->where('jenis_id','=',1)->latest('tahun')->latest('bulan')->latest('tmt')->first();
                $mutasi_detail = $m->detail()->where('status','=',1)->first();

                // Get referensi
                $referensi = Referensi::where('sk_id','=',$request->sk_id)->where('jabatan_dasar_id','=',$mutasi_detail->jabatan_dasar_id)->where('layer_id','=',$mutasi_detail->layer_id)->first();

                // Simpan / update mutasi
                if(!$mutasi || $request->id == 0) $mutasi = new Mutasi;
                $mutasi->pegawai_id = $request->pegawai_id;
                $mutasi->sk_id = $request->sk_id;
                $mutasi->jenis_id = $request->jenis_mutasi;
                $mutasi->status_kepeg_id = $status_kepegawaian->id;
                $mutasi->golru_id = $request->golru;
                $mutasi->gaji_pokok_id = $request->gaji_pokok;
                $mutasi->bulan = $request->id == 0 ? 0 : $mutasi->bulan;
                $mutasi->tahun = $request->id == 0 ? 0 : $mutasi->tahun;
                $mutasi->tmt = $request->tmt != '' ? DateTimeExt::change($request->tmt) : null;
                $mutasi->remun_penerimaan = mround(($status_kepegawaian->persentase / 100) * $referensi->remun_standar, 1);
                $mutasi->remun_gaji = mround((30 / 100) * $mutasi->remun_penerimaan, 1);
                $mutasi->remun_insentif = mround((70 / 100) * $mutasi->remun_penerimaan, 1);
                $mutasi->save();
            }
            // Jika remun = 0, serdos = 1, perubahan = 0
            elseif($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1 && $jenis_mutasi->perubahan == 0) {
                // Get golru
                $golru = Golru::find($request->golru);

                // Get gaji pokok
                $gaji_pokok = GajiPokok::find($request->gaji_pokok);

                // Simpan / update mutasi
                if(!$mutasi || $request->id == 0) $mutasi = new Mutasi;
                $mutasi->pegawai_id = $request->pegawai_id;
                $mutasi->sk_id = $request->sk_id;
                $mutasi->jenis_id = $request->jenis_mutasi;
                $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                $mutasi->golru_id = $golru->id;
                $mutasi->gaji_pokok_id = $request->gaji_pokok;
                $mutasi->bulan = date('n', strtotime(DateTimeExt::change($request->tmt)));
                $mutasi->tahun = date('Y', strtotime(DateTimeExt::change($request->tmt)));
                $mutasi->tmt = $request->tmt != '' ? DateTimeExt::change($request->tmt) : null;
                $mutasi->remun_penerimaan = 0;
                $mutasi->remun_gaji = 0;
                $mutasi->remun_insentif = 0;
                $mutasi->save();

                // Update pegawai
                $pegawai->angkatan_id = $request->angkatan;
                $pegawai->nama_supplier = $request->nama_supplier;
                $pegawai->norek_btn = $request->nomor_rekening;
                $pegawai->nama_btn = $request->nama_rekening;
                $pegawai->save();

                // Get mutasi pegawai
                if($request->id == 0) {
                    $m = $pegawai->mutasi()->where('jenis_id','=',1)->first();
                    if($m) {
                        foreach($m->detail as $d) {
                            // Simpan mutasi detail
                            $detail = new MutasiDetail;
                            $detail->mutasi_id = $mutasi->id;
                            $detail->jabatan_id = $d->jabatan_id;
                            $detail->jabatan_dasar_id = $d->jabatan_dasar_id;
                            $detail->unit_id = $d->unit_id;
                            $detail->layer_id = $d->layer_id;
                            $detail->status = $d->status;
                            $detail->save();
                        }
                    }
                }
            }
            // Jika perubahan = 1
            elseif($jenis_mutasi->perubahan == 1) {
                // Get golru
                $golru = Golru::find($request->golru);

                // Get gaji pokok
                $gaji_pokok = GajiPokok::find($request->gaji_pokok);

                // Simpan / update mutasi
                if(!$mutasi || $request->id == 0) $mutasi = new Mutasi;
                $mutasi->pegawai_id = $request->pegawai_id;
                $mutasi->sk_id = $request->sk_id;
                $mutasi->jenis_id = $request->jenis_mutasi;
                $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                $mutasi->golru_id = $golru->id;
                $mutasi->gaji_pokok_id = $request->gaji_pokok;
                $mutasi->bulan = date('n', strtotime(DateTimeExt::change($request->tmt)));
                $mutasi->tahun = date('Y', strtotime(DateTimeExt::change($request->tmt)));
                $mutasi->tmt = $request->tmt != '' ? DateTimeExt::change($request->tmt) : null;
                $mutasi->remun_penerimaan = 0;
                $mutasi->remun_gaji = 0;
                $mutasi->remun_insentif = 0;
                $mutasi->save();

                // Set TMT golongan
                $tmt_golongan = date('Y-m-d', strtotime("-".$request->mk_tahun." year", strtotime($mutasi->tmt)));
                $tmt_golongan = date('Y-m-d', strtotime("-".$request->mk_bulan." month", strtotime($tmt_golongan)));

                // Simpan pegawai
                $pegawai = Pegawai::find($request->pegawai_id);
                $pegawai->golongan_id = $golru->golongan_id;
                $pegawai->golru_id = $golru->id;
                $pegawai->tmt_golongan = $tmt_golongan;
                $pegawai->save();

                // Get mutasi pegawai
                if($request->id == 0) {
                    $m = $pegawai->mutasi()->where('jenis_id','=',1)->first();
                    if($m) {
                        foreach($m->detail as $d) {
                            // Simpan mutasi detail
                            $detail = new MutasiDetail;
                            $detail->mutasi_id = $mutasi->id;
                            $detail->jabatan_id = $d->jabatan_id;
                            $detail->jabatan_dasar_id = $d->jabatan_dasar_id;
                            $detail->unit_id = $d->unit_id;
                            $detail->layer_id = $d->layer_id;
                            $detail->angkatan_id = 0;
                            $detail->status = $d->status;
                            $detail->save();
                        }
                    }
                }

                // Simpan perubahan
                $perubahan = $mutasi->perubahan;
                if(!$perubahan) $perubahan = new Perubahan;
                $perubahan->mutasi_id = $mutasi->id;
                $perubahan->sk_id = $gaji_pokok->sk_id;
                $perubahan->pejabat_id = $request->pejabat;
                $perubahan->no_sk = $request->no_sk;
                $perubahan->tanggal_sk = DateTimeExt::change($request->tanggal_sk);
                $perubahan->mk_tahun = $request->mk_tahun;
                $perubahan->mk_bulan = $request->mk_bulan;
                $perubahan->tmt = DateTimeExt::change($request->tmt);
                $perubahan->save();
            }
            // Jika lainnya
            else {
                // Simpan / update mutasi
                if(!$mutasi || $request->id == 0) $mutasi = new Mutasi;
                $mutasi->pegawai_id = $request->pegawai_id;
                $mutasi->sk_id = $request->sk_id;
                $mutasi->jenis_id = $request->jenis_mutasi;
                $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                $mutasi->golru_id = $request->golru;
                $mutasi->gaji_pokok_id = $request->gaji_pokok;
                $mutasi->bulan = $request->id == 0 ? 0 : $mutasi->bulan;
                $mutasi->tahun = $request->id == 0 ? 0 : $mutasi->tahun;
                $mutasi->tmt = $request->tmt != '' ? DateTimeExt::change($request->tmt) : null;
                $mutasi->remun_penerimaan = 0;
                $mutasi->remun_gaji = 0;
                $mutasi->remun_insentif = 0;
                $mutasi->save();

                // Get mutasi pegawai
                if($request->id == 0) {
                    $m = $pegawai->mutasi()->where('jenis_id','=',1)->first();
                    if($m) {
                        foreach($m->detail as $d) {
                            // Simpan mutasi detail
                            $detail = new MutasiDetail;
                            $detail->mutasi_id = $mutasi->id;
                            $detail->jabatan_id = $d->jabatan_id;
                            $detail->jabatan_dasar_id = $d->jabatan_dasar_id;
                            $detail->unit_id = $d->unit_id;
                            $detail->layer_id = $d->layer_id;
                            $detail->status = $d->status;
                            $detail->save();
                        }
                    }
                }

                // Jika sanksi
                if($jenis_mutasi->nama != 'Sanksi') {
                    // Simpan pegawai
                    $pegawai = Pegawai::find($request->pegawai_id);
                    $pegawai->status_kerja_id = $request->jenis_mutasi;
                    $pegawai->tmt_non_aktif = DateTimeExt::change($request->tmt);
                    $pegawai->save();
                }
            }

            // Redirect
            return redirect()->route('admin.pegawai.detail', ['id' => $request->pegawai_id, 'mutasi' => 1])->with(['message' => 'Berhasil menambah data.']);
        }
    }
    
    /**
     * Import from Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

		$array = Excel::toArray(new ByStartRowImport(2), public_path('storage/Remun_Gaji_2024_02.xlsx'));
        $bulan = 2;
        $tahun = 2024;
        $sk = 12;

        // NB: Gaji pokok PPPK dikosongi dulu
        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $key=>$data) {
                if($data[0] != null) {
                    // Get jenis mutasi
                    $jenis_mutasi = JenisMutasi::where('nama','=',$data[12])->first();

                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[1])->orWhere('npu','=',$data[1])->first();
                    $pegawai->jenis = $data[13];
                    $pegawai->save();

                    // Get mutasi sebelum
                    $mutasi_sebelum = $pegawai->mutasi()->where('tahun','!=',0)->where('bulan','!=',0)->first();

                    // Cek mutasi
                    $mutasi = Mutasi::where('pegawai_id','=',$pegawai->id)->where('tmt','=',DateTimeExt::change($data[11]))->first();
                    if(!$mutasi) $mutasi = new Mutasi;

                    // Get unit
                    $unit = Unit::where('nama','=',$data[3])->first();

                    if($jenis_mutasi->status == 1) {
                        // Get jabatan
                        if(!in_array($data[4], ['Koordinator Program Studi A','Koordinator Program Studi B','Koordinator Program Studi C']))
                            $jabatan = Jabatan::where('sk_id','=',$sk)->where('nama','=',$data[4])->where('sub','=',$data[5])->first();
                        else
                            $jabatan = Jabatan::where('sk_id','=',$sk)->where('nama','=',$data[4])->where('sub','=','-')->first();

                        // Get referensi
                        $referensi = Referensi::where('sk_id','=',$sk)->where('jabatan_dasar_id','=',$jabatan->jabatan_dasar_id)->where('layer_id','=',($data[13] == 1 ? $unit->layer_id : 1))->first();
                    }

                    // Simpan data mutasi
                    $mutasi->pegawai_id = $pegawai->id;
                    $mutasi->sk_id = $sk;
                    $mutasi->jenis_id = $jenis_mutasi->id;
                    $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                    $mutasi->golru_id = $mutasi_sebelum ? $mutasi_sebelum->golru_id : null;
                    $mutasi->gaji_pokok_id = $mutasi_sebelum ? $mutasi_sebelum->gaji_pokok_id : null;
                    $mutasi->bulan = 0;
                    $mutasi->tahun = 0;
                    $mutasi->uraian = 'Perubahan Februari 2024';
                    $mutasi->tmt = DateTimeExt::change($data[11]);
                    $mutasi->remun_penerimaan = $jenis_mutasi->status == 1 ? mround(($pegawai->status_kepegawaian->persentase / 100) * $referensi->remun_standar, 1) : 0;
                    $mutasi->remun_gaji = $jenis_mutasi->status == 1 ? mround((30 / 100) * $mutasi->remun_penerimaan, 1) : 0;
                    $mutasi->remun_insentif = $jenis_mutasi->status == 1 ? mround((70 / 100) * $mutasi->remun_penerimaan, 1) : 0;
                    $mutasi->kolektif = null;
                    $mutasi->num_order = ($key+1);
                    $mutasi->save();

                    if($jenis_mutasi->status == 1) {
                        // Simpan mutasi detail
                        $mutasi_detail = MutasiDetail::where('mutasi_id','=',$mutasi->id)->where('jabatan_id','=',$jabatan->id)->first();
                        if(!$mutasi_detail) $mutasi_detail = new MutasiDetail;
                        $mutasi_detail->mutasi_id = $mutasi->id;
                        $mutasi_detail->jabatan_id = $jabatan->id;
                        $mutasi_detail->jabatan_dasar_id = $jabatan->jabatan_dasar_id;
                        $mutasi_detail->unit_id = $unit->id;
                        $mutasi_detail->layer_id = $data[13] == 1 ? $unit->layer_id : 1;
                        $mutasi_detail->angkatan_id = 0;
                        $mutasi_detail->status = 1;
                        $mutasi_detail->save();
                        
                        // Update jabfung_id dan unit_id pada pegawai
                        if($jabatan->jenis_id == 1) {
                            $pegawai->jabfung_id = $jabatan->grup_id;
                            $pegawai->unit_id = $unit->id;
                            $pegawai->save();
                        }
                
                        // Update jabstruk_id pada pegawai
                        if($jabatan->jenis_id == 2) {
                            $pegawai->jabstruk_id = $jabatan->grup_id;
                            $pegawai->save();
                        }

                        // Jika jabatannya adalah struktural, maka otomatis menambahkan jabatan fungsional jika ada
                        if($jabatan->jenis_id == 2) {
                            // Get jabatan fungsional
                            $jabatan_fungsional = $mutasi_sebelum ? $mutasi_sebelum->detail()->whereHas('jabatan', function(Builder $query) {
                                return $query->where('jenis_id','=',1);
                            })->first() : false;
                            if($jabatan_fungsional) {
                                // Simpan mutasi detail
                                $mutasi_detail_jf = MutasiDetail::where('mutasi_id','=',$mutasi->id)->where('jabatan_id','=',$jabatan_fungsional->jabatan->id)->first();
                                if(!$mutasi_detail_jf) $mutasi_detail_jf = new MutasiDetail;
                                $mutasi_detail_jf->mutasi_id = $mutasi->id;
                                $mutasi_detail_jf->jabatan_id = $jabatan_fungsional->jabatan->id;
                                $mutasi_detail_jf->jabatan_dasar_id = $jabatan_fungsional->jabatan->jabatan_dasar_id;
                                $mutasi_detail_jf->unit_id = $jabatan_fungsional->unit->id;
                                $mutasi_detail_jf->layer_id = $jabatan_fungsional->unit->layer_id;
                                $mutasi_detail_jf->angkatan_id = 0;
                                $mutasi_detail_jf->status = 0;
                                $mutasi_detail_jf->save();
                                
                                // Update jabfung_id dan unit_id pada pegawai
                                $pegawai->jabfung_id = $jabatan_fungsional->jabatan->grup_id;
                                $pegawai->unit_id = $jabatan_fungsional->unit->id;
                                $pegawai->save();
                            }
                        }

                        // Simpan koorprodi
                        if(in_array($data[4], ['Koordinator Program Studi A','Koordinator Program Studi B','Koordinator Program Studi C'])) {
                            // Get prodi
                            $prodi = Prodi::where('nama','=',str_replace('Koorprodi ', '', $data[4]))->first();
                            if($prodi) {
                                // Simpan mutasi koorprodi
                                $mutasi_koorprodi = MutasiKoorprodi::where('mutasi_detail_id','=',$mutasi_detail->id)->where('prodi_id','=',$prodi->id)->first();
                                if(!$mutasi_koorprodi) $mutasi_koorprodi = new MutasiKoorprodi;
                                $mutasi_koorprodi->mutasi_detail_id = $mutasi_detail->id;
                                $mutasi_koorprodi->prodi_id = $prodi->id;
                                $mutasi_koorprodi->save();
                            }
                            else {
                                $explode = explode(';', $data[4]);
                                foreach($explode as $e) {
                                    $prodis = Prodi::where('nama','=',str_replace('Koorprodi ', '', $e))->first();
                                    if($prodis) {
                                        // Simpan mutasi koorprodi
                                        $mutasi_koorprodi = MutasiKoorprodi::where('mutasi_detail_id','=',$mutasi_detail->id)->where('prodi_id','=',$prodis->id)->first();
                                        if(!$mutasi_koorprodi) $mutasi_koorprodi = new MutasiKoorprodi;
                                        $mutasi_koorprodi->mutasi_detail_id = $mutasi_detail->id;
                                        $mutasi_koorprodi->prodi_id = $prodis->id;
                                        $mutasi_koorprodi->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            var_dump($error);
            return;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Get mutasi
        $mutasi = Mutasi::findOrFail($request->id);
        $mutasi->delete();

        // Get mutasi detail
        $mutasi_detail = MutasiDetail::where('mutasi_id','=',$mutasi->id)->get();
        if(count($mutasi_detail) > 0)
            MutasiDetail::whereIn('id',$mutasi_detail->pluck('id')->toArray())->delete();

        // Redirect
        return redirect($request->redirect)->with(['message' => 'Berhasil menghapus data.']);
    }

    /**
     * Sync
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sync(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $mutasi = Mutasi::whereHas('pegawai', function(Builder $query) {
            return $query->where('status_kerja_id','=',1);
        })->orderBy('pegawai_id','asc')->orderByRaw("proses IS NULL DESC")->orderBy('proses','desc')->orderBy('tmt','desc')->orderBy('gaji_pokok_id','desc')->get();

        return view('admin/mutasi/sync', [
            'mutasi' => $mutasi
        ]);
    }
}
