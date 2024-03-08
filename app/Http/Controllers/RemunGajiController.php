<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\ByStartRowImport;
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
use App\Models\RemunKoorprodi;

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
                })->where('proses','=',$p->tahun.'-'.($p->bulan < 10 ? '0'.$p->bulan : $p->bulan).'-01')->count();

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
            $tanggal_1 = $request->tahun.'-'.($request->bulan < 10 ? '0'.$request->bulan : $request->bulan).'-01';

            // Set tanggal periode sebelumnya
            $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

            // Get latest num order
            $latest = RemunGaji::where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->latest('num_order')->first();

            // Get mutasi remun
            $mutasi = collect();
            $mutasi_1 = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->where('proses_remun','=',$tanggal_1)->get();
            $mutasi_2 = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->whereNull('proses_remun')->where('tmt','<=',$tanggal)->get();
            $mutasi = $mutasi->merge($mutasi_1)->merge($mutasi_2);

            // Loop mutasi
            foreach($mutasi as $m) {
                // Jika status kepegawaian aktif
                if($m->jenis->status == 1) {
					// Get jabatan tertinggi dalam mutasi
					$jabatan_tertinggi = $m->detail()->where('status','=',1)->first();

                    // Get remun gaji bulan sebelumnya
                    $remun_gaji_sebelum = RemunGaji::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->first();
                    if(!$remun_gaji_sebelum || ($remun_gaji_sebelum && $remun_gaji_sebelum->unit_id != $jabatan_tertinggi->unit_id))
                        $new_num_order = $latest->num_order + $m->num_order;

					// Simpan remun gaji baru
					$new_remun_gaji = RemunGaji::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($tanggal)))->where('tahun','=',date('Y', strtotime($tanggal)))->first();
					if(!$new_remun_gaji) $new_remun_gaji = new RemunGaji;
					$new_remun_gaji->pegawai_id = $m->pegawai->id;
					$new_remun_gaji->status_kepeg_id = $m->status_kepeg_id;
					$new_remun_gaji->golru_id = $m->golru_id;
					$new_remun_gaji->jabatan_dasar_id = $jabatan_tertinggi->jabatan_dasar_id;
					$new_remun_gaji->jabatan_id = $jabatan_tertinggi->jabatan_id;
					$new_remun_gaji->unit_id = $jabatan_tertinggi->unit_id;
					$new_remun_gaji->layer_id = $jabatan_tertinggi->layer_id;
					$new_remun_gaji->bulan = date('n', strtotime($tanggal));
					$new_remun_gaji->tahun = date('Y', strtotime($tanggal));
					$new_remun_gaji->kategori = $m->pegawai->jenis;
					$new_remun_gaji->remun_penerimaan = $m->remun_penerimaan;
					$new_remun_gaji->remun_gaji = $m->remun_gaji;
					$new_remun_gaji->remun_insentif = $m->remun_insentif;
					$new_remun_gaji->num_order = $new_num_order;
					$new_remun_gaji->save();

					if($m->tmt <= $tanggal_sebelum) {
						// Loop tanggal
						$temp_tanggal = $tanggal_sebelum;
                        $temp_tanggal_1 = date('Y-m', strtotime($temp_tanggal)).'-01';
						while($temp_tanggal >= $m->tmt) {
							// Get mutasi sebelum
							$mutasi_sebelum = Mutasi::where('pegawai_id','=',$m->pegawai_id)->where('proses_remun','=',$temp_tanggal_1)->first();

							// Get remun gaji bulan sebelumnya
							$rg = RemunGaji::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($temp_tanggal)))->where('tahun','=',date('Y', strtotime($temp_tanggal)))->first();

							// Jika ada mutasi sebelum dan jabatan lebih dari 1
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
                    // Get remun gaji sebelum
                    $remun_gaji_sebelum = RemunGaji::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->first();

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
					$new_remun_gaji->num_order = $remun_gaji_sebelum->num_order;
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
                $new_remun_gaji->status_kepeg_id = $r->status_kepeg_id;
                $new_remun_gaji->golru_id = $r->golru_id;
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
                $new_remun_gaji->num_order = $r->num_order;
                $new_remun_gaji->save();

                // Simpan koorprodi
                if(in_array($new_remun_gaji->jabatan_dasar->nama, ['Koordinator Program Studi A','Koordinator Program Studi B','Koordinator Program Studi C'])) {
                    // Get remun gaji bulan sebelumnya
                    $remun_gaji_sebelum = RemunGaji::where('pegawai_id','=',$r->pegawai->id)->where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->first();

                    if($remun_gaji_sebelum->koorprodi()->count() > 0) {
                        foreach($remun_gaji_sebelum->koorprodi as $kp) {
                            // Simpan
                            $remun_koorprodi = RemunKoorprodi::where('pegawai_id','=',$kp->pegawai_id)->where('remun_gaji_id','=',$new_remun_gaji->id)->where('prodi_id','=',$kp->prodi_id)->first();
                            if(!$remun_koorprodi) $remun_koorprodi = new RemunKoorprodi;
                            $remun_koorprodi->pegawai_id = $kp->pegawai_id;
                            $remun_koorprodi->remun_gaji_id = $new_remun_gaji->id;
                            $remun_koorprodi->prodi_id = $kp->prodi_id;
                            $remun_koorprodi->save();
                        }
                    }
                }
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

        // Count remun gaji
        $count = RemunGaji::where('bulan','=',$bulan)->where('tahun','=',$tahun)->count();

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
            'count' => $count,
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

		$array = Excel::toArray(new ByStartRowImport(2), public_path('storage/Remun_Gaji_2024_01.xlsx'));
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
                            // Simpan remun koorprodi
                            $koorprodi_remun = RemunKoorprodi::where('pegawai_id','=',$pegawai->id)->where('remun_gaji_id','=',$remun_gaji->id)->where('prodi_id','=',$prodi->id)->first();
                            if(!$koorprodi_remun) $koorprodi_remun = new RemunKoorprodi;
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
                                    // Simpan remun koorprodi
                                    $koorprodi_remun = RemunKoorprodi::where('pegawai_id','=',$pegawai->id)->where('remun_gaji_id','=',$remun_gaji->id)->where('prodi_id','=',$prodis->id)->first();
                                    if(!$koorprodi_remun) $koorprodi_remun = new RemunKoorprodi;
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

        // Get kategori, unit, bulan, tahun, dan tanggal
        $kategori = $request->query('kategori');
        $unit = $request->query('unit');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Get SK
        $sk = SK::whereHas('jenis', function(Builder $query) {
            return $query->where('nama','=','Remunerasi Gaji');
        })->whereYear('tmt',$tahun)->first();

        // Get unit
        $unit = Unit::findOrFail($request->query('unit'));

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        // Remun Gaji
        if($tahun < 2024)
            $remun_gaji = RemunGaji::where('unit_id','=',$request->query('unit'))->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->orderBy('remun_gaji','desc')->orderBy('status_kepeg_id','asc')->get();
        else
            $remun_gaji = RemunGaji::where('unit_id','=',$request->query('unit'))->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->orderBy('num_order','asc')->get();

        // Set title
        $title = 'Remunerasi Gaji '.$unit->nama.' '.$get_kategori.' ('.$tahun.' '.DateTimeExt::month($bulan).')';

        // PDF
        $pdf = PDF::loadView('admin/remun-gaji/print', [
            'title' => $title,
            'unit' => $unit,
            'kategori' => $kategori,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'sk' => $sk,
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
        // Set tanggal
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

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
                $mutasi = $p->mutasi_detail()->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                    return $query->where('jenis_id','=',1)->where('proses_remun','!=',$tanggal);
                })->where('status','=',1)->first();
                $pegawai_masuk[$key]->mutasi_sebelum = $mutasi;

                // Input ke unit
                if($mutasi && !in_array($mutasi->unit->nama, $unit_asal))
                    array_push($unit_asal, $mutasi->unit->nama);

                // Get mutasi jabatan
                $mutasi_sebelum = $p->mutasi_detail()->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                    return $query->where('proses_remun','!=',$tanggal);
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
                $mutasi = $p->mutasi_detail()->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                    return $query->where('proses_remun','=',$tanggal);
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
}
