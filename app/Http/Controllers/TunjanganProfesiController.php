<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\TunjanganProfesiCSVExport;
use App\Exports\TunjanganProfesiExcelExport;
use App\Imports\TunjanganProfesiImport;
use App\Models\TunjanganProfesi;
use App\Models\JenisTunjanganProfesi;
use App\Models\Angkatan;
use App\Models\Pegawai;
use App\Models\Golongan;
use App\Models\Gaji;
use App\Models\GajiPokok;
use App\Models\Mutasi;
use App\Models\MutasiSerdos;
use App\Models\SK;
use App\Models\Proses;
use App\Models\Unit;

class TunjanganProfesiController extends Controller
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

        // Get jenis
        $jenis = JenisTunjanganProfesi::findOrFail($request->query('jenis'));

        // Get angkatan
        $angkatan = Angkatan::where('jenis_id','=',$jenis->id)->orderBy('nama','asc')->get();

        // Get tunjangan profesi
        $tunjangan = [];
        if($request->query('angkatan') != null && $request->query('angkatan') != 0)
            $tunjangan = TunjanganProfesi::where('angkatan_id','=',$request->query('angkatan'))->where('bulan','=',$bulan)->where('tahun','=',$tahun)->get();

        // View
        return view('admin/tunjangan-profesi/index', [
            'tunjangan' => $tunjangan,
            'angkatan' => $angkatan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'jenis' => $jenis
        ]);
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
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-10'; // Maks tanggal 10

        // Get jenis
        $jenis = JenisTunjanganProfesi::find($request->query('jenis'));

        // Get angkatan
        if($jenis)
            $angkatan = Angkatan::where('jenis_id','=',$jenis->id)->orderBy('nama','asc')->get();
        else
            $angkatan = Angkatan::orderBy('jenis_id','asc')->orderBy('nama','asc')->get();

        // Get jenis tunjangan
        $jenis_tunjangan = JenisTunjanganProfesi::all();
        
        $data = [];
        $total = [
            'pegawai' => 0,
            'pegawai_non_aktif' => 0,
            'tunjangan' => 0,
            'pph' => 0,
            'diterimakan' => 0,
        ];
        foreach($angkatan as $a) {
            if($a->jenis_id != 4) {
                // Get tunjangan profesi
                $tunjangan = TunjanganProfesi::where('angkatan_id','=',$a->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->get();

                // Get pegawai non aktif
                $pegawai_non_aktif = TunjanganProfesi::where('angkatan_id','=',$a->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->whereHas('pegawai', function (Builder $query) use ($tanggal) {
                    return $query->whereDoesntHave('status_kerja', function (Builder $query2) {
                        return $query2->where('status','=',1);
                    })->where('tmt_non_aktif','<=',$tanggal);
                })->get();

                // Push data
                array_push($data, [
                    'id' => $a->id,
                    'angkatan' => $a->nama,
                    'jenis' => $a->jenis->nama,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'pegawai' => $tunjangan->count(),
                    'pegawai_non_aktif' => $pegawai_non_aktif->pluck('nama')->toArray(),
                    'tunjangan' => $tunjangan->sum('tunjangan'),
                    'pph' => $tunjangan->sum('pph'),
                    'diterimakan' => $tunjangan->sum('diterimakan'),
                ]);

                // Count total
                $total['pegawai'] += $tunjangan->count();
                $total['pegawai_non_aktif'] += $pegawai_non_aktif->count();
                $total['tunjangan'] += $tunjangan->sum('tunjangan');
                $total['pph'] += $tunjangan->sum('pph');
                $total['diterimakan'] += $tunjangan->sum('diterimakan');
            }
        }

        // Non PNS
        if(!$jenis || ($jenis && $jenis->id == 4)) {
            // Get tunjangan profesi
            $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
                return $query->where('jenis_id','=',4);
            })->where('bulan','=',$bulan)->where('tahun','=',$tahun)->get();

            // Get pegawai non aktif
            $pegawai_non_aktif = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
                return $query->where('jenis_id','=',4);
            })->where('bulan','=',$bulan)->where('tahun','=',$tahun)->whereHas('pegawai', function (Builder $query) use ($tanggal) {
                return $query->whereDoesntHave('status_kerja', function (Builder $query2) {
                    return $query2->where('status','=',1);
                })->where('tmt_non_aktif','<=',$tanggal);
            })->get();

            // Push data
            array_push($data, [
                'id' => '',
                'angkatan' => 'Semua Angkatan',
                'jenis' => 'Profesi Non PNS',
                'bulan' => $bulan,
                'tahun' => $tahun,
                'pegawai' => $tunjangan->count(),
                'pegawai_non_aktif' => $pegawai_non_aktif->pluck('nama')->toArray(),
                'tunjangan' => $tunjangan->sum('tunjangan'),
                'pph' => $tunjangan->sum('pph'),
                'diterimakan' => $tunjangan->sum('diterimakan'),
            ]);

            // Count total
            $total['pegawai'] += $tunjangan->count();
            $total['pegawai_non_aktif'] += $pegawai_non_aktif->count();
            $total['tunjangan'] += $tunjangan->sum('tunjangan');
            $total['pph'] += $tunjangan->sum('pph');
            $total['diterimakan'] += $tunjangan->sum('diterimakan');
        }

        // View
        return view('admin/tunjangan-profesi/monitoring', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'jenis' => $jenis,
            'jenis_tunjangan' => $jenis_tunjangan,
            'data' => $data,
            'total' => $total,
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
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        if($request->method() == "GET") {
            // Get proses
            $proses = Proses::where('jenis','=',3)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();

            foreach($proses as $key=>$p) {
                // Count pegawai
                $proses[$key]->pegawai = TunjanganProfesi::where('bulan','=',$p->bulan)->where('tahun','=',$p->tahun)->count();

                // Sum tunjangan profesi
                $proses[$key]->tunjangan = TunjanganProfesi::where('bulan','=',$p->bulan)->where('tahun','=',$p->tahun)->sum('diterimakan');
            }

            // View
            return view('admin/tunjangan-profesi/process', [
                'proses' => $proses
            ]);
        }
        elseif($request->method() == "POST") {
            // Set tanggal proses
            $tanggal = $request->tahun.'-'.($request->bulan < 10 ? '0'.$request->bulan : $request->bulan).'-'.$request->tanggal; // Maks tanggal 14

            // Set tanggal periode sebelumnya
            $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

            // Get mutasi serdos nonaktif
            $mutasi_serdos_nonaktif = MutasiSerdos::whereHas('jenis', function(Builder $query) {
                return $query->where('status','=',0);
            })->where('tmt','<=',$tanggal)->where('bulan','=',0)->where('tahun','=',0)->get();
            if(count($mutasi_serdos_nonaktif) <= 0) {
                $mutasi_serdos_nonaktif = MutasiSerdos::whereHas('jenis', function(Builder $query) {
                    return $query->where('status','=',0);
                })->where('tmt','<=',$tanggal)->where('bulan','=',date('n', strtotime($tanggal)))->where('tahun','=',date('Y', strtotime($tanggal)))->get();
            }
            
            // Get tunjangan profesi bulan sebelumnya
            $tunjangan_sebelum = TunjanganProfesi::whereNotIn('pegawai_id',$mutasi_serdos_nonaktif->pluck('pegawai_id')->toArray())->where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->get();

            foreach($tunjangan_sebelum as $t) {
                // Get mutasi
                $mutasi = Mutasi::whereHas('pegawai', function (Builder $query) use ($t) {
                    return $query->whereHas('status_kerja', function (Builder $query2) {
                        return $query2->where('status','=',1);
                    })->where('id','=',$t->pegawai_id);
                })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();

                if($mutasi) {
                    // Simpan tunjangan baru
                    $tunjangan = TunjanganProfesi::where('pegawai_id','=',$t->pegawai_id)->where('angkatan_id','=',$t->angkatan_id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->first();
                    if(!$tunjangan) $tunjangan = new TunjanganProfesi;
                    $tunjangan->pegawai_id = $t->pegawai_id;
                    $tunjangan->angkatan_id = $t->angkatan_id;
                    $tunjangan->unit_id = $t->pegawai->unit_id;
                    $tunjangan->golongan_id = $mutasi->golru->golongan_id;
                    $tunjangan->nip = $t->nip;
                    $tunjangan->nama = $t->nama;
                    $tunjangan->nomor_rekening = $t->nomor_rekening;
                    $tunjangan->nama_rekening = $t->nama_rekening;
                    $tunjangan->bulan = $request->bulan;
                    $tunjangan->tahun = $request->tahun;

                    // Jika disesuaikan dengan gaji pokok terbaru
                    if($request->kategori == 1) {
                        $tunjangan->tunjangan = $t->tunjangan;
                        $tunjangan->pph = $t->pph;
                        $tunjangan->diterimakan = $t->diterimakan;
                    }
                    // Jika disamakan dengan bulan sebelumnya
                    else {
                        $tunjangan->tunjangan = $t->angkatan->jenis_id == 1 ? 2 * $mutasi->gaji_pokok->gaji_pokok : $mutasi->gaji_pokok->gaji_pokok;
                        $tunjangan->pph = $mutasi->golru->golongan_id == 4 ? (15/100) * $tunjangan->tunjangan : (5/100) * $tunjangan->tunjangan;
                        $tunjangan->diterimakan = $tunjangan->tunjangan - $tunjangan->pph;
                    }
                    $tunjangan->save();
                }
            }

            foreach($mutasi_serdos_nonaktif as $m) {
                // Update bulan dan tahun proses
                $ms = MutasiSerdos::find($m->id);
                $ms->bulan = date('n', strtotime($tanggal));
                $ms->tahun = date('Y', strtotime($tanggal));
                $ms->save();
            }

            // Get mutasi serdos aktif
            $mutasi_serdos = MutasiSerdos::whereHas('jenis', function(Builder $query) {
                return $query->where('status','=',1);
            })->where('tmt','<=',$tanggal)->where('bulan','=',0)->where('tahun','=',0)->get();
            if(count($mutasi_serdos) <= 0) {
                $mutasi_serdos = MutasiSerdos::whereHas('jenis', function(Builder $query) {
                    return $query->where('status','=',1);
                })->where('tmt','<=',$tanggal)->where('bulan','=',date('n', strtotime($tanggal)))->where('tahun','=',date('Y', strtotime($tanggal)))->get();
            }
            
            foreach($mutasi_serdos as $m) {
                // Get mutasi
                $mutasi = Mutasi::whereHas('pegawai', function (Builder $query) use ($m) {
                    return $query->whereHas('status_kerja', function (Builder $query2) {
                        return $query2->where('status','=',1);
                    })->where('id','=',$m->pegawai_id);
                })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();

                if($mutasi) {
                    // Cek apakah pegawai sudah punya tunjangan kehormatan profesor
                    $cek = $m->pegawai()->whereHas('tunjangan_profesi', function(Builder $query) {
                        return $query->whereHas('angkatan', function(Builder $query2) {
                            return $query2->where('jenis_id','=',1);
                        });
                    })->get();

                    // Simpan tunjangan baru
                    $tunjangan = TunjanganProfesi::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->first();
                    if(!$tunjangan) $tunjangan = new TunjanganProfesi;
                    $tunjangan->pegawai_id = $m->pegawai_id;
                    $tunjangan->angkatan_id = $m->angkatan_id;
                    $tunjangan->unit_id = $m->unit_id;
                    $tunjangan->golongan_id = $mutasi->golru->golongan_id;
                    $tunjangan->nip = $mutasi->pegawai->nip;
                    $tunjangan->nama = $m->nama_supplier;
                    $tunjangan->nomor_rekening = $m->nomor_rekening;
                    $tunjangan->nama_rekening = $m->nama_rekening;
                    $tunjangan->bulan = $request->bulan;
                    $tunjangan->tahun = $request->tahun;
                    $tunjangan->tunjangan = count($cek) > 0 ? 2 * $m->gaji_pokok->gaji_pokok : $m->gaji_pokok->gaji_pokok;
                    $tunjangan->pph = $mutasi->golru->golongan_id == 4 ? (15/100) * $tunjangan->tunjangan : (5/100) * $tunjangan->tunjangan;
                    $tunjangan->diterimakan = $tunjangan->tunjangan - $tunjangan->pph;
                    $tunjangan->save();
                }

                // Update mutasi serdos
                foreach($mutasi_serdos as $m) {
                    // Update bulan dan tahun proses
                    $ms = MutasiSerdos::find($m->id);
                    $ms->bulan = date('n', strtotime($tanggal));
                    $ms->tahun = date('Y', strtotime($tanggal));
                    $ms->save();
                }
            }

            // Simpan proses
            $proses = Proses::where('jenis','=',3)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->first();
            if(!$proses) $proses = new Proses;
            $proses->user_id = Auth::user()->id;
            $proses->jenis = 3;
            $proses->tanggal = $request->tanggal;
            $proses->bulan = $request->bulan;
            $proses->tahun = $request->tahun;
            $proses->save();

            // Redirect
            return redirect()->route('admin.tunjangan-profesi.process')->with(['message' => 'Berhasil memperbarui data.']);
        }
    }

    /**
     * Export to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get jenis
        $jenis = JenisTunjanganProfesi::find($request->query('jenis'));

        if($jenis) {
            // Get tunjangan profesi
            $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($jenis) {
                return $query->where('jenis_id','=',$jenis->id);
            })->where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();

            // Download
            return Excel::download(new TunjanganProfesiExcelExport($tunjangan), 'Tunjangan Profesi ('.$jenis->nama.') - '.$request->tahun.' '.\Ajifatur\Helpers\DateTimeExt::month($request->bulan).'.xlsx');
        }
        else {
            // Get tunjangan profesi
            $tunjangan = TunjanganProfesi::where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();

            // Download
            return Excel::download(new TunjanganProfesiExcelExport($tunjangan), 'Tunjangan Profesi - '.$request->tahun.' '.\Ajifatur\Helpers\DateTimeExt::month($request->bulan).'.xlsx');
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

        // // Get tunjangan kehormatan profesor
        // $tunjangan = TunjanganProfesi::whereHas('angkatan', function(Builder $query) {
        //     return $query->where('jenis_id','=',1);
        // })->whereIn('bulan',[1,2,3,4,5])->where('tahun','=',2023)->get();
        // foreach($tunjangan as $t) {
        //     // Update
        //     if($t->diterimakan > $t->tunjangan) {
        //         $tunj = TunjanganProfesi::find($t->id);
        //         $tunj->tunjangan = 2 * $t->tunjangan;
        //         $tunj->save();
        //     }
        // }
        // return;

        // Set jenis, bulan, tahun
        $jenis = 1;
        $bulan = 6;
        $tahun = 2023;

        // Set file
        if($jenis == 1)
		    $array = Excel::toArray(new TunjanganProfesiImport, public_path('storage/Serdos Kehormatan Profesor.xlsx'));
        elseif($jenis == 2)
    		$array = Excel::toArray(new TunjanganProfesiImport, public_path('storage/Serdos GB.xlsx'));
        elseif($jenis == 3)
    		$array = Excel::toArray(new TunjanganProfesiImport, public_path('storage/Serdos Non GB.xlsx'));

        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[1] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[5])->first();

                    if($pegawai) {
                        // Get angkatan
                        // $angkatan = Angkatan::where('jenis_id','=',$jenis)->where('nama','=',$data[6])->first();
                        // if(!$angkatan) $angkatan = new Angkatan;
                        // $angkatan->jenis_id = $jenis;
                        // $angkatan->nama = $data[6];
                        // $angkatan->save();

                        // Get tunjangan profesi yang sudah ada
                        $tp = $pegawai->tunjangan_profesi()->whereHas('angkatan', function(Builder $query) use ($jenis) {
                            return $query->where('jenis_id','=',$jenis);
                        })->first();

                        // Get golongan
                        // if($jenis == 3)
                        //     $golongan = Golongan::where('nama','=',$data[7])->first();

                        // if($tp) {
                            // Simpan tunjangan
                            $tunjangan = TunjanganProfesi::whereHas('angkatan', function(Builder $query) use ($jenis) {
                                return $query->where('jenis_id','=',$jenis);
                            })->where('pegawai_id','=',$pegawai->id)->where('bulan','=',$data[7])->where('tahun','=',$tahun)->first();
                            if(!$tunjangan) $tunjangan = new TunjanganProfesi;
                            $tunjangan->pegawai_id = $pegawai->id;
                            $tunjangan->angkatan_id = $tp ? $tp->angkatan_id : 0;
                            $tunjangan->unit_id = $tp ? $tp->unit_id : 0;
                            $tunjangan->nip = $data[5];
                            $tunjangan->nama = $data[1];
                            $tunjangan->nomor_rekening = $data[3];
                            $tunjangan->nama_rekening = $data[2];
                            $tunjangan->bulan = $data[7];
                            $tunjangan->tahun = $tahun;
                            if($jenis == 1 || $jenis == 2) {
                                $tunjangan->golongan_id = 4;
                                $tunjangan->tunjangan = $jenis == 1 ? 2 * $data[6] : $data[6];
                                $tunjangan->pph = mround((15/100) * $tunjangan->tunjangan, 1);
                            }
                            elseif($jenis == 3) {
                                $pph = ($pegawai->golongan_id == 4) ? 15 : 5;
                                $tunjangan->golongan_id = $pegawai->golongan_id;
                                $tunjangan->tunjangan = (100 * $data[4]) / (100 - $pph);
                                $tunjangan->pph = ($pph / 100) * $tunjangan->tunjangan;
                            }
                            $tunjangan->diterimakan = $data[4];
                            $tunjangan->save();
                        // }
                        // else {
                        //     array_push($error, [
                        //         'nip' => $data[5],
                        //         'nama' => $data[1],
                        //         'bulan' => $data[7],
                        //     ]);
                        // }
                    }
                }
            }
        }
        echo "<pre>";
        print_r($error);
        echo "</pre>";
    }
	
    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::findOrFail($request->id);
        $tunjangan->delete();

        // Redirect
        return redirect()->route('admin.pegawai.detail', ['id' => $tunjangan->pegawai_id, 'tunjangan_profesi' => 1])->with(['message' => 'Berhasil menghapus data.']);
    }

    /**
     * New Professor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function new(Request $request)
    {		
        // Get dosen PNS
        $dosen = Pegawai::whereHas('status_kerja', function(Builder $query) {
            return $query->where('status','=',1);
        })->whereHas('status_kepegawaian', function(Builder $query) {
            return $query->where('id','=',1);
        })->where('jenis','=',1)->orderBy('nip','asc')->get();
		
		// Get pegawai ID pada tunjangan kehormatan profesor
		$latest = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','=',1);
        })->latest('tahun')->latest('bulan')->first();
		$pegawaiID = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','=',1);
        })->where('bulan','=',$latest->bulan)->where('tahun','=',$latest->tahun)->pluck('pegawai_id')->toArray();
		
		$new = [];
		foreach($dosen as $d) {
            // Get mutasi jabatan
            $mutasi = $d->mutasi()->where('jenis_id','=',1)->first();
			
			// Get jabatan fungsional
            $jabfung = $mutasi ? $mutasi->detail()->whereHas('jabatan', function (Builder $query) {
                return $query->where('jenis_id','=',1);
            })->first() : false;
			
			// Push
			if($jabfung && ($jabfung->jabatan->nama == 'Profesor' || $jabfung->jabatan->nama == 'Profesor/Guru Besar') && !in_array($d->id, $pegawaiID)) {
				// Get angkatan
				$latest_t = TunjanganProfesi::has('angkatan')->where('pegawai_id','=',$d->id)->latest('tahun')->latest('bulan')->first();
				$angkatan = $latest_t->angkatan;
				
				array_push($new, [
					'pegawai' => $d,
					'unit' => $jabfung->unit,
					'angkatan' => $angkatan,
					'tmt' => $mutasi->tmt
				]);
			}
		}
		
        // View
        return view('admin/tunjangan-profesi/new', [
            'new' => $new
        ]);
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

        // Get tunjangan bulan ini
        $tunjangan_bulan_ini = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','!=',1);
        })->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();

        // Set tanggal sebelumnya
        $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

        // Get tunjangan bulan sebelumnya
        $tunjangan_bulan_sebelumnya = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','!=',1);
        })->where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();

        // Cek bulan ini
        $cek_bulan_ini = [];
        if(count($tunjangan_bulan_ini) > 0) {
            foreach($tunjangan_bulan_ini as $t) {
                if(!in_array($t, $tunjangan_bulan_sebelumnya))
                    array_push($cek_bulan_ini, $t);
            }
        }

        // Cek bulan sebelumnya
        $cek_bulan_sebelumnya = [];
        if(count($tunjangan_bulan_sebelumnya) > 0) {
            foreach($tunjangan_bulan_sebelumnya as $t) {
                if(!in_array($t, $tunjangan_bulan_ini))
                    array_push($cek_bulan_sebelumnya, $t);
            }
        }
        
        // Get pegawai on
        $pegawai_on = [];
        if(count($tunjangan_bulan_ini) > 0) {
            $pegawai_on = Pegawai::whereIn('id', $cek_bulan_ini)->get();
        }

        // Get pegawai off
        $pegawai_off = [];
        if(count($tunjangan_bulan_sebelumnya) > 0) {
            $pegawai_off = Pegawai::whereIn('id', $cek_bulan_sebelumnya)->get();
        }
		
        // View
        return view('admin/tunjangan-profesi/change', [
            'tunjangan_bulan_ini' => $tunjangan_bulan_ini,
            'tunjangan_bulan_sebelumnya' => $tunjangan_bulan_sebelumnya,
            'pegawai_on' => $pegawai_on,
            'pegawai_off' => $pegawai_off,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
        ]);
    }
}