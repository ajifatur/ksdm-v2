<?php

/**
 * pegawai_spkgb()
 * check_mutasi()
 * kdanak_to_unit()
 * array_sum_range()
 * nip_baru()
 * jabatan()
 */

use Illuminate\Database\Eloquent\Builder;
use App\Models\AnakSatker;
use App\Models\GajiPokok;
use App\Models\Pegawai;
use App\Models\Jabatan;
use App\Models\SK;

// Get pegawai yang SPKGB
if(!function_exists('pegawai_spkgb')) {
    function pegawai_spkgb($mkg, $golru, $tahun, $bulan, $tanggal, $tipe) {
        // Set TMT
		$tmt = [];
        foreach($mkg as $m) {
			array_push($tmt, ($tahun - $m).'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01');
        }
		
		// Get pegawai berdasarkan golru
		$pegawai = Pegawai::whereHas('golru', function(Builder $query) use ($golru) {
			return $query->whereIn('golru_id',$golru);
		})->where('status_kerja_id','=',1)->whereHas('status_kepegawaian', function(Builder $query) use($tipe) {
            if($tipe == 1)
                return $query->whereIn('nama', ['PNS','CPNS']);
            elseif($tipe == 2)
                return $query->whereIn('nama', ['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN']);
        })->whereIn('tmt_golongan',$tmt)->orderBy('tmt_golongan','asc')->orderBy('jenis','asc')->get();

		foreach($pegawai as $key=>$p) {
			// Get mutasi sebelumnya
			$pegawai[$key]->mutasi_sebelum = $p->mutasi()->whereHas('jenis', function(Builder $query) use($tipe) {
                if($tipe == 1)
				    return $query->whereIn('nama',['Mutasi CPNS ke PNS','Mutasi Pangkat','KGB','PMK','PGP']);
                elseif($tipe == 2)
				    return $query->whereIn('nama',['Peralihan BLU ke PTNBH','Mutasi Pangkat','KGB','PMK','PGP']);
			})->where('tmt','<',$tanggal)->first();

            // Get SPKGB
			$pegawai[$key]->mutasi_spkgb = $p->mutasi()->has('spkgb')->whereHas('jenis', function(Builder $query) {
				return $query->where('nama','=','KGB');
			})->where('tmt','=',$tanggal)->first();

            // Get gaji pokok lama
            $pegawai[$key]->gaji_pokok_lama = $pegawai[$key]->mutasi_sebelum ? $pegawai[$key]->mutasi_sebelum->gaji_pokok : $p->mutasi()->first()->gaji_pokok;

            // Set masa kerja baru
            $mk_baru = $tahun - date('Y', strtotime($p->tmt_golongan));
    
            // Set gaji pokok baru
            $sk_gaji_pns = SK::where('jenis_id','=',5)->where('status','=',1)->first();
            $pegawai[$key]->gaji_pokok_baru = $pegawai[$key]->gaji_pokok_lama ? GajiPokok::where('sk_id','=',$sk_gaji_pns->id)->where('nama','=',substr($pegawai[$key]->gaji_pokok_lama->nama,0,2).($mk_baru < 10 ? '0'.$mk_baru : $mk_baru))->first() : null;
		}

        return $pegawai;
    }
}

// Cek mutasi
if(!function_exists('check_mutasi')) {
    function check_mutasi($pegawai, $bulan, $tahun) {
        // Get mutasi terbaru
        $mutasi = $pegawai->mutasi()->whereHas('jenis', function(Builder $query) {
            return $query->where('remun','=',1);
        })->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kolektif','=',0)->first();

        // Set result
        $result = false;
        
        // Get mutasi sebelumnya
        if($mutasi) {
            $mutasi_sebelum = $pegawai->mutasi()->whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->where('id','!=',$mutasi->id)->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
        
            // Jika rangkap jabatan, mengecek perubahan
            if($mutasi_sebelum && count($mutasi_sebelum->detail) > 0) {
                // Cek jabatan
                $jabatan = [];
                foreach($mutasi_sebelum->detail as $d) {
                    if($d->jabatan)
                        array_push($jabatan, $d->jabatan->nama);
                }

                $id = '';
                foreach($mutasi->detail as $d) {
                    if($d->status == 1 && !in_array($d->jabatan->nama, $jabatan)) {
                        $id = $d->jabatan->nama;
                    }
                }

                if($id != '') $result = true;
                else {
                    // Cek unit
                    $unit = [];
                    foreach($mutasi_sebelum->detail as $d) {
                        if($d->jabatan)
                            array_push($unit, $d->unit->nama);
                    }

                    foreach($mutasi->detail as $d) {
                        if($d->status == 1 && !in_array($d->unit->nama, $unit)) {
                            $id = $d->unit->nama;
                        }
                    }
                    if($id != '') $result = true;
                    else $result = false;
                }
            }
            else $result = true;
        }
        
        return $result;
    }
}

// Konversi kode anak satker ke unit
if(!function_exists('kdanak_to_unit')) {
    function kdanak_to_unit($kdanak, $pegawai_id) {
        // Get anak satker
        $anak_satker = AnakSatker::where('kode','=',$kdanak)->first();

        if($anak_satker->unit_id != 0)
            return $anak_satker->unit_id;
        else {
            // Get pegawai
            $pegawai = Pegawai::find($pegawai_id);

            return $pegawai->unit_id;
        }
    }
}

// Array sum range
if(!function_exists('array_sum_range')) {
    function array_sum_range($array, $first, $last) {
        $sum = 0;
        for($i=$first; $i<=$last; $i++) {
            $sum += $array[$i];
        }
        return $sum;
    }
}

// NIP baru
if(!function_exists('nip_baru')) {
    function nip_baru($pegawai) {
        return $pegawai->npu != null ? $pegawai->npu : $pegawai->nip;
    }
}

// Jabatan
if(!function_exists('jabatan')) {
    function jabatan($jabatan) {
        if($jabatan) {
            if($jabatan->grup && $jabatan->grup->nama == 'Koordinator Program Studi')
                return 'Koordinator Program Studi';
            elseif($jabatan->sub != '-')
                return $jabatan->sub;
            elseif($jabatan->sub == '-')
                return $jabatan->nama;
            else
                return '-';
        }
        else
            return '-';
    }
}