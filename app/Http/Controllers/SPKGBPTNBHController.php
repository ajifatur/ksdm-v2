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

class SPKGBPTNBHController extends Controller
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

        if($request->query('bulan') == null && $request->query('tahun') == null) {
            // Set 2 bulan berikutnya
            $tanggal = date('Y-m-d', strtotime("+2 month", strtotime($tanggal)));
            $bulan = date('n', strtotime($tanggal));
            $tahun = date('Y', strtotime($tanggal));
        }

        // Get SPKGB tambahan
        $spkgb = SPKGB::whereHas('mutasi', function(Builder $query) use ($bulan, $tahun) {
            return $query->where('bulan','=',$bulan)->where('tahun','=',$tahun);
        })->whereNotIn('pegawai_id', array_merge(
            $this->get_pegawai([2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32], [9,10,11,12,13,14,15,16,17], $tahun, $bulan, $tanggal)->pluck('id')->toArray(),
            $this->get_pegawai([3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33], [5,6,7,8], $tahun, $bulan, $tanggal)->pluck('id')->toArray(),
            $this->get_pegawai([3,5,7,9,11,13,15,17,19,21,23,25,27], [2,3,4], $tahun, $bulan, $tanggal)->pluck('id')->toArray(),
            $this->get_pegawai([2,4,6,8,10,12,14,16,18,20,22,24,26], [1], $tahun, $bulan, $tanggal)->pluck('id')->toArray(),
        ))->get();

        // View
        return view('admin/spkgb/ptnbh/index', [
            'pegawai' => [
                'pegawai_gol_iii_iv' => $this->get_pegawai([2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32], [9,10,11,12,13,14,15,16,17], $tahun, $bulan, $tanggal),
                'pegawai_golru_ii_a_d' => $this->get_pegawai([3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33], [5,6,7,8], $tahun, $bulan, $tanggal),
                'pegawai_golru_i_b_d' => $this->get_pegawai([3,5,7,9,11,13,15,17,19,21,23,25,27], [2,3,4], $tahun, $bulan, $tanggal),
                'pegawai_golru_i_a' => $this->get_pegawai([2,4,6,8,10,12,14,16,18,20,22,24,26], [1], $tahun, $bulan, $tanggal),
            ],
            'bulan' => $bulan,
            'tahun' => $tahun,
            'spkgb' => $spkgb,
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

        // Get pegawai
		$pegawai = Pegawai::findOrFail($id);

        // Get jenis mutasi
        $jenis_mutasi = JenisMutasi::whereIn('nama',['Peralihan BLU ke PTNBH','Mutasi Pangkat','KGB','PMK'])->get();

        // Get golru
        $golru = Golru::all();

        // Get gaji pokok
        if(in_array($pegawai->status_kepegawaian->nama, ['BLU','Calon Pegawai Tetap','Pegawai Tetap']))
            $gaji_pokok = Golru::find($pegawai->golru_id)->gaji_pokok;
        else
            $gaji_pokok = [];

        // Get pejabat
        $pejabat = Pejabat::orderBy('num_order','asc')->get();

        // Get mutasi
        $mutasi = $pegawai->mutasi()->first();

        // Get mutasi sebelum
        $mutasi_sebelum = $pegawai->mutasi()->whereHas('jenis', function(Builder $query) {
            return $query->whereIn('nama',['Peralihan BLU ke PTNBH','Mutasi Pangkat','KGB','PMK']);
        })->where('tmt','<',$tanggal)->first();

        // Set masa kerja baru
        $mk_baru = $tahun - date('Y', strtotime($pegawai->tmt_golongan));

        // Set gaji pokok baru
        $sk_gaji_pns = SK::where('jenis_id','=',5)->where('status','=',1)->first();
        $gaji_pokok_baru = GajiPokok::where('sk_id','=',$sk_gaji_pns->id)->where('nama','=',substr($mutasi->gaji_pokok->nama,0,2).($mk_baru < 10 ? '0'.$mk_baru : $mk_baru))->first();

        // View
        return view('admin/spkgb/ptnbh/create', [
            'pegawai' => $pegawai,
            'jenis_mutasi' => $jenis_mutasi,
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
            'tanggal_sk_baru' => 'required',
            'jenis_mutasi' => $request->mutasi_sebelum_id == 0 ? 'required' : '',
            'golru' => $request->mutasi_sebelum_id == 0 ? 'required' : '',
            'gaji_pokok' => $request->mutasi_sebelum_id == 0 ? 'required' : '',
            'no_sk' => $request->mutasi_sebelum_id == 0 ? 'required' : '',
            'tanggal_sk' => $request->mutasi_sebelum_id == 0 ? 'required' : '',
            'mk_tahun' => $request->mutasi_sebelum_id == 0 ? 'required' : '',
            'mk_bulan' => $request->mutasi_sebelum_id == 0 ? 'required' : '',
            'pejabat' => $request->mutasi_sebelum_id == 0 ? 'required' : '',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Get gaji pokok
            $gaji_pokok = GajiPokok::where('sk_id','=',$sk_gaji_pns->id)->find($request->gaji_pokok_baru);
            $gaji_pokok_sebelum = GajiPokok::where('sk_id','=',$sk_gaji_pns->id)->find($request->gaji_pokok);

            // Get jenis mutasi sebelum
            $jenis_mutasi_sebelum = JenisMutasi::find($request->jenis_mutasi);

            // Get / simpan mutasi sebelum
            $mutasi_sebelum = Mutasi::find($request->mutasi_sebelum_id);
            if($request->mutasi_sebelum_id == 0) {
                $mutasi_sebelum = new Mutasi;
                $mutasi_sebelum->pegawai_id = $pegawai->id;
                $mutasi_sebelum->sk_id = 0;
                $mutasi_sebelum->jenis_id = $jenis_mutasi_sebelum->id;
                $mutasi_sebelum->status_kepeg_id = $pegawai->status_kepeg_id;
                $mutasi_sebelum->golru_id = $gaji_pokok_sebelum->golru_id;
                $mutasi_sebelum->gaji_pokok_id = $gaji_pokok_sebelum->id;
                $mutasi_sebelum->bulan = date('n', strtotime(DateTimeExt::change($request->tmt_sebelum)));
                $mutasi_sebelum->tahun = date('Y', strtotime(DateTimeExt::change($request->tmt_sebelum)));
                $mutasi_sebelum->uraian = $jenis_mutasi_sebelum->nama.' '.$gaji_pokok_sebelum->golru->nama.' '.$request->mk_tahun.' tahun '.$request->mk_bulan.' bulan';
                $mutasi_sebelum->tmt = DateTimeExt::change($request->tmt_sebelum);
                $mutasi_sebelum->remun_penerimaan = 0;
                $mutasi_sebelum->remun_gaji = 0;
                $mutasi_sebelum->remun_insentif = 0;
                $mutasi_sebelum->save();
            }

            // Simpan perubahan sebelum
            if($request->mutasi_sebelum_id == 0) {
                $perubahan_sebelum = new Perubahan;
                $perubahan_sebelum->mutasi_id = $mutasi_sebelum->id;
                $perubahan_sebelum->sk_id = $gaji_pokok_sebelum->sk_id;
                $perubahan_sebelum->pejabat_id = $request->pejabat;
                $perubahan_sebelum->no_sk = $request->no_sk;
                $perubahan_sebelum->tanggal_sk = DateTimeExt::change($request->tanggal_sk);
                $perubahan_sebelum->mk_tahun = $request->mk_tahun;
                $perubahan_sebelum->mk_bulan = $request->mk_bulan;
                $perubahan_sebelum->tmt = DateTimeExt::change($request->tmt_sebelum);
                $perubahan_sebelum->save();
            }

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
            $mutasi->uraian = $jenis_mutasi->nama.' '.$gaji_pokok->golru->nama.' '.$request->mk_tahun_baru.' tahun 0 bulan';
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
            $perubahan = new Perubahan;
            $perubahan->mutasi_id = $mutasi->id;
            $perubahan->sk_id = $gaji_pokok->sk_id;
            $perubahan->pejabat_id = 4;
            $perubahan->no_sk = $request->no_sk_baru;
            $perubahan->tanggal_sk = DateTimeExt::change($request->tanggal_sk_baru);
            $perubahan->mk_tahun = $request->mk_tahun_baru;
            $perubahan->mk_bulan = 0;
            $perubahan->tmt = $request->tanggal;
            $perubahan->save();

            // Get penandatangan
            $ttd = Pegawai::where('nama','=','Moh Khoiruddin')->first();

            // Simpan SPKGB
            $spkgb = new SPKGB;
            $spkgb->mutasi_id = $mutasi->id;
            $spkgb->mutasi_sebelum_id = $mutasi_sebelum->id;
            $spkgb->pegawai_id = $pegawai->id;
            $spkgb->jabfung_id = $pegawai->jabfung_id;
            $spkgb->jabstruk_id = $pegawai->jabstruk_id;
            $spkgb->unit_id = $pegawai->unit_id;
            $spkgb->ttd_id = $ttd->id;
            $spkgb->nama = $request->nama;
            $spkgb->save();
        }

        // Redirect
        return redirect()->route('admin.spkgb.ptnbh.index', ['bulan' => date('n', strtotime($request->tanggal)), 'tahun' => date('Y', strtotime($request->tanggal))])->with(['message' => 'Berhasil menambah data.']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        // Get SPKGB
        $spkgb = SPKGB::whereHas('mutasi', function(Builder $query) {
            return $query->has('perubahan');
        })->findOrFail($id);

        // Get jenis mutasi
        $jenis_mutasi = JenisMutasi::whereIn('nama',['Peralihan BLU ke PTNBH','Mutasi Pangkat','KGB','PMK'])->get();

        // Get golru
        $golru = Golru::all();

        // Get gaji pokok
        if(in_array($spkgb->pegawai->status_kepegawaian->nama, ['BLU','Calon Pegawai Tetap','Pegawai Tetap']))
            $gaji_pokok = Golru::find($spkgb->mutasi->golru_id)->gaji_pokok;
        else
            $gaji_pokok = [];

        // Get pejabat
        $pejabat = Pejabat::orderBy('num_order','asc')->get();

        // View
        return view('admin/spkgb/ptnbh/edit', [
            'spkgb' => $spkgb,
            'jenis_mutasi' => $jenis_mutasi,
            'golru' => $golru,
            'gaji_pokok' => $gaji_pokok,
            'pejabat' => $pejabat,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'no_sk_baru' => 'required',
            'tanggal_sk_baru' => 'required',
            'jenis_mutasi' => 'required',
            'golru' => 'required',
            'gaji_pokok' => 'required',
            'no_sk' => 'required',
            'tanggal_sk' => 'required',
            'mk_tahun' => 'required',
            'mk_bulan' => 'required',
            'pejabat' => 'required',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Get SPKGB
            $spkgb = SPKGB::findOrFail($request->id);

            // Get gaji pokok
            $gaji_pokok = GajiPokok::find($request->gaji_pokok);

            // Get jenis mutasi
            $jenis_mutasi = JenisMutasi::find($request->jenis_mutasi);

            // Update mutasi sebelum
            $mutasi_sebelum = $spkgb->mutasi_sebelum;
            $mutasi_sebelum->jenis_id = $jenis_mutasi->id;
            $mutasi_sebelum->golru_id = $gaji_pokok->golru_id;
            $mutasi_sebelum->gaji_pokok_id = $gaji_pokok->id;
            $mutasi_sebelum->bulan = date('n', strtotime(DateTimeExt::change($request->tmt_sebelum)));
            $mutasi_sebelum->tahun = date('Y', strtotime(DateTimeExt::change($request->tmt_sebelum)));
            $mutasi_sebelum->uraian = $jenis_mutasi->nama.' '.$gaji_pokok->golru->nama.' '.$request->mk_tahun.' tahun '.$request->mk_bulan.' bulan';
            $mutasi_sebelum->tmt = DateTimeExt::change($request->tmt_sebelum);
            $mutasi_sebelum->save();

            // Update perubahan sebelum
            $perubahan_sebelum = $mutasi_sebelum->perubahan;
            $perubahan_sebelum->pejabat_id = $request->pejabat;
            $perubahan_sebelum->no_sk = $request->no_sk;
            $perubahan_sebelum->tanggal_sk = DateTimeExt::change($request->tanggal_sk);
            $perubahan_sebelum->mk_tahun = $request->mk_tahun;
            $perubahan_sebelum->mk_bulan = $request->mk_bulan;
            $perubahan_sebelum->tmt = DateTimeExt::change($request->tmt_sebelum);
            $perubahan_sebelum->save();

            // Update perubahan
            $perubahan = $spkgb->mutasi->perubahan;
            $perubahan->no_sk = $request->no_sk_baru;
            $perubahan->tanggal_sk = DateTimeExt::change($request->tanggal_sk_baru);
            $perubahan->save();
        }

        // Redirect
        return redirect()->route('admin.spkgb.ptnbh.index', ['bulan' => date('n', strtotime($spkgb->mutasi->tmt)), 'tahun' => date('Y', strtotime($spkgb->mutasi->tmt))])->with(['message' => 'Berhasil mengupdate data.']);
    }

    /**
     * Monitoring.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monitoring(Request $request)
    {
        $tahun = $request->query('tahun') ?: date('Y');
        $data = [];
        $total = 0;
        for($i=1; $i<=12; $i++) {
            // Get SPKGB
            $spkgb = SPKGB::whereHas('pegawai', function(Builder $query) {
                return $query->whereHas('status_kepegawaian', function(Builder $query) {
                    return $query->whereIn('nama',['BLU','Calon Pegawai Tetap','Pegawai Tetap']);
                });
            })->whereHas('mutasi', function(Builder $query) use ($i, $tahun) {
                return $query->has('perubahan')->where('bulan','=',$i)->where('tahun','=',$tahun);
            })->count();

            // Increment total
            $total += $spkgb;

            // Push to array
            array_push($data, [
                'bulan' => $i,
                'nama' => DateTimeExt::month($i),
                'spkgb' => $spkgb
            ]);
        }

        // View
        return view('admin/spkgb/ptnbh/monitoring', [
            'tahun' => $tahun,
            'data' => $data,
            'total' => $total
        ]);
    }

    public function get_pegawai($mkg, $golru, $tahun, $bulan, $tanggal) {
        // Set TMT
		$tmt = [];
        foreach($mkg as $m) {
			array_push($tmt, ($tahun - $m).'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01');
        }
		
		// Get pegawai berdasarkan golru
		$pegawai = Pegawai::whereHas('golru', function(Builder $query) use ($golru) {
			return $query->whereIn('golru_id',$golru);
		})->where('status_kerja_id','=',1)->whereHas('status_kepegawaian', function(Builder $query) {
            return $query->whereIn('nama',['BLU','Calon Pegawai Tetap','Pegawai Tetap']);
        })->whereIn('tmt_golongan',$tmt)->orderBy('tmt_golongan','asc')->orderBy('jenis','asc')->get();
		foreach($pegawai as $key=>$p) {
			// Get mutasi KP / KGB sebelumnya
			$pegawai[$key]->mutasi_sebelum = $p->mutasi()->whereHas('jenis', function(Builder $query) {
				return $query->whereIn('nama',['Peralihan BLU ke PTNBH','Mutasi Pangkat','KGB','PMK']);
			})->where('tmt','<',$tanggal)->first();

            // Get SPKGB
			$pegawai[$key]->mutasi_spkgb = $p->mutasi()->has('spkgb')->whereHas('jenis', function(Builder $query) {
				return $query->where('nama','=','KGB');
			})->where('tmt','=',$tanggal)->first();

            // Get gaji pokok lama
            $pegawai[$key]->gaji_pokok_lama = $p->mutasi()->where('tmt','<',$tanggal)->first() ? $p->mutasi()->where('tmt','<',$tanggal)->first()->gaji_pokok : $p->mutasi()->first()->gaji_pokok;

            // Set masa kerja baru
            $mk_baru = $tahun - date('Y', strtotime($p->tmt_golongan));
    
            // Set gaji pokok baru
            $sk_gaji_pns = SK::where('jenis_id','=',5)->where('status','=',1)->first();
            $pegawai[$key]->gaji_pokok_baru = $pegawai[$key]->gaji_pokok_lama ? GajiPokok::where('sk_id','=',$sk_gaji_pns->id)->where('nama','=',substr($pegawai[$key]->gaji_pokok_lama->nama,0,2).($mk_baru < 10 ? '0'.$mk_baru : $mk_baru))->first() : null;
		}

        return $pegawai;
    }
}
