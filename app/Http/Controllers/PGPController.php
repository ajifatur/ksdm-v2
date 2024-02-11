<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\ByStartRowImport;
use App\Models\GajiPokok;
use App\Models\Mutasi;
use App\Models\MutasiDetail;
use App\Models\Pegawai;
use App\Models\Perubahan;
use App\Models\SPKGB;

class PGPController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {		
		// Get TMT
		$tmt = Mutasi::whereHas('jenis', function(Builder $query) {
			return $query->where('nama','=','PGP');
		})->orderBy('tmt','desc')->groupBy('tmt')->pluck('tmt')->toArray();

        // Get mutasi PGP
        $mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
            return $query->where('nama','=','PGP');
        })->where('tmt','=',in_array($request->query('tmt'),$tmt) ? $request->query('tmt') : $tmt[0])->get();

        // View
        return view('admin/pgp/index', [
            'mutasi' => $mutasi,
			'tmt' => $tmt
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

        // // Get PGP
        // $pgp = Mutasi::whereHas('jenis', function(Builder $query) {
        //     return $query->where('nama','=','PGP');
        // })->get();
        // foreach($pgp as $p) {
        //     // Update perubahan
        //     $perubahan = Perubahan::where('mutasi_id','=',$p->id)->first();
        //     $bulan = $perubahan->mk_bulan;
        //     $perubahan->mk_bulan = ($bulan == 0) ? 11 : $perubahan->mk_bulan - 1;
        //     $perubahan->mk_tahun = ($bulan == 0) ? $perubahan->mk_tahun - 1 : $perubahan->mk_tahun;
        //     $perubahan->save();
        // }
        // return;

        // // Update SPKGB Maret 2024
        // // Get SPKGB
        // $spkgb = SPKGB::whereHas('mutasi', function(Builder $query) {
        //     return $query->where('tmt','=','2024-03-01');
        // })->get();
        // foreach($spkgb as $s) {
        //     // Get PGP
        //     $pgp = Mutasi::where('pegawai_id','=',$s->pegawai_id)->where('jenis_id','=',18)->first();

        //     // Get gaji pokok
        //     $gaji_pokok = GajiPokok::where('sk_id','=',14)->where('nama','=', $s->mutasi->gaji_pokok->nama)->first();

        //     // Update SPKGB
        //     $update = SPKGB::find($s->id);
        //     $update->mutasi_sebelum_id = $pgp->id;
        //     $update->save();

        //     // Update mutasi
        //     $s->mutasi->gaji_pokok_id = $gaji_pokok->id;
        //     $s->mutasi->save();

        //     // Update perubahan
        //     $s->mutasi->perubahan->no_sk = '-';
        //     $s->mutasi->perubahan->tanggal_sk = '2024-02-05';
        //     $s->mutasi->perubahan->save();
        // }
        // return;

        $sk_pgp = 14; // SK PGP 2024
        $sk_remun = 12; // SK Remun 2024
        $jenis_mutasi = 18; // PGP
        $tmt = '2024-01-01'; // TMT

        // Set file
        $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/PGP 2024.xlsx'));

        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[0])->first();
                    // if(!$pegawai) array_push($error, $data[1]);

                    // Get gaji pokok
                    $gaji_pokok = GajiPokok::where('sk_id','=',14)->where('nama','=',$data[4])->first();

                    // Simpan mutasi
                    $mutasi = Mutasi::where('pegawai_id','=',$pegawai->id)->where('jenis_id','=',$jenis_mutasi)->where('tmt','=',$tmt)->first();
                    if(!$mutasi) $mutasi = new Mutasi;
                    $mutasi->pegawai_id = $pegawai->id;
                    $mutasi->sk_id = $sk_remun;
                    $mutasi->jenis_id = $jenis_mutasi;
                    $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                    $mutasi->golru_id = $gaji_pokok->golru_id;
                    $mutasi->gaji_pokok_id = $gaji_pokok->id;
                    $mutasi->bulan = date('n', strtotime($tmt));
                    $mutasi->tahun = date('Y', strtotime($tmt));
                    $mutasi->uraian = 'PGP 2024';
                    $mutasi->tmt = $tmt;
                    $mutasi->remun_penerimaan = 0;
                    $mutasi->remun_gaji = 0;
                    $mutasi->remun_insentif = 0;
                    $mutasi->kolektif = 1;
                    $mutasi->save();

                    // Simpan mutasi detail
                    $m = $pegawai->mutasi()->where('jenis_id','=',1)->first();
                    if($m && count($mutasi->detail) == 0) {
                        foreach($m->detail as $d) {
                            $detail = new MutasiDetail;
                            $detail->mutasi_id = $mutasi->id;
                            $detail->jabatan_id = $d->jabatan_id;
                            $detail->jabatan_dasar_id = $d->jabatan_dasar_id;
                            $detail->unit_id = $d->unit_id;
                            $detail->layer_id = $d->layer_id;
                            $detail->angkatan_id = $d->angkatan_id;
                            $detail->status = $d->status;
                            $detail->save();
                        }
                    }

                    // Simpan perubahan
                    if(!$mutasi->perubahan) {
                        $perubahan = new Perubahan;
                        $perubahan->mutasi_id = $mutasi->id;
                        $perubahan->sk_id = $gaji_pokok->sk_id;
                        $perubahan->pejabat_id = 4;
                        $perubahan->no_sk = '-';
                        $perubahan->tanggal_sk = '2024-02-02';
                        $perubahan->mk_tahun = $data[6];
                        $perubahan->mk_bulan = $data[7];
                        $perubahan->tmt = $tmt;
                        $perubahan->save();
                    }
                }
            }
        }
        var_dump($error);
    }
}
