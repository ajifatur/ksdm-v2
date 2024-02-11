<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Models\Pegawai;
use App\Models\Gaji;
use App\Models\GajiNonASN;
use App\Models\GajiPokok;
use App\Models\StatusKepegawaian;

class PantauanController extends Controller
{
    /**
     * MKG PNS
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function mkg(Request $request)
    {
        // Set tanggal
		$tanggal = date('Y').'-'.date('m').'-01';

        // Get pegawai
        if($request->query('tipe') == 1) {
            $pegawai = Pegawai::whereHas('status_kerja', function(Builder $query) {
                return $query->where('status','=',1);
            })->whereHas('status_kepegawaian', function(Builder $query) {
                return $query->whereIn('nama', ['CPNS','PNS']);
            })->orderBy('tmt_golongan','asc')->orderBy('jenis','asc')->get();
        }
        elseif($request->query('tipe') == 2) {
            $pegawai = Pegawai::whereHas('status_kerja', function(Builder $query) {
                return $query->where('status','=',1);
            })->whereHas('status_kepegawaian', function(Builder $query) {
                return $query->whereIn('nama', ['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN','Non PNS']);
            })->orderBy('tmt_golongan','asc')->orderBy('jenis','asc')->get();
        }

		foreach($pegawai as $key=>$p) {
			// Get mutasi KP / KGB / PMK / PGP terakhir
			$pegawai[$key]->mutasi_terakhir = $p->mutasi()->whereHas('jenis', function(Builder $query) {
				return $query->whereIn('nama',['Mutasi Pangkat','KGB','PMK','PGP']);
			})->first();

            // Get MKG tahun dan bulan
            if($p->tmt_golongan != null) {
                $b1 = date('n', strtotime($p->tmt_golongan));
                $b2 = date('n', strtotime($tanggal));
                $t1 = date('Y', strtotime($p->tmt_golongan));
                $t2 = date('Y', strtotime($tanggal));

                if($b1 > $b2) {
                    $pegawai[$key]->mkg_bulan = $b2 + 12 - $b1;
                    $pegawai[$key]->mkg_tahun = $t2 - 1 - $t1;
                }
                else {
                    $pegawai[$key]->mkg_bulan = $b2 - $b1;
                    $pegawai[$key]->mkg_tahun = $t2 - $t1;
                }
            }
            else {
                $pegawai[$key]->mkg_tahun = 0;
                $pegawai[$key]->mkg_bulan = 0;
            }

            // Cek
            if($p->tmt_golongan != null && $pegawai[$key]->mutasi_terakhir != null) {
                $b1 = date('n', strtotime($p->tmt_golongan));
                $b2 = date('n', strtotime($pegawai[$key]->mutasi_terakhir->tmt));
                $t1 = date('Y', strtotime($p->tmt_golongan));
                $t2 = date('Y', strtotime($pegawai[$key]->mutasi_terakhir->tmt));

                if($b1 > $b2) {
                    $cek_mkg_bulan = $b2 + 12 - $b1;
                    $cek_mkg_tahun = $t2 - 1 - $t1;
                }
                else {
                    $cek_mkg_bulan = $b2 - $b1;
                    $cek_mkg_tahun = $t2 - $t1;
                }

                if($pegawai[$key]->mutasi_terakhir->perubahan->mk_tahun == $cek_mkg_tahun && $pegawai[$key]->mutasi_terakhir->perubahan->mk_bulan == $cek_mkg_bulan)
                    $pegawai[$key]->cek = 'Benar';
                else
                    $pegawai[$key]->cek = 'Salah';
            }
            else
                $pegawai[$key]->cek = 'Tidak Terdefinisi';
		}

        // View
        return view('admin/pantauan/mkg', [
            'pegawai' => $pegawai,
            'tanggal' => $tanggal,
            'tipe' => $request->query('tipe')
        ]);
    }

    /**
     * Pensiun PNS
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pensiun(Request $request)
    {
        // Get pegawai
        $pegawai = Pegawai::whereHas('status_kerja', function(Builder $query) {
            return $query->where('status','=',1);
        })->whereHas('status_kepegawaian', function(Builder $query) {
            return $query->whereIn('nama', ['CPNS','PNS']);
        })->orderBy('tmt_golongan','asc')->orderBy('jenis','asc')->get();
		foreach($pegawai as $key=>$p) {
            // Set TMT pensiun
            $bulan_pensiun = date('n', strtotime($p->tanggal_lahir)) + 1;

            if($bulan_pensiun > 12) {
                $bulan_pensiun = $bulan_pensiun - 12;
                $tahun_pensiun = date('Y', strtotime($p->tanggal_lahir)) + $p->jabfung->bup + 1;
            }
            else {
                $tahun_pensiun = date('Y', strtotime($p->tanggal_lahir)) + $p->jabfung->bup;
            }

            $pegawai[$key]->tmt_pensiun = $tahun_pensiun.'-'.($bulan_pensiun < 10 ? '0'.$bulan_pensiun : $bulan_pensiun).'-01';
		}

        // Sort
        $pegawai = $pegawai->sortBy('tmt_pensiun');

        // View
        return view('admin/pantauan/pensiun', [
            'pegawai' => $pegawai
        ]);
    }

    /**
     * Gaji Pokok
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function gajiPokok(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        if($request->query('tipe') == 1) {
            // Periode gaji pokok terakhir
            $gaji_terakhir = Gaji::whereHas('jenis_gaji', function(Builder $query) {
                return $query->where('nama','=','Gaji Induk');
            })->latest('tahun')->latest('bulan')->first();
            $tanggal = $gaji_terakhir->tahun.'-'.$gaji_terakhir->bulan.'-01';
            
            // Get pegawai
            $pegawai = Pegawai::whereHas('status_kerja', function(Builder $query) {
                return $query->where('status','=',1);
            })->whereHas('status_kepegawaian', function(Builder $query) {
                return $query->whereIn('nama', ['CPNS','PNS']);
            })->orderBy('tmt_golongan','asc')->orderBy('jenis','asc')->get();
            foreach($pegawai as $key=>$p) {
                // Get gaji pokok terakhir dari mutasi
                $pegawai[$key]->mutasi_gaji_pokok_terakhir = $p->mutasi()->first() && $p->mutasi()->first()->gaji_pokok ? $p->mutasi()->first()->gaji_pokok : null;
                
                // Get gaji pokok terakhir dari gaji induk
                $gaji_induk = $gaji_terakhir ? $p->gaji()->whereHas('jenis_gaji', function(Builder $query) {
                    return $query->where('nama','=','Gaji Induk');
                })->where('tahun','=',$gaji_terakhir->tahun)->where('bulan','=',$gaji_terakhir->bulan)->first() : null;
                $gaji_pokok = $gaji_induk ? GajiPokok::whereHas('sk', function(Builder $query) {
                    // return $query->where('status','=',1)->whereHas('jenis', function(Builder $query) {
                    return $query->whereHas('jenis', function(Builder $query) {
                        return $query->where('nama','=','Gaji Pokok PNS');
                    });
                })->where('gaji_pokok','=',$gaji_induk->gjpokok)->first() : null;
                $pegawai[$key]->gpp_gaji_pokok_terakhir = $gaji_pokok ?: null;
                
                // Cek
                $pegawai[$key]->cek = 'Beda';
                if($pegawai[$key]->mutasi_gaji_pokok_terakhir && $pegawai[$key]->gpp_gaji_pokok_terakhir) {
                    if($pegawai[$key]->mutasi_gaji_pokok_terakhir->gaji_pokok == $pegawai[$key]->gpp_gaji_pokok_terakhir->gaji_pokok)
                        $pegawai[$key]->cek = 'Sama';
                }

                // SPKGB terakhir
                if($pegawai[$key]->cek == 'Beda') {
                    $pegawai[$key]->spkgb_terakhir = $p->spkgb()->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                        return $query->where('tmt','>=',$tanggal);
                    })->first();
                }
                else
                    $pegawai[$key]->spkgb_terakhir = null;
            }
        }
        elseif($request->query('tipe') == 2) {
            // Periode gaji pokok terakhir
            $gaji_terakhir = GajiNonASN::latest('tahun')->latest('bulan')->first();
            $tanggal = $gaji_terakhir->tahun.'-'.$gaji_terakhir->bulan.'-01';
            
            // Get pegawai
            $pegawai = Pegawai::whereHas('status_kerja', function(Builder $query) {
                return $query->where('status','=',1);
            })->whereHas('status_kepegawaian', function(Builder $query) {
                return $query->whereIn('nama', ['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN','Non PNS']);
            })->orderBy('tmt_golongan','asc')->orderBy('jenis','asc')->get();
            foreach($pegawai as $key=>$p) {
                // Get gaji pokok terakhir dari mutasi
                $pegawai[$key]->mutasi_gaji_pokok_terakhir = $p->mutasi()->first() && $p->mutasi()->first()->gaji_pokok ? $p->mutasi()->first()->gaji_pokok : null;
                
                // Get gaji pokok terakhir dari gaji induk
                $gaji_induk = $gaji_terakhir ? $p->gaji_non_asn()->where('tahun','=',$gaji_terakhir->tahun)->where('bulan','=',$gaji_terakhir->bulan)->first() : null;
                $gaji_pokok = $gaji_induk ? GajiPokok::whereHas('sk', function(Builder $query) {
                    return $query->whereHas('jenis', function(Builder $query) {
                        return $query->where('nama','=','Gaji Pokok PNS');
                    });
                })->where('gaji_pokok','=',$gaji_induk->gjpokok)->first() : null;
                if($gaji_pokok == null) {
                    // Cek 80%
                    $gaji_pokok = $gaji_induk ? GajiPokok::whereHas('sk', function(Builder $query) {
                        return $query->whereHas('jenis', function(Builder $query) {
                            return $query->where('nama','=','Gaji Pokok PNS');
                        });
                    })->where('gaji_pokok','=',(100/80) * $gaji_induk->gjpokok)->first() : null;
                }
                $pegawai[$key]->gpp_gaji_pokok_terakhir = $gaji_pokok ?: null;
                
                // Cek
                $pegawai[$key]->cek = 'Beda';
                if($pegawai[$key]->mutasi_gaji_pokok_terakhir && $pegawai[$key]->gpp_gaji_pokok_terakhir) {
                    if($pegawai[$key]->mutasi_gaji_pokok_terakhir->gaji_pokok == $pegawai[$key]->gpp_gaji_pokok_terakhir->gaji_pokok)
                        $pegawai[$key]->cek = 'Sama';
                }

                // SPKGB terakhir
                if($pegawai[$key]->cek == 'Beda') {
                    $pegawai[$key]->spkgb_terakhir = $p->spkgb()->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                        return $query->where('tmt','>=',$tanggal);
                    })->first();
                }
                else
                    $pegawai[$key]->spkgb_terakhir = null;
            }
        }

        // View
        return view('admin/pantauan/gaji-pokok', [
            'pegawai' => $pegawai,
            'gaji_terakhir' => $gaji_terakhir,
            'tipe' => $request->query('tipe'),
        ]);
    }

    /**
     * Status Kepegawaian
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function statusKepegawaian(Request $request)
    {
        // Get status kepegawaian
        $status_kepegawaian = StatusKepegawaian::orderBy('persentase','desc')->get();
        foreach($status_kepegawaian as $key=>$s) {
            // Count pegawai
            $status_kepegawaian[$key]->dosen = Pegawai::whereHas('status_kerja', function(Builder $query) {
                return $query->where('status','=',1);
            })->where('status_kepeg_id','=',$s->id)->where('jenis','=',1)->count();
            $status_kepegawaian[$key]->tendik = Pegawai::whereHas('status_kerja', function(Builder $query) {
                return $query->where('status','=',1);
            })->where('status_kepeg_id','=',$s->id)->where('jenis','=',2)->count();
        }

        // View
        return view('admin/pantauan/status-kepegawaian', [
            'status_kepegawaian' => $status_kepegawaian
        ]);
    }
}
