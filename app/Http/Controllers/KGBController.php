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
use App\Models\SPKGB;
use App\Models\SK;
use App\Models\JenisMutasi;
use App\Models\Golru;
use App\Models\GajiPokok;
use App\Models\Pejabat;

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
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
		$tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
		$tmt = [];
		
		// Get TMT golongan III dan IV
		for($i = $tahun; $i >= ($tahun - 32); $i-=2) {
			array_push($tmt, $i.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01');
		}

        // Get pegawai
		$pegawai = Pegawai::whereHas('golru', function(Builder $query) {
			return $query->whereIn('golongan_id',[3,4]);
		})->where('status_kerja_id','=',1)->whereIn('status_kepeg_id',[1,2])->whereIn('tmt_golongan',$tmt)->findOrFail($id);

        // Get golru
        $golru = Golru::all();

        // Get gaji pokok
        if($pegawai->status_kepeg_id == 1 || $pegawai->status_kepeg_id == 2)
            $gaji_pokok = Golru::find($pegawai->golru_id)->gaji_pokok;
        else
            $gaji_pokok = [];

        // Get pejabat
        $pejabat = Pejabat::orderBy('num_order','asc')->get();

        // Get mutasi
        $mutasi = $pegawai->mutasi()->first();

        // Get mutasi sebelum
        $mutasi_sebelum = $pegawai->mutasi()->whereHas('jenis', function(Builder $query) {
            return $query->whereIn('nama',['Mutasi Pangkat','KGB','PMK']);
        })->where('tmt','<',$tanggal)->first();

        // Set masa kerja baru
        $mk_baru = $tahun - date('Y', strtotime($pegawai->tmt_golongan));

        // Set gaji pokok baru
        $sk_gaji_pns = SK::where('jenis_id','=',5)->where('status','=',1)->first();
        $gaji_pokok_baru = GajiPokok::where('sk_id','=',$sk_gaji_pns->id)->where('nama','=',substr($mutasi->gaji_pokok->nama,0,2).$mk_baru)->first();

        // View
        return view('admin/kgb/create', [
            'pegawai' => $pegawai,
            'golru' => $golru,
            'gaji_pokok' => $gaji_pokok,
            'pejabat' => $pejabat,
            'mutasi' => $mutasi,
            'mutasi_sebelum' => $mutasi_sebelum,
            'mk_baru' => $mk_baru,
            'gaji_pokok_baru' => $gaji_pokok_baru,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tanggal' => $tanggal,
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

        // Get SK
        $sk_remun = SK::where('jenis_id','=',1)->where('status','=',1)->first();
        $sk_gaji_pns = SK::where('jenis_id','=',5)->where('status','=',1)->first();

        // Get jenis mutasi
        $jenis_mutasi = JenisMutasi::where('nama','=','KGB')->first();

        // Validation
        $validator = Validator::make($request->all(), [
            'no_sk_baru' => 'required',
            'gaji_pokok' => $request->mutasi_sebelum_id != 0 ? 'required' : '',
            'no_sk' => $request->mutasi_sebelum_id != 0 ? 'required' : '',
            'tanggal_sk' => $request->mutasi_sebelum_id != 0 ? 'required' : '',
            'mk_tahun' => $request->mutasi_sebelum_id != 0 ? 'required' : '',
            'mk_bulan' => $request->mutasi_sebelum_id != 0 ? 'required' : '',
            'pejabat' => $request->mutasi_sebelum_id != 0 ? 'required' : '',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Get gaji pokok
            if($request->mutasi_sebelum_id != 0)
                $gaji_pokok = GajiPokok::where('sk_id','=',$sk_gaji_pns->id)->find($request->gaji_pokok_baru);
            else
                $gaji_pokok = GajiPokok::where('sk_id','=',$sk_gaji_pns->id)->find($request->gaji_pokok);

            // Get mutasi sebelum
            $mutasi_sebelum = Mutasi::find($request->mutasi_sebelum_id);

            // Simpan mutasi
            $mutasi = new Mutasi;
            $mutasi->pegawai_id = $pegawai->id;
            $mutasi->sk_id = $sk_remun->id;
            $mutasi->jenis_id = $jenis_mutasi->id;
            $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
            $mutasi->golru_id = $gaji_pokok->golru_id;
            $mutasi->gaji_pokok_id = $gaji_pokok->id;
            $mutasi->bulan = date('n', strtotime($request->tanggal));
            $mutasi->tahun = date('Y', strtotime($request->tanggal));
            $mutasi->uraian = $jenis_mutasi->nama.' '.$gaji_pokok->golru->nama.' '.($request->mutasi_sebelum_id != 0 ? $request->mk_tahun_baru : $request->mk_tahun).' tahun '.($request->mutasi_sebelum_id != 0 ? 0 : $request->mk_bulan).' bulan';
            $mutasi->tmt = $request->tanggal;
            $mutasi->remun_penerimaan = 0;
            $mutasi->remun_gaji = 0;
            $mutasi->remun_insentif = 0;
            $mutasi->save();

            // Simpan mutasi detail
            $m = $pegawai->mutasi()->where('jenis_id','=',1)->first();
            if($m) {
                foreach($m->detail as $d) {
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
            $perubahan->pejabat_id = $request->mutasi_sebelum_id != 0 ? 4 : $request->pejabat;
            $perubahan->no_sk = $request->no_sk_baru;
            $perubahan->tanggal_sk = date('Y-m-d');
            $perubahan->mk_tahun = $request->mutasi_sebelum_id != 0 ? $request->mk_tahun_baru : $request->mk_tahun;
            $perubahan->mk_bulan = $request->mutasi_sebelum_id != 0 ? 0 : $request->mk_bulan;
            $perubahan->tmt = $request->tanggal;
            $perubahan->save();

            // Simpan SPKGB
            $spkgb = new SPKGB;
            $spkgb->mutasi_id = $mutasi->id;
            $spkgb->mutasi_sebelum_id = $mutasi_sebelum ? $mutasi_sebelum->id : 0;
            $spkgb->save();
        }
    }

    /**
     * Print PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function print(Request $request, $id)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
		$tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
		$tmt = [];
		
		// Get TMT golongan III dan IV
		for($i = $tahun; $i >= ($tahun - 32); $i-=2) {
			array_push($tmt, $i.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01');
		}

        // Get pegawai
		$pegawai = Pegawai::whereHas('golru', function(Builder $query) {
			return $query->whereIn('golongan_id',[3,4]);
		})->where('status_kerja_id','=',1)->whereIn('status_kepeg_id',[1,2])->whereIn('tmt_golongan',$tmt)->findOrFail($id);

        // Get mutasi
        $mutasi = $pegawai->mutasi()->first();

        // Get mutasi sebelum
        $mutasi_sebelum = $pegawai->mutasi()->whereHas('jenis', function(Builder $query) {
            return $query->whereIn('nama',['Mutasi Pangkat','KGB','PMK']);
        })->where('tmt','<',$tanggal)->first();

        // Set masa kerja baru
        $mk_baru = $tahun - date('Y', strtotime($pegawai->tmt_golongan));
		
		// Set title
		$title = 'KGB 2024-01-01 a.n. '.$pegawai->nama;
		
        // PDF
        $pdf = \PDF::loadView('admin/kgb/print', [
            'title' => $title,
            'pegawai' => $pegawai,
            'mutasi' => $mutasi,
            'mutasi_sebelum' => $mutasi_sebelum,
            'mk_baru' => $mk_baru,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tanggal' => $tanggal,
        ]);
        $pdf->setPaper([0, 0 , 612, 935]);
        return $pdf->stream($title.'.pdf');
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
