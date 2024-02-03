<?php

use Illuminate\Database\Eloquent\Builder;
use App\Models\GajiPokok;
use App\Models\Pegawai;
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