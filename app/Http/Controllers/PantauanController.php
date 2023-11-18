<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\MutasiImport;
use App\Models\Pegawai;
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
        $pegawai = Pegawai::where('status_kerja_id','=',1)->whereIn('status_kepeg_id',[1,2])->orderBy('tmt_golongan','asc')->orderBy('jenis','asc')->get();
		foreach($pegawai as $key=>$p) {
			// Get mutasi KP / KGB / PMK terakhir
			$pegawai[$key]->mutasi_terakhir = $p->mutasi()->whereHas('jenis', function(Builder $query) {
				return $query->whereIn('nama',['Mutasi Pangkat','KGB','PMK']);
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
                    $pegawai[$key]->cek = 'Salah '.($cek_mkg_tahun).' '.($cek_mkg_bulan);
            }
            else
                $pegawai[$key]->cek = 'Tidak Terdefinisi';
		}

        // View
        return view('admin/pantauan/mkg', [
            'pegawai' => $pegawai,
            'tanggal' => $tanggal
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
            $status_kepegawaian[$key]->dosen = Pegawai::where('status_kerja_id','=',1)->where('status_kepeg_id','=',$s->id)->where('jenis','=',1)->count();
            $status_kepegawaian[$key]->tendik = Pegawai::where('status_kerja_id','=',1)->where('status_kepeg_id','=',$s->id)->where('jenis','=',2)->count();
        }

        // View
        return view('admin/pantauan/status-kepegawaian', [
            'status_kepegawaian' => $status_kepegawaian
        ]);
    }
}
