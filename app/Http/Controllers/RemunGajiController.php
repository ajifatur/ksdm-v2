<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\RemunGajiImport;
use App\Models\RemunGaji;
use App\Models\Unit;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\SubJabatan;
use App\Models\Pegawai;
use App\Models\Proses;
use App\Models\LebihKurang;
use App\Models\Mutasi;
use App\Models\MutasiDetail;
use App\Models\SK;
use App\Models\StatusKepegawaian;
use App\Models\Referensi;
use App\Models\Prodi;
use App\Models\KoorprodiRemun;

class RemunGajiController extends Controller
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
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
        $kategori = $request->query('kategori') ?: 0;

        // Get remun gaji
        $remun_gaji = [];
        if($request->query('unit') != null && $request->query('unit') != 0) {
            if($kategori != 0)
                $remun_gaji = RemunGaji::where('unit_id','=',$request->query('unit'))->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->orderBy('remun_gaji','desc')->get();
            else
                $remun_gaji = RemunGaji::where('unit_id','=',$request->query('unit'))->where('bulan','=',$bulan)->where('tahun','=',$tahun)->orderBy('remun_gaji','desc')->get();
        }
        else {
            if($kategori != 0)
                $remun_gaji = RemunGaji::where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->orderBy('remun_gaji','desc')->get();
        }

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->where('nama','!=','-')->orderBy('num_order','asc')->get();

        // Get SK
        $sk = SK::where('jenis_id','=',1)->where('status','=',1)->first();

        // Get jabatan
        $jabatan = Jabatan::where('sk_id','=',$sk->id)->orderBy('nama','asc')->get();

        // View
        return view('admin/remun-gaji/index', [
            'remun_gaji' => $remun_gaji,
            'unit' => $unit,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $kategori,
            'jabatan' => $jabatan,
        ]);
    }

    /**
     * Process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function process(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        if($request->method() == "GET") {
            // Get proses
            $proses = Proses::where('jenis','=',1)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();

            foreach($proses as $key=>$p) {
                // Count mutasi
                $proses[$key]->mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
                    return $query->where('remun','=',1);
                })->where('bulan','=',$p->bulan)->where('tahun','=',$p->tahun)->count();

                // Count pegawai
                $proses[$key]->pegawai = RemunGaji::where('bulan','=',$p->bulan)->where('tahun','=',$p->tahun)->count();

                // Count lebih kurang
                $proses[$key]->lebih_kurang = LebihKurang::where('bulan_proses','=',$p->bulan)->where('tahun_proses','=',$p->tahun)->where('triwulan_proses','=',0)->where('selisih','!=',0)->where('kekurangan','=',0)->count();

                // Sum remun gaji
                $proses[$key]->remun_gaji = RemunGaji::where('bulan','=',$p->bulan)->where('tahun','=',$p->tahun)->sum('remun_gaji') + LebihKurang::where('bulan_proses','=',$p->bulan)->where('tahun_proses','=',$p->tahun)->where('triwulan_proses','=',0)->sum('selisih');
            }

            // View
            return view('admin/remun-gaji/process', [
                'proses' => $proses
            ]);
        }
        elseif($request->method() == "POST") {
            // Set tanggal proses
            $tanggal = $request->tahun.'-'.($request->bulan < 10 ? '0'.$request->bulan : $request->bulan).'-'.$request->tanggal;

            // Set tanggal periode sebelumnya
            $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

            // Get mutasi remun
            $mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();
            if(count($mutasi) == 0) {
                $mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
                    return $query->where('remun','=',1);
                })->where('bulan','=',0)->where('tahun','=',0)->where('tmt','<=',$tanggal)->get();
            }

            // Loop mutasi
            foreach($mutasi as $m) {
                // Jika status kepegawaian aktif
                if($m->jenis->status == 1) {
					// Get jabatan tertinggi dalam mutasi
					$jabatan_tertinggi = $m->detail()->where('status','=',1)->first();

                    // Get remun gaji sebelum
                    // $remun_gaji_sebelum = RemunGaji::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->first();

					// Simpan remun gaji baru
					$new_remun_gaji = RemunGaji::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($tanggal)))->where('tahun','=',date('Y', strtotime($tanggal)))->first();
					if(!$new_remun_gaji) $new_remun_gaji = new RemunGaji;
					$new_remun_gaji->pegawai_id = $m->pegawai->id;
					$new_remun_gaji->golru_id = $m->golru_id;
					$new_remun_gaji->status_kepeg_id = $m->status_kepeg_id;
					$new_remun_gaji->bulan = date('n', strtotime($tanggal));
					$new_remun_gaji->tahun = date('Y', strtotime($tanggal));
					$new_remun_gaji->kategori = $m->pegawai->jenis;
					$new_remun_gaji->jabatan_dasar_id = $jabatan_tertinggi->jabatan_dasar_id;
					$new_remun_gaji->jabatan_id = $jabatan_tertinggi->jabatan_id;
					$new_remun_gaji->unit_id = $jabatan_tertinggi->unit_id;
					$new_remun_gaji->layer_id = $jabatan_tertinggi->layer_id;
					$new_remun_gaji->remun_penerimaan = $m->remun_penerimaan;
					$new_remun_gaji->remun_gaji = $m->remun_gaji;
					$new_remun_gaji->remun_insentif = $m->remun_insentif;
					$new_remun_gaji->save();

					if($m->tmt <= $tanggal_sebelum) {
						// Loop tanggal
						$temp_tanggal = $tanggal_sebelum;
						while($temp_tanggal >= $m->tmt) {
							// Get mutasi sebelum
							$mutasi_sebelum = Mutasi::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($temp_tanggal)))->where('tahun','=',date('Y', strtotime($temp_tanggal)))->first();

							// Get remun gaji bulan sebelumnya
							$rg = RemunGaji::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($temp_tanggal)))->where('tahun','=',date('Y', strtotime($temp_tanggal)))->first();

							// Jika ada mutasi sebelum dan jabatan lebih dari 1
							// if($mutasi_sebelum && $mutasi_sebelum->detail()->count() > 1) {
                            if($mutasi_sebelum && $mutasi_sebelum->detail()->count() >= 1) {
								// Get jabatan tertinggi sebelum
								$jabatan_tertinggi_sebelum = $mutasi_sebelum->detail()->where('status','=',1)->first();

								// Jika jabatan tidak lebih tinggi dari jabatan tertinggi
								if($jabatan_tertinggi_sebelum->jabatan_dasar->grade < $jabatan_tertinggi->jabatan_dasar->grade || ($jabatan_tertinggi_sebelum->jabatan_dasar->grade == $jabatan_tertinggi->jabatan_dasar->grade && $jabatan_tertinggi_sebelum->jabatan_dasar->nilai < $jabatan_tertinggi->jabatan_dasar->nilai)) {
									// Simpan lebih kurang
									$lebih_kurang = LebihKurang::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($temp_tanggal)))->where('tahun','=',date('Y', strtotime($temp_tanggal)))->where('bulan_proses','=',$request->bulan)->where('tahun_proses','=',$request->tahun)->where('triwulan_proses','=',0)->first();
									if(!$lebih_kurang) $lebih_kurang = new LebihKurang;
									$lebih_kurang->pegawai_id = $m->pegawai_id;
									$lebih_kurang->jabatan_terbayar_id = $rg ? $rg->jabatan_id : 0;
									$lebih_kurang->jabatan_seharusnya_id = $jabatan_tertinggi->jabatan_id;
									$lebih_kurang->bulan = date('n', strtotime($temp_tanggal));
									$lebih_kurang->tahun = date('Y', strtotime($temp_tanggal));
									$lebih_kurang->bulan_proses = $request->bulan;
									$lebih_kurang->triwulan_proses = 0;
									$lebih_kurang->tahun_proses = $request->tahun;
									$lebih_kurang->terbayar = $rg ? $rg->remun_gaji : 0;
									$lebih_kurang->seharusnya = $m->remun_gaji;
									$lebih_kurang->selisih = $lebih_kurang->seharusnya - $lebih_kurang->terbayar;
                                    $lebih_kurang->kekurangan = 0;
									$lebih_kurang->save();
								}
							}
							// Jika ada mutasi sebelum dan jabatannya 1
							// Jika tidak ada mutasi sebelum
							// elseif(($mutasi_sebelum && $mutasi_sebelum->detail()->count() == 1) || !$mutasi_sebelum) {
                            elseif(!$mutasi_sebelum) {
								// Simpan lebih kurang
								$lebih_kurang = LebihKurang::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($temp_tanggal)))->where('tahun','=',date('Y', strtotime($temp_tanggal)))->where('bulan_proses','=',$request->bulan)->where('tahun_proses','=',$request->tahun)->where('triwulan_proses','=',0)->first();
								if(!$lebih_kurang) $lebih_kurang = new LebihKurang;
								$lebih_kurang->pegawai_id = $m->pegawai_id;
								$lebih_kurang->jabatan_terbayar_id = $rg ? $rg->jabatan_id : 0;
								$lebih_kurang->jabatan_seharusnya_id = $jabatan_tertinggi->jabatan_id;
								$lebih_kurang->bulan = date('n', strtotime($temp_tanggal));
								$lebih_kurang->tahun = date('Y', strtotime($temp_tanggal));
								$lebih_kurang->bulan_proses = $request->bulan;
                                $lebih_kurang->triwulan_proses = 0;
								$lebih_kurang->tahun_proses = $request->tahun;
								$lebih_kurang->terbayar = $rg ? $rg->remun_gaji : 0;
								$lebih_kurang->seharusnya = $m->remun_gaji;
								$lebih_kurang->selisih = $lebih_kurang->seharusnya - $lebih_kurang->terbayar;
                                $lebih_kurang->kekurangan = 0;
								$lebih_kurang->save();
							}

							// Timpa tanggal
							$temp_tanggal = date('Y-m-d', strtotime("-1 month", strtotime($temp_tanggal)));
						}
					}
                }
                // Jika jenis mutasinya sanksi
                elseif($m->jenis_id == 7) {
					// Simpan remun gaji baru
					$new_remun_gaji = RemunGaji::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($tanggal)))->where('tahun','=',date('Y', strtotime($tanggal)))->first();
					if(!$new_remun_gaji) $new_remun_gaji = new RemunGaji;
					$new_remun_gaji->pegawai_id = $m->pegawai->id;
					$new_remun_gaji->golru_id = $m->golru_id;
					$new_remun_gaji->status_kepeg_id = $m->pegawai->status_kepeg_id;
					$new_remun_gaji->bulan = date('n', strtotime($tanggal));
					$new_remun_gaji->tahun = date('Y', strtotime($tanggal));
					$new_remun_gaji->kategori = $m->pegawai->jenis;
					$new_remun_gaji->jabatan_dasar_id = 0;
					$new_remun_gaji->jabatan_id = 0;
					$new_remun_gaji->unit_id = 0;
					$new_remun_gaji->layer_id = 0;
					$new_remun_gaji->remun_penerimaan = $m->remun_penerimaan;
					$new_remun_gaji->remun_gaji = $m->remun_gaji;
					$new_remun_gaji->remun_insentif = $m->remun_insentif;
					$new_remun_gaji->save();
                }

                // Update mutasi
                $update_mutasi = Mutasi::find($m->id);
                $update_mutasi->bulan = date('n', strtotime($tanggal));
                $update_mutasi->tahun = date('Y', strtotime($tanggal));
                $update_mutasi->save();
            }

            // Get remun gaji tanpa mutasi
            $remun_gaji = RemunGaji::where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->whereNotIn('pegawai_id',$mutasi->pluck('pegawai_id')->toArray())->get();
            foreach($remun_gaji as $r) {
                // Simpan remun gaji baru
                $new_remun_gaji = RemunGaji::where('pegawai_id','=',$r->pegawai_id)->where('bulan','=',date('n', strtotime($tanggal)))->where('tahun','=',date('Y', strtotime($tanggal)))->first();
                if(!$new_remun_gaji) $new_remun_gaji = new RemunGaji;
                $new_remun_gaji->pegawai_id = $r->pegawai->id;
                $new_remun_gaji->golru_id = $r->golru_id;
                $new_remun_gaji->status_kepeg_id = $r->status_kepeg_id;
                $new_remun_gaji->jabatan_dasar_id = $r->jabatan_dasar_id;
                $new_remun_gaji->jabatan_id = $r->jabatan_id;
                $new_remun_gaji->unit_id = $r->unit_id;
                $new_remun_gaji->layer_id = $r->layer_id;
                $new_remun_gaji->bulan = date('n', strtotime($tanggal));
                $new_remun_gaji->tahun = date('Y', strtotime($tanggal));
                $new_remun_gaji->kategori = $r->kategori;
                $new_remun_gaji->remun_penerimaan = $r->remun_penerimaan;
                $new_remun_gaji->remun_gaji = $r->remun_gaji;
                $new_remun_gaji->remun_insentif = $r->remun_insentif;
                $new_remun_gaji->save();
            }

            // Simpan proses
            $proses = Proses::where('jenis','=',1)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->first();
            if(!$proses) $proses = new Proses;
            $proses->user_id = Auth::user()->id;
            $proses->jenis = 1;
            $proses->tanggal = $request->tanggal;
            $proses->bulan = $request->bulan;
            $proses->tahun = $request->tahun;
            $proses->save();

            // Redirect
            return redirect()->route('admin.remun-gaji.process')->with(['message' => 'Berhasil memperbarui data.']);
        }
    }

    /**
     * Monitoring.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monitoring(Request $request)
    {
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->where('nama','!=','-')->orderBy('pusat','asc')->orderBy('num_order','asc')->get();

        $data = [];
        $isPusat = 0;
        foreach($unit as $u) {
            $nominal = [];
            $nominal_pusat = [];
            for($i=1; $i<=2; $i++) {
                // Append pusat
                if($isPusat == 0 && $u->pusat == 1) {
                    // Get unit pusat
                    $unit_pusat = Unit::where(function($query) use ($tanggal) {
                        $query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
                    })->where(function($query) use ($tanggal) {
                        $query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
                    })->where('pusat','=',1)->pluck('id')->toArray();
    
                    // Count remun gaji
                    $remun_gaji = RemunGaji::whereIn('unit_id',$unit_pusat)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$i)->get();
    
                    // Get pegawai
                    $pegawai = $remun_gaji->pluck('pegawai_id')->toArray();
    
                    // Count lebih kurang
                    $lebih_kurang = LebihKurang::whereIn('pegawai_id',$pegawai)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',0)->sum('selisih');

                    array_push($nominal_pusat, [
                        'kategori' => $i,
                        'pegawai' => $remun_gaji->count(),
                        'remun_gaji' => $remun_gaji->sum('remun_gaji'),
                        'selisih' => $lebih_kurang,
                        'dibayarkan' => $remun_gaji->sum('remun_gaji') + $lebih_kurang,
                    ]);
                }

                // Count remun gaji
                $remun_gaji = RemunGaji::where('unit_id','=',$u->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$i)->get();

                // Get pegawai
                $pegawai = $remun_gaji->pluck('pegawai_id')->toArray();

                // Count lebih kurang
                $lebih_kurang = LebihKurang::whereIn('pegawai_id',$pegawai)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',0)->sum('selisih');

                array_push($nominal, [
                    'kategori' => $i,
                    'pegawai' => $remun_gaji->count(),
                    'remun_gaji' => $remun_gaji->sum('remun_gaji'),
                    'selisih' => $lebih_kurang,
                    'dibayarkan' => $remun_gaji->sum('remun_gaji') + $lebih_kurang,
                ]);
            }

            // Append pusat
            if($isPusat == 0 && $u->pusat == 1) {
                array_push($data, [
                    'unit' => 'Pusat',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'nominal' => $nominal_pusat
                ]);
            }

            if($nominal[0]['pegawai'] > 0 || $nominal[1]['pegawai'] > 0) {
                array_push($data, [
                    'unit' => $u,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'nominal' => $nominal
                ]);
            }

            $isPusat = $u->pusat;
        }

        // View
        return view('admin/remun-gaji/monitoring', [
            'unit' => $unit,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'data' => $data,
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

		$array = Excel::toArray(new RemunGajiImport, public_path('storage/Remun_Gaji_2024_01.xlsx'));
        $bulan = 1;
        $tahun = 2024;
        $sk = 12;

        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[1])->orWhere('npu','=',$data[1])->first();

                    // Cek remun gaji
                    $remun_gaji = RemunGaji::where('pegawai_id','=',$pegawai->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                    if(!$remun_gaji) $remun_gaji = new RemunGaji;

                    // Get status kepegawaian
                    $status_kepegawaian = StatusKepegawaian::where('nama','=',$data[5])->first();

                    // Get jabatan
                    if(!in_array($data[3], ['Koordinator Program Studi A','Koordinator Program Studi B','Koordinator Program Studi C']))
                        $jabatan = Jabatan::where('sk_id','=',$sk)->where('nama','=',$data[3])->where('sub','=',$data[4])->first();
                    else
                        $jabatan = Jabatan::where('sk_id','=',$sk)->where('nama','=',$data[3])->where('sub','=','-')->first();

                    // Get unit
                    $unit = Unit::where('nama','=',$data[7])->first();

                    // Simpan data remun gaji
                    $remun_gaji->pegawai_id = $pegawai->id;
                    $remun_gaji->status_kepeg_id = $status_kepegawaian->id;
                    $remun_gaji->golru_id = $pegawai->golru_id;
                    $remun_gaji->jabatan_dasar_id = $jabatan->jabatan_dasar_id;
                    $remun_gaji->jabatan_id = $jabatan->id;
                    $remun_gaji->unit_id = $unit->id;
                    $remun_gaji->layer_id = $data[6] == 'TENDIK' ? 1 : $unit->layer_id;
                    $remun_gaji->bulan = $bulan;
                    $remun_gaji->tahun = $tahun;
                    $remun_gaji->kategori = $pegawai->jenis;
                    $remun_gaji->remun_penerimaan = $data[10];
                    $remun_gaji->remun_gaji = $data[11];
                    $remun_gaji->remun_insentif = $data[12];
                    $remun_gaji->save();

                    // Simpan koorprodi
                    if(in_array($data[3], ['Koordinator Program Studi A','Koordinator Program Studi B','Koordinator Program Studi C'])) {
                        // Get prodi
                        $prodi = Prodi::where('nama','=',str_replace('Koorprodi ', '', $data[4]))->first();
                        if($prodi) {
                            // Simpan koorprodi remun
                            $koorprodi_remun = KoorprodiRemun::where('pegawai_id','=',$pegawai->id)->where('remun_gaji_id','=',$remun_gaji->id)->where('prodi_id','=',$prodi->id)->first();
                            if(!$koorprodi_remun) $koorprodi_remun = new KoorprodiRemun;
                            $koorprodi_remun->pegawai_id = $pegawai->id;
                            $koorprodi_remun->remun_gaji_id = $remun_gaji->id;
                            $koorprodi_remun->prodi_id = $prodi->id;
                            $koorprodi_remun->save();
                        }
                        else {
                            $explode = explode(';', $data[4]);
                            foreach($explode as $e) {
                                $prodis = Prodi::where('nama','=',str_replace('Koorprodi ', '', $e))->first();
                                if($prodis) {
                                    // Simpan koorprodi remun
                                    $koorprodi_remun = KoorprodiRemun::where('pegawai_id','=',$pegawai->id)->where('remun_gaji_id','=',$remun_gaji->id)->where('prodi_id','=',$prodis->id)->first();
                                    if(!$koorprodi_remun) $koorprodi_remun = new KoorprodiRemun;
                                    $koorprodi_remun->pegawai_id = $pegawai->id;
                                    $koorprodi_remun->remun_gaji_id = $remun_gaji->id;
                                    $koorprodi_remun->prodi_id = $prodis->id;
                                    $koorprodi_remun->save();
                                }
                            }
                        }

                    }
                }
            }
        }
        var_dump($error);
    }

    /**
     * Print PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function print(Request $request)
    {
        // Check the access
        // has_access(method(__METHOD__), Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $kategori = $request->query('kategori');
        $unit = $request->query('unit');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Get SK
        $sk = SK::where('jenis_id','=',1)->where('awal_tahun','=',$tahun)->first();
        
        // Count SK pada tahun berjalan
        $count_sk = SK::where('jenis_id','=',1)->whereYear('tanggal',$tahun)->count();

        // Get unit
        $unit = Unit::findOrFail($request->query('unit'));

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        // Remun Gaji
        if($tahun < 2024)
            $remun_gaji = RemunGaji::where('unit_id','=',$request->query('unit'))->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->orderBy('remun_gaji','desc')->orderBy('status_kepeg_id','asc')->get();
        else
            $remun_gaji = RemunGaji::where('unit_id','=',$request->query('unit'))->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->get();

        // Set title
        $title = 'Remun Gaji '.$unit->nama.' '.$get_kategori.' ('.$tahun.' '.DateTimeExt::month($bulan).')';

        // PDF
        $pdf = PDF::loadView('admin/remun-gaji/print', [
            'title' => $title,
            'unit' => $unit,
            'kategori' => $kategori,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'sk' => $sk,
            'count_sk' => $count_sk,
            'remun_gaji' => $remun_gaji
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Perubahan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function change(Request $request)
    {
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
        $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

        // Get unit and unit_id
        if($request->query('unit') == "0")
            $unit = Unit::where('pusat','=',1)->pluck('id')->toArray();
        else
            $unit = Unit::where('id','=',$request->query('unit'))->where('pusat','=',0)->pluck('id')->toArray();

        // Get unit list
        $unit_list = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->where('pusat','=',0)->where('nama','!=','-')->orderBy('num_order','asc')->get();

        // Get remun bulan ini
        $remun_gaji_bulan_ini['dosen']['pns_IV'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->where('golongan_id','=',4);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['dosen']['pns_III'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->where('golongan_id','=',3);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['dosen']['pppk'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[7]);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['dosen']['tetap'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[3,4,6,8]);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['dosen']['kontrak'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[5]);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['tendik']['pns_IV'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->where('golongan_id','=',4);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['tendik']['pns_III'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->where('golongan_id','=',3);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['tendik']['pns_II_I'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->whereIn('golongan_id',[1,2]);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['tendik']['pppk'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[7]);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['tendik']['tetap'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[3,4,6,8]);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_ini['tendik']['kontrak'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[5]);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();

        // Get remun bulan sebelumnya
        $remun_gaji_bulan_sebelumnya['dosen']['pns_IV'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->where('golongan_id','=',4);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['dosen']['pns_III'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->where('golongan_id','=',3);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['dosen']['pppk'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[7]);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['dosen']['tetap'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[3,4,6,8]);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['dosen']['kontrak'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[5]);
        })->whereIn('unit_id',$unit)->where('kategori','=',1)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['tendik']['pns_IV'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->where('golongan_id','=',4);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['tendik']['pns_III'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->where('golongan_id','=',3);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['tendik']['pns_II_I'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[1,2]);
        })->whereHas('golru', function(Builder $query) {
            return $query->whereIn('golongan_id',[1,2]);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['tendik']['pppk'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[7]);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['tendik']['tetap'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[3,4,6,8]);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya['tendik']['kontrak'] = RemunGaji::whereHas('pegawai', function(Builder $query) {
            return $query->whereIn('status_kepeg_id',[5]);
        })->whereIn('unit_id',$unit)->where('kategori','=',2)->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();

        // Mutasi dosen
        $dosen['pns_IV'] = $this->compare($remun_gaji_bulan_sebelumnya['dosen']['pns_IV'], $remun_gaji_bulan_ini['dosen']['pns_IV'], $bulan, $tahun);
        $dosen['pns_III'] = $this->compare($remun_gaji_bulan_sebelumnya['dosen']['pns_III'], $remun_gaji_bulan_ini['dosen']['pns_III'], $bulan, $tahun);
        $dosen['pppk'] = $this->compare($remun_gaji_bulan_sebelumnya['dosen']['pppk'], $remun_gaji_bulan_ini['dosen']['pppk'], $bulan, $tahun);
        $dosen['tetap'] = $this->compare($remun_gaji_bulan_sebelumnya['dosen']['tetap'], $remun_gaji_bulan_ini['dosen']['tetap'], $bulan, $tahun);
        $dosen['kontrak'] = $this->compare($remun_gaji_bulan_sebelumnya['dosen']['kontrak'], $remun_gaji_bulan_ini['dosen']['kontrak'], $bulan, $tahun);

        // Mutasi tendik
        $tendik['pns_IV'] = $this->compare($remun_gaji_bulan_sebelumnya['tendik']['pns_IV'], $remun_gaji_bulan_ini['tendik']['pns_IV'], $bulan, $tahun);
        $tendik['pns_III'] = $this->compare($remun_gaji_bulan_sebelumnya['tendik']['pns_III'], $remun_gaji_bulan_ini['tendik']['pns_III'], $bulan, $tahun);
        $tendik['pns_II_I'] = $this->compare($remun_gaji_bulan_sebelumnya['tendik']['pns_II_I'], $remun_gaji_bulan_ini['tendik']['pns_II_I'], $bulan, $tahun);
        $tendik['pppk'] = $this->compare($remun_gaji_bulan_sebelumnya['tendik']['pppk'], $remun_gaji_bulan_ini['tendik']['pppk'], $bulan, $tahun);
        $tendik['tetap'] = $this->compare($remun_gaji_bulan_sebelumnya['tendik']['tetap'], $remun_gaji_bulan_ini['tendik']['tetap'], $bulan, $tahun);
        $tendik['kontrak'] = $this->compare($remun_gaji_bulan_sebelumnya['tendik']['kontrak'], $remun_gaji_bulan_ini['tendik']['kontrak'], $bulan, $tahun);

        // Jenis dosen
        $jenis_dosen = [
            ['key' => 'pns_IV', 'name' => 'Dosen PNS Gol IV'],
            ['key' => 'pns_III', 'name' => 'Dosen PNS Gol III'],
            ['key' => 'pppk', 'name' => 'Dosen PPPK'],
            ['key' => 'tetap', 'name' => 'Dosen Tetap'],
            ['key' => 'kontrak', 'name' => 'Dosen Kontrak'],
        ];
        // Jenis tendik
        $jenis_tendik = [
            ['key' => 'pns_IV', 'name' => 'Tendik PNS Gol IV'],
            ['key' => 'pns_III', 'name' => 'Tendik PNS Gol III'],
            ['key' => 'pns_II_I', 'name' => 'Tendik PNS Gol II dan I'],
            ['key' => 'pppk', 'name' => 'Tendik PPPK'],
            ['key' => 'tetap', 'name' => 'Tendik Tetap'],
            ['key' => 'kontrak', 'name' => 'Tendik Pramubakti'],
        ];
		
        // View
        return view('admin/remun-gaji/change', [
            'remun_gaji_bulan_ini' => $remun_gaji_bulan_ini,
            'remun_gaji_bulan_sebelumnya' => $remun_gaji_bulan_sebelumnya,
            'dosen' => $dosen,
            'tendik' => $tendik,
            'jenis_dosen' => $jenis_dosen,
            'jenis_tendik' => $jenis_tendik,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
            'unit_list' => $unit_list,
        ]);
    }

    /**
     * Perubahan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeAll(Request $request)
    {
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
        $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

        // Get remun gaji
        $remun_gaji_bulan_ini = RemunGaji::has('pegawai')->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();
        $remun_gaji_bulan_sebelumnya = RemunGaji::has('pegawai')->where('bulan','=',date('n',strtotime($tanggal_sebelum)))->where('tahun','=',date('Y',strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();

        // Compare        
        $compare = $this->compare($remun_gaji_bulan_sebelumnya, $remun_gaji_bulan_ini, $bulan, $tahun);
		
        // View
        return view('admin/remun-gaji/change-all', [
            'remun_gaji_bulan_ini' => $remun_gaji_bulan_ini,
            'remun_gaji_bulan_sebelumnya' => $remun_gaji_bulan_sebelumnya,
            'compare' => $compare,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal
        ]);
    }

    /**
     * Compare.
     *
     * @param  $sebelum
     * @param  $sesudah
     * @return \Illuminate\Http\Response
     */
    public function compare($sebelum, $sesudah, $bulan, $tahun)
    {
        // Cek pegawai masuk
        $masuk = [];
        if(count($sesudah) > 0) {
            foreach($sesudah as $s) {
                if(!in_array($s, $sebelum))
                    array_push($masuk, $s);
            }
        }

        // Cek pegawai keluar
        $keluar = [];
        if(count($sebelum) > 0) {
            foreach($sebelum as $s) {
                if(!in_array($s, $sesudah))
                    array_push($keluar, $s);
            }
        }

        // Get pegawai masuk
        $pegawai_masuk = [];
        $unit_asal = [];
        $pegawai_baru = false;
        if(count($masuk) > 0) {
            $pegawai_masuk = Pegawai::whereIn('id', $masuk)->get();
            foreach($pegawai_masuk as $key=>$p) {
                // Get mutasi jabatan fungsional
                $mutasi = $p->mutasi_detail()->whereHas('mutasi', function(Builder $query) use ($bulan, $tahun) {
                    return $query->where('jenis_id','=',1)->where('bulan','!=',$bulan)->orWhere('tahun','!=',$tahun);
                })->where('status','=',1)->first();
                $pegawai_masuk[$key]->mutasi_sebelum = $mutasi;

                // Input ke unit
                if($mutasi && !in_array($mutasi->unit->nama, $unit_asal))
                    array_push($unit_asal, $mutasi->unit->nama);

                // Get mutasi jabatan
                $mutasi_sebelum = $p->mutasi_detail()->whereHas('mutasi', function(Builder $query) use ($bulan, $tahun) {
                    return $query->where('bulan','!=',$bulan)->orWhere('tahun','!=',$tahun);
                })->where('status','=',1)->first();
                if(!$mutasi_sebelum)
                    $pegawai_baru = true;
            }
        }

        // Get pegawai keluar
        $pegawai_keluar = [];
        $unit_tujuan = [];
        $non_aktif = [];
        if(count($keluar) > 0) {
            $pegawai_keluar = Pegawai::whereIn('id', $keluar)->get();
            foreach($pegawai_keluar as $key=>$p) {
                // Get mutasi jabatan
                $mutasi = $p->mutasi_detail()->whereHas('mutasi', function(Builder $query) use ($bulan, $tahun) {
                    return $query->where('bulan','=',$bulan)->where('tahun','=',$tahun);
                })->where('status','=',1)->first();
                $pegawai_keluar[$key]->mutasi_sesudah = $mutasi;

                // Input ke unit
                if($mutasi && !in_array($mutasi->unit->nama, $unit_tujuan))
                    array_push($unit_tujuan, $mutasi->unit->nama);

                // Input ke non aktif
                if($p->status_kerja->status == 0 && !in_array($p->status_kerja->nama, $non_aktif))
                    array_push($non_aktif, $p->status_kerja->nama);
            }
        }

        // Set pegawai masuk
        $keterangan_pegawai_masuk = '';
        if(count($pegawai_masuk) > 0) {
            foreach($pegawai_masuk as $key=>$p) {
                $keterangan_pegawai_masuk .= ($key+1).'. '.$p->nama."<br>";
            }
        }

        // Set pegawai keluar
        $keterangan_pegawai_keluar = '';
        if(count($pegawai_keluar) > 0) {
            foreach($pegawai_keluar as $key=>$p) {
                $keterangan_pegawai_keluar .= ($key+1).'. '.$p->nama."<br>";
            }
        }

        // Set keterangan
        $keterangan = '';
        if(count($unit_asal) > 0)
            $keterangan .= 'Mutasi dari '.implode(', ', $unit_asal)."<br>";
        if(count($unit_tujuan) > 0)
            $keterangan .= 'Mutasi ke '.implode(', ', $unit_tujuan)."<br>";
        if(count($non_aktif) > 0)
            $keterangan .= implode(', ', $non_aktif)."<br>";
        if($pegawai_baru == true)
            $keterangan .= 'Pegawai Baru'."<br>";

        // Return
        return [
            'masuk' => $pegawai_masuk,
            'keluar' => $pegawai_keluar,
            'pegawai_masuk' => $keterangan_pegawai_masuk,
            'pegawai_keluar' => $keterangan_pegawai_keluar,
            'keterangan' => $keterangan,
        ];
    }

    public function importMei(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

		$array = Excel::toArray(new RemunGajiImport, public_path('storage/Remun_Gaji_Mei.xlsx'));

        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[0])->first();

                    // Get remun bulan april
                    $remun_april = RemunGaji::where('pegawai_id','=',$pegawai->id)->where('bulan','=',4)->first();

                    // Jika tidak mutasi
                    if($data[11] != 1) {
                        // Simpan ke remun bulan mei
                        $remun_mei = RemunGaji::where('pegawai_id','=',$pegawai->id)->where('bulan','=',5)->first();
                        if(!$remun_mei) $remun_mei = new RemunGaji;
                        $remun_mei->pegawai_id = $remun_april->pegawai_id;
                        $remun_mei->status_kepeg_id = $remun_april->status_kepeg_id;
                        $remun_mei->golru_id = $remun_april->golru_id;
                        $remun_mei->jabatan_dasar_id = $remun_april->jabatan_dasar_id;
                        $remun_mei->jabatan_id = $remun_april->jabatan_id;
                        $remun_mei->unit_id = $remun_april->unit_id;
                        $remun_mei->layer_id = $remun_april->layer_id;
                        $remun_mei->bulan = 5;
                        $remun_mei->tahun = $remun_april->tahun;
                        $remun_mei->kategori = $remun_april->kategori;
                        $remun_mei->remun_penerimaan = $remun_april->remun_penerimaan;
                        $remun_mei->remun_gaji = $remun_april->remun_gaji;
                        $remun_mei->remun_insentif = $remun_april->remun_insentif;
                        $remun_mei->save();
                    }
                    // Jika mutasi
                    else {
                        // Get jabatan
                        $jabatan = Jabatan::where('sk_id','=',7)->where('nama','=',$data[7])->where('sub','=',$data[8])->first();

                        // Get unit
                        $unit = Unit::where('nama','=',$data[2])->first();

                        // Get referensi
                        $referensi = Referensi::where('sk_id','=',7)->where('jabatan_dasar_id','=',$jabatan->jabatan_dasar_id)->where('layer_id','=',$unit->layer_id)->first();

                        if($data[5] != 'Calon Pegawai Tetap') {
                            if($data[7] == 'Profesor/Guru Besar')
                                $tmt = '2023-02-01';
                            else
                                $tmt = '2023-04-01';

                            // Simpan ke remun bulan mei
                            $remun_mei = RemunGaji::where('pegawai_id','=',$pegawai->id)->where('bulan','=',5)->first();
                            if(!$remun_mei) $remun_mei = new RemunGaji;
                            $remun_mei->pegawai_id = $remun_april->pegawai_id;
                            $remun_mei->status_kepeg_id = $remun_april->status_kepeg_id;
                            $remun_mei->golru_id = $remun_april->golru_id;
                            $remun_mei->jabatan_dasar_id = $jabatan->jabatan_dasar_id;
                            $remun_mei->jabatan_id = $jabatan->id;
                            $remun_mei->unit_id = $remun_april->unit_id;
                            $remun_mei->layer_id = $remun_april->layer_id;
                            $remun_mei->bulan = 5;
                            $remun_mei->tahun = $remun_april->tahun;
                            $remun_mei->kategori = $remun_april->kategori;
                            $remun_mei->remun_penerimaan = $referensi->remun_standar;
                            $remun_mei->remun_gaji = $referensi->remun_gaji;
                            $remun_mei->remun_insentif = $referensi->remun_insentif;
                            $remun_mei->save();

                            // Simpan mutasi
                            $mutasi = Mutasi::where('pegawai_id','=',$pegawai->id)->where('bulan','=',5)->first();
                            if(!$mutasi) $mutasi = new Mutasi;
                            $mutasi->pegawai_id = $pegawai->id;
                            $mutasi->sk_id = 7;
                            $mutasi->jenis_id = 1;
                            $mutasi->status_kepeg_id = 1;
                            $mutasi->golru_id = null;
                            $mutasi->gaji_pokok_id = null;
                            $mutasi->bulan = 5;
                            $mutasi->tahun = 2023;
                            $mutasi->uraian = 'Perubahan Mei 2023';
                            $mutasi->tmt = $tmt;
                            $mutasi->remun_penerimaan = $referensi->remun_standar;
                            $mutasi->remun_gaji = $referensi->remun_gaji;
                            $mutasi->remun_insentif = $referensi->remun_insentif;
                            $mutasi->save();

                            // Simpan mutasi detail
                            $mutasi_detail = $mutasi->detail()->first();
                            if(!$mutasi_detail) $mutasi_detail = new MutasiDetail;
                            $mutasi_detail->mutasi_id = $mutasi->id;
                            $mutasi_detail->jabatan_id = $jabatan->id;
                            $mutasi_detail->jabatan_dasar_id = $jabatan->jabatan_dasar_id;
                            $mutasi_detail->unit_id = $remun_april->unit_id;
                            $mutasi_detail->layer_id = $remun_april->layer_id;
                            $mutasi_detail->status = 1;
                            $mutasi_detail->save();
                        }
                        else {
                            // Get pegawai baru
                            $pegawai_baru = Pegawai::where('nama','=',$data[1])->where('status_kepeg_id','=',4)->first();

                            // Simpan ke remun bulan mei
                            if($pegawai_baru)
                                $remun_mei = RemunGaji::where('pegawai_id','=',$pegawai_baru->id)->where('bulan','=',5)->first();
                            else {
                                $remun_mei = new RemunGaji;
                                array_push($error, $data[1]);
                            }

                            if(!$remun_mei) $remun_mei = new RemunGaji;
                            $remun_mei->pegawai_id = $pegawai_baru ? $pegawai_baru->id : 0;
                            $remun_mei->status_kepeg_id = 4;
                            $remun_mei->golru_id = null;
                            $remun_mei->jabatan_dasar_id = $jabatan->jabatan_dasar_id;
                            $remun_mei->jabatan_id = $jabatan->id;
                            $remun_mei->unit_id = $remun_april->unit_id;
                            $remun_mei->layer_id = $remun_april->layer_id;
                            $remun_mei->bulan = 5;
                            $remun_mei->tahun = $remun_april->tahun;
                            $remun_mei->kategori = $remun_april->kategori;
                            $remun_mei->remun_penerimaan = mround((80 / 100) * $referensi->remun_standar, 1);
                            $remun_mei->remun_gaji = mround((80 / 100) * $referensi->remun_gaji, 1);
                            $remun_mei->remun_insentif = mround((80 / 100) * $referensi->remun_insentif, 1);
                            $remun_mei->save();
                        }
                    }
                }
            }
        }
        var_dump($error);
    }

    public function mround() {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");
        
        // Get remun gaji Januari - Mei
        $remun_gaji = RemunGaji::where('bulan','<',6)->get();
        foreach($remun_gaji as $rg) {
            // Update
            $update = RemunGaji::find($rg->id);
            $update->remun_penerimaan = mround($rg->remun_penerimaan, 1);
            $update->remun_gaji = mround($rg->remun_gaji, 1);
            $update->remun_insentif = mround($rg->remun_insentif, 1);
            $update->save();
        }
    }
}
