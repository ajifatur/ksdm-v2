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
use App\Imports\MutasiImport;
use App\Models\Mutasi;
use App\Models\MutasiDetail;
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
use App\Models\Referensi;
use App\Models\RemunGaji;
use App\Models\SK;
use App\Models\StatusKepegawaian;
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
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');

        // Get mutasi
        $mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
            return $query->where('remun','=',1);
        })->where('bulan','=',$bulan)->where('tahun','=',$tahun)->orderBy('tmt','desc')->get();

        // View
        return view('admin/mutasi/index', [
            'mutasi' => $mutasi,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    /**
     * Mutasi Baru (Remun).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function new(Request $request)
    {
        // Get mutasi
        $mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
            return $query->where('remun','=',1);
        })->where('bulan','=',0)->where('tahun','=',0)->orderBy('tmt','desc')->get();

        // View
        return view('admin/mutasi/new', [
            'mutasi' => $mutasi
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        // Get pegawai
        $pegawai = Pegawai::findOrFail($id);

        // Get SK
        $sk = SK::whereHas('jenis', function(Builder $query) {
            return $query->where('nama','=','Remun Gaji');
        })->where('status','=',1)->first();

        // Get mutasi
        $mutasi = $pegawai->mutasi()->first();

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

        // Get status kepegawaian
        $status_kepegawaian = StatusKepegawaian::get();

        // Get golru
        $golru = Golru::all();

        // Get gaji pokok
        if($pegawai->status_kepegawaian->golru == 1)
            $gaji_pokok = Golru::find($pegawai->golru_id)->gaji_pokok;
        else
            $gaji_pokok = [];

        // Get jabatan
        $jabatan = Jabatan::where('sk_id','=',$sk->id)->orderBy('nama','asc')->get();

        // Get unit
        $unit = Unit::where('nama','!=','-')->orderBy('num_order','asc')->get();

        // Get pejabat
        $pejabat = Pejabat::orderBy('num_order','asc')->get();

        // Get angkatan
        $angkatan = [];
        for($i=1; $i<=3; $i++) {
            $angkatan[$i]['data'] = Angkatan::where('jenis_id','=',$i)->orderBy('nama','asc')->get();
        }

        // View
        return view('admin/mutasi/create', [
            'pegawai' => $pegawai,
            'sk' => $sk,
            'mutasi' => $mutasi,
            'jenis_mutasi' => $jenis_mutasi,
            'status_kepegawaian' => $status_kepegawaian,
            'jabatan' => $jabatan,
            'unit' => $unit,
            'golru' => $golru,
            'gaji_pokok' => $gaji_pokok,
            'pejabat' => $pejabat,
            'angkatan' => $angkatan,
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
        // Get pegawai
        $pegawai = Pegawai::find($request->pegawai_id);

        // Get jenis mutasi
        $jenis_mutasi = JenisMutasi::find($request->jenis_mutasi);

        // Get mutasi
        $mutasi = Mutasi::find($request->id);

        // Validation
        $validator = Validator::make($request->all(), [
            'uraian' => $jenis_mutasi->nama == 'Mutasi Jabatan' ? 'required' : '',
            'jenis_mutasi' => 'required',
            'golru' => $pegawai->status_kepegawaian->golru == 1 ? 'required' : '',
            'gaji_pokok' => $pegawai->status_kepegawaian->golru == 1 ? 'required' : '',
            'tmt' => 'required',
            'no_sk' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 0) ? 'required' : '',
            'tanggal_sk' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 0) ? 'required' : '',
            'mk_tahun' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 0) ? 'required' : '',
            'mk_bulan' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 0) ? 'required' : '',
            'pejabat' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 0) ? 'required' : '',
            'angkatan' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1) ? 'required' : '',
            'nama_supplier' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1) ? 'required' : '',
            'nomor_rekening' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1) ? 'required' : '',
            'nama_rekening' => ($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1) ? 'required' : '',
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
                $mutasi->uraian = $request->uraian;
                $mutasi->tmt = $request->tmt != '' ? DateTimeExt::change($request->tmt) : null;
                $mutasi->remun_penerimaan = mround(($pegawai->status_kepegawaian->persentase / 100) * $referensi->remun_standar, 1);
                $mutasi->remun_gaji = mround((30 / 100) * $mutasi->remun_penerimaan, 1);
                $mutasi->remun_insentif = mround((70 / 100) * $mutasi->remun_penerimaan, 1);
                $mutasi->kolektif = 0;
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
                    $mutasi_detail = new MutasiDetail;
                    $mutasi_detail->mutasi_id = $mutasi->id;
                    $mutasi_detail->jabatan_dasar_id = $jabatan_mutasi->jabatan_dasar_id;
                    $mutasi_detail->jabatan_id = $jabatan_mutasi->id;
                    $mutasi_detail->unit_id = $unit_mutasi->id;
                    $mutasi_detail->layer_id = $unit_mutasi->layer_id;
                    $mutasi_detail->angkatan_id = 0;
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
                $mutasi->uraian = $jenis_mutasi->nama;
                $mutasi->tmt = $request->tmt != '' ? DateTimeExt::change($request->tmt) : null;
                $mutasi->remun_penerimaan = mround(($status_kepegawaian->persentase / 100) * $referensi->remun_standar, 1);
                $mutasi->remun_gaji = mround((30 / 100) * $mutasi->remun_penerimaan, 1);
                $mutasi->remun_insentif = mround((70 / 100) * $mutasi->remun_penerimaan, 1);
                $mutasi->save();
            }
            // Jika remun = 0 dan serdos = 1
            elseif($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 1) {
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
                $mutasi->uraian = $jenis_mutasi->nama.' '.$golru->nama.' '.$request->mk_tahun.' tahun '.$request->mk_bulan.' bulan';
                $mutasi->tmt = $request->tmt != '' ? DateTimeExt::change($request->tmt) : null;
                $mutasi->remun_penerimaan = 0;
                $mutasi->remun_gaji = 0;
                $mutasi->remun_insentif = 0;
                $mutasi->save();

                // Update pegawai
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
                            $detail->angkatan_id = $request->angkatan;
                            $detail->status = $d->status;
                            $detail->save();
                        }
                    }
                }
            }
            // Jika remun = 0 dan serdos = 0
            elseif($jenis_mutasi->remun == 0 && $jenis_mutasi->serdos == 0) {
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
                $mutasi->uraian = $jenis_mutasi->nama.' '.$golru->nama.' '.$request->mk_tahun.' tahun '.$request->mk_bulan.' bulan';
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
                $mutasi->uraian = $jenis_mutasi->nama;
                $mutasi->tmt = $request->tmt != '' ? DateTimeExt::change($request->tmt) : null;
                $mutasi->remun_penerimaan = 0;
                $mutasi->remun_gaji = 0;
                $mutasi->remun_insentif = 0;
                $mutasi->save();

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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @param  int  $mutasi_id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $mutasi_id)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Get mutasi
        $mutasi = Mutasi::findOrFail($mutasi_id);

        // Get pegawai
        $pegawai = Pegawai::findOrFail($id);

        // Get SK
        $sk = SK::find($mutasi->sk_id);

        // Get jenis mutasi
        $jenis_mutasi = JenisMutasi::orderBy('num_order','asc')->get();

        // Get jabatan remun
        if($mutasi->jenis->remun == 0) {
            foreach($mutasi->detail as $key=>$d) {
                // Get grup
                $grup = $d->jabatan->grup()->first();
                $mutasi->detail[$key]->jabatan_remun = $grup->jabatan()->where('sk_id','=',$sk->id)->first() ? $grup->jabatan()->where('sk_id','=',$sk->id)->first()->id : 0;
            }
        }

        // Get status kepegawaian
        $status_kepegawaian = StatusKepegawaian::get();

        // Get golru
        $golru = Golru::all();

        // Get gaji pokok
        if($pegawai->status_kepegawaian->golru == 1)
        $gaji_pokok = Golru::find($pegawai->golru_id)->gaji_pokok;
    else
        $gaji_pokok = [];

        // Get jabatan
        $jabatan = $sk ? Jabatan::where('sk_id','=',$sk->id)->orderBy('nama','asc')->get() : Jabatan::orderBy('nama','asc')->get();

        // Get unit
        $unit = Unit::where('nama','!=','-')->orderBy('num_order','asc')->get();

        // Get pejabat
        $pejabat = Pejabat::orderBy('num_order','asc')->get();

        // Get angkatan
        $angkatan = [];
        for($i=1; $i<=3; $i++) {
            $angkatan[$i]['data'] = Angkatan::where('jenis_id','=',$i)->orderBy('nama','asc')->get();
        }

        // View
        return view('admin/mutasi/edit', [
            'pegawai' => $pegawai,
            'sk' => $sk,
            'mutasi' => $mutasi,
            'jenis_mutasi' => $jenis_mutasi,
            'jabatan' => $jabatan,
            'unit' => $unit,
            'status_kepegawaian' => $status_kepegawaian,
            'golru' => $golru,
            'gaji_pokok' => $gaji_pokok,
            'pejabat' => $pejabat,
            'angkatan' => $angkatan,
        ]);
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

        // Set default values
        $bulan = 10;
        $tahun = 2023;
        $uraian = "Perubahan Oktober 2023";

        // Get SK
        $sk = SK::where('jenis_id','=',1)->where('status','=',1)->first();

		$array = Excel::toArray(new MutasiImport, public_path('assets/spreadsheets/Perubahan_2023_Oktober_MutJab.xlsx'));

        // NB: Gaji pokok PPPK dikosongi dulu
        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',trim($data[1]))->first();
                    if(!$pegawai) {
                        // Get status kepegawaian
                        $status_kepegawaian = StatusKepegawaian::where('nama','=',$data[2])->first();

                        // Get golongan
                        $golongan = Golongan::where('nama','=',$data[4])->first();

                        // Set jenis / kategori
                        if($data[3] == 'DOSEN') $jenis = 1;
                        elseif($data[3] == 'TENDIK') $jenis = 2;
                        else $jenis = 0;

                        $pegawai = new Pegawai;
                        $pegawai->status_kepeg_id = $status_kepegawaian->id;
                        $pegawai->status_kerja_id = 1;
                        $pegawai->golongan_id = $golongan->id;
                        $pegawai->golru_id = null;
                        $pegawai->jabfung_id = null;
                        $pegawai->jabstruk_id = null;
                        $pegawai->unit_id = null;
                        $pegawai->nip = trim($data[1]);
                        $pegawai->jenis = $jenis;
                        $pegawai->nama = $data[0];
                        $pegawai->gelar_depan = '';
                        $pegawai->gelar_belakang = '';
                        $pegawai->tmt_cpns = null;
                        $pegawai->tmt_golongan = null;
                        $pegawai->tmt_non_aktif = null;
                        $pegawai->save();
                    }

                    if($data[9] == 'Mutasi Jabatan') {
                        // Get mutasi sebelum
                        $mutasi_sebelum = Mutasi::where('pegawai_id','=',$pegawai->id)->where('bulan','!=',0)->where('tahun','!=',0)->orderBy('tahun','desc')->orderBy('bulan','desc')->first();

                        // Get jabatan
                        $jabatan = Jabatan::where('sk_id','=',$sk->id)->where('nama','=',$data[6])->where('sub','=',$data[7])->first();
            
                        // Get unit
                        $unit = Unit::where('nama','=',$data[5])->first();
            
                        if($jabatan) {
                            // Get referensi
                            $referensi = Referensi::where('sk_id','=',$sk->id)->where('jabatan_dasar_id','=',$jabatan->jabatan_dasar_id)->where('layer_id','=',$unit->layer_id)->first();

                            // Get remun bulan sebelumnya
                            $remun_gaji = RemunGaji::where('pegawai_id','=',$pegawai->id)->where('bulan','=',$bulan-1)->where('tahun','=',$tahun)->first();

                            // Simpan mutasi
                            $mutasi = Mutasi::where('pegawai_id','=',$pegawai->id)->where('tmt','=',DateTimeExt::change($data[8]))->first();
                            if(!$mutasi) $mutasi = new Mutasi;
                            $mutasi->pegawai_id = $pegawai->id;
                            $mutasi->sk_id = $sk->id;
                            $mutasi->jenis_id = 1;
                            $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                            $mutasi->golru_id = $remun_gaji ? $remun_gaji->golru_id : null;
                            $mutasi->gaji_pokok_id = $mutasi_sebelum ? $mutasi_sebelum->gaji_pokok_id : null;
                            $mutasi->bulan = 0;
                            $mutasi->tahun = 0;
                            $mutasi->uraian = $uraian;
                            $mutasi->tmt = DateTimeExt::change($data[8]);
                            $mutasi->remun_penerimaan = $remun_gaji ? mround(($remun_gaji->status_kepegawaian->persentase / 100) * $referensi->remun_standar, 1) : mround(($pegawai->status_kepegawaian->persentase / 100) * $referensi->remun_standar, 1);
                            $mutasi->remun_gaji = mround((30 / 100) * $mutasi->remun_penerimaan, 1);
                            $mutasi->remun_insentif = mround((70 / 100) * $mutasi->remun_penerimaan, 1);
                            $mutasi->save();

                            // Simpan mutasi detail
                            $mutasi_detail = MutasiDetail::where('mutasi_id','=',$mutasi->id)->where('jabatan_id','=',$jabatan->id)->first();
                            if(!$mutasi_detail) $mutasi_detail = new MutasiDetail;
                            $mutasi_detail->mutasi_id = $mutasi->id;
                            $mutasi_detail->jabatan_id = $jabatan->id;
                            $mutasi_detail->jabatan_dasar_id = $jabatan->jabatan_dasar_id;
                            $mutasi_detail->unit_id = $unit->id;
                            $mutasi_detail->layer_id = $unit->layer_id;
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
                                    $mutasi_detail_jf->status = 0;
                                    $mutasi_detail_jf->save();
									
									// Update jabfung_id dan unit_id pada pegawai
									$pegawai->jabfung_id = $jabatan_fungsional->jabatan->grup_id;
									$pegawai->unit_id = $jabatan_fungsional->unit->id;
									$pegawai->save();
                                }
                            }
                        }
                        else array_push($error, $data[0]);
                    }
                    else {
                        // Get jenis mutasi
                        $jenis_mutasi = JenisMutasi::where('nama','=',$data[9])->first();
        
                        // Simpan mutasi
                        $mutasi = Mutasi::where('pegawai_id','=',$pegawai->id)->where('tmt','=',DateTimeExt::change($data[8]))->first();
                        if(!$mutasi) $mutasi = new Mutasi;
                        $mutasi->pegawai_id = $pegawai->id;
                        $mutasi->sk_id = $sk->id;
                        $mutasi->jenis_id = $jenis_mutasi->id;
                        $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                        $mutasi->golru_id = null;
                        $mutasi->gaji_pokok_id = null;
                        $mutasi->bulan = 0;
                        $mutasi->tahun = 0;
                        $mutasi->uraian = $uraian;
                        $mutasi->tmt = DateTimeExt::change($data[8]);
                        $mutasi->remun_penerimaan = 0;
                        $mutasi->remun_gaji = 0;
                        $mutasi->remun_insentif = 0;
                        $mutasi->save();
                    }
                }
            }
        }

        // Redirect
        return redirect()->route('admin.mutasi.new')->with(['message' => 'Berhasil mengimport data. Error : '.(count($error) > 0 ? implode(', ', $error) : '-')]);
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
     * Check.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function check(Request $request)
    {	
		// Get mutasi
		$mutasi = Mutasi::whereHas('pegawai', function(Builder $query) {
			return $query->where('status_kerja_id','=',1)->whereIn('status_kepeg_id',[1,2]);
		})->orderBy('pegawai_id','asc')->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
		
		// View
        return view('admin/mutasi/check', [
            'mutasi' => $mutasi
        ]);
	}
    
    /**
     * Import Peralihan BLU ke Pegawai Tetap
     *
     * @return \Illuminate\Http\Response
     */
    public function importBLU(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Set default values
        $bulan = 10;
        $tahun = 2023;
        $uraian = "Peralihan BLU ke Pegawai Tetap PTNBH";

        // Get SK
        $sk = SK::where('jenis_id','=',1)->where('status','=',1)->first();

		$array = Excel::toArray(new MutasiImport, public_path('storage/BLU 2023.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',trim($data[1]))->first();
    
                    // Get gaji pokok
                    $gaji_pokok = GajiPokok::where('nama','=',$data[7])->first();
    
                    // Simpan mutasi
                    $mutasi = Mutasi::where('jenis_id','=',13)->where('pegawai_id','=',$pegawai->id)->first();
                    if(!$mutasi) $mutasi = new Mutasi;
                    $mutasi->pegawai_id = $pegawai->id;
                    $mutasi->sk_id = $sk->id;
                    $mutasi->jenis_id = 13;
                    $mutasi->status_kepeg_id = 3;
                    $mutasi->golru_id = $gaji_pokok->golru_id;
                    $mutasi->gaji_pokok_id = $gaji_pokok->id;
                    $mutasi->bulan = 10;
                    $mutasi->tahun = 2023;
                    $mutasi->uraian = $uraian;
                    $mutasi->tmt = '2023-10-01';
                    $mutasi->remun_penerimaan = 0;
                    $mutasi->remun_gaji = 0;
                    $mutasi->remun_insentif = 0;
                    $mutasi->save();
    
                    // Simpan pegawai
                    $pegawai->golongan_id = $gaji_pokok->golru->golongan_id;
                    $pegawai->golru_id = $gaji_pokok->golru_id;
                    $pegawai->npu = $data[2];
                    $pegawai->tmt_golongan = DateTimeExt::change($data[8]);
                    $pegawai->save();
    
                    // Get mutasi pegawai
                    $m = $pegawai->mutasi()->where('jenis_id','=',1)->where('tmt','<=',$mutasi->tmt)->first();
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

                    // Simpan perubahan
                    $perubahan = $mutasi->perubahan;
                    if(!$perubahan) $perubahan = new Perubahan;
                    $perubahan->mutasi_id = $mutasi->id;
                    $perubahan->sk_id = $gaji_pokok->sk_id;
                    $perubahan->pejabat_id = 5;
                    $perubahan->no_sk = $data[9];
                    $perubahan->tanggal_sk = '2023-10-06';
                    $perubahan->mk_tahun = $data[4];
                    $perubahan->mk_bulan = $data[5];
                    $perubahan->tmt = '2023-10-01';
                    $perubahan->save();
                }
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function jabatan(Request $request)
    {
        // Get mutasi
        $mutasi = Mutasi::where('jenis_id','=',1)->orderBy('tmt','desc')->get();

        // View
        return view('admin/mutasi/jabatan', [
            'mutasi' => $mutasi
        ]);
    }
}
