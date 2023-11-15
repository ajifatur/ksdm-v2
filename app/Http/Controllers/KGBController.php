<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\MutasiImport;
use App\Models\Pegawai;
use App\Models\Mutasi;
use App\Models\MutasiDetail;
use App\Models\Perubahan;
use App\Models\SK;
use App\Models\JenisMutasi;
use App\Models\Golru;
use App\Models\GajiPokok;

class KGBController extends Controller
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
		$tmt = [];
		
		// Get TMT golongan III dan IV
		for($i = $tahun; $i >= ($tahun - 32); $i-=2) {
			array_push($tmt, $i.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01');
		}
		
		// Get pegawai berdasarkan TMT golongan
		$pegawai = Pegawai::whereHas('golru', function(Builder $query) {
			return $query->whereIn('golongan_id',[3,4]);
		})->where('status_kerja_id','=',1)->whereIn('status_kepeg_id',[1,2])->whereIn('tmt_golongan',$tmt)->orderBy('tmt_golongan','asc')->get();
		foreach($pegawai as $key=>$p) {
			// Get mutasi KP / KGB sebelumnya
			$pegawai[$key]->mutasi_sebelum = $p->mutasi()->whereHas('jenis', function(Builder $query) {
				return $query->whereIn('nama',['Mutasi Pangkat','KGB','PMK']);
			})->where('tmt','<',$tanggal)->first();
		}

        // View
        return view('admin/kgb/index', [
            'pegawai' => $pegawai,
            'bulan' => $bulan,
            'tahun' => $tahun,
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

        // Get SK
        $sk = SK::where('jenis_id','=',1)->where('status','=',1)->first();

        // Get jenis mutasi
        $jenis_mutasi = JenisMutasi::where('nama','=','Mutasi Pangkat')->first();

		$array = Excel::toArray(new MutasiImport, public_path('storage/KP Oktober 2023.xlsx'));

        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {                    
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[0])->first();

                    // Get golru
                    $golru = Golru::where('golongan_id','=',substr($data[8],0,1))->where('ruang','=',substr($data[8],1,1))->first();

                    // Get gaji pokok
                    $mkg = $data[6] > 32 ? 32 : $data[6];
                    $gaji_pokok = GajiPokok::where('golru_id','=',$golru->id)->where('nama','=',$data[8].($mkg < 10 ? '0'.$mkg : $mkg))->first();

                    // Simpan mutasi
                    $mutasi = Mutasi::where('pegawai_id','=',$pegawai->id)->where('jenis_id','=',$jenis_mutasi->id)->where('tmt','=',DateTimeExt::change($data[5]))->first();
                    if(!$mutasi) $mutasi = new Mutasi;
                    $mutasi->pegawai_id = $pegawai->id;
                    $mutasi->sk_id = $sk->id;
                    $mutasi->jenis_id = $jenis_mutasi->id;
                    $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                    $mutasi->golru_id = $golru->id;
                    $mutasi->gaji_pokok_id = $gaji_pokok->id;
                    $mutasi->bulan = date('n', strtotime(DateTimeExt::change($data[5])));
                    $mutasi->tahun = date('Y', strtotime(DateTimeExt::change($data[5])));
                    $mutasi->uraian = $jenis_mutasi->nama.' '.$golru->nama.' '.$data[6].' tahun '.$data[7].' bulan';
                    $mutasi->tmt = DateTimeExt::change($data[5]);
                    $mutasi->remun_penerimaan = 0;
                    $mutasi->remun_gaji = 0;
                    $mutasi->remun_insentif = 0;
                    $mutasi->save();

                    // Set TMT golongan
                    $tmt_golongan = date('Y-m-d', strtotime("-".$data[6]." year", strtotime($mutasi->tmt)));
                    $tmt_golongan = date('Y-m-d', strtotime("-".$data[7]." month", strtotime($tmt_golongan)));

                    // Simpan pegawai
                    $pegawai->golongan_id = $golru->golongan_id;
                    $pegawai->golru_id = $golru->id;
                    $pegawai->tmt_golongan = $tmt_golongan;
                    $pegawai->save();

                    // Get mutasi pegawai
                    if($mutasi->detail()->count() <= 0) {
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

                    // Simpan perubahan
                    $perubahan = $mutasi->perubahan;
                    if(!$perubahan) $perubahan = new Perubahan;
                    $perubahan->mutasi_id = $mutasi->id;
                    $perubahan->sk_id = $gaji_pokok->sk_id;
                    $perubahan->pejabat_id = $data[4];
                    $perubahan->no_sk = $data[2];
                    $perubahan->tanggal_sk = DateTimeExt::change($data[3]);
                    $perubahan->mk_tahun = $data[6];
                    $perubahan->mk_bulan = $data[7];
                    $perubahan->tmt = DateTimeExt::change($data[5]);
                    $perubahan->save();
                }
            }
        }
    }
}
