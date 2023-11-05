<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
use App\Models\PengaktifanSerdos;
use App\Models\Mutasi;
use App\Models\SK;
use App\Models\Proses;

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Get pegawai sudah mendapat tunjangan profesi
        $pegawai_aktif = TunjanganProfesi::pluck('pegawai_id')->toArray();

        // Get pegawai
        $pegawai = Pegawai::where('jenis','=',1)->whereHas('status_kerja', function (Builder $query) {
            return $query->where('status','=',1);
        })->whereIn('status_kepeg_id',[1,2])->whereNotIn('id',$pegawai_aktif)->get();

        // Get angkatan
        $angkatan = [];
        for($i=1; $i<=3; $i++) {
            $angkatan[$i]['data'] = Angkatan::where('jenis','=',$i)->orderBy('nama','asc')->get();
        }

        // View
        return view('admin/tunjangan-profesi/create', [
            'pegawai' => $pegawai,
            'angkatan' => $angkatan
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
        // Validation
        $validator = Validator::make($request->all(), [
            'pegawai' => 'required',
            'angkatan' => 'required',
            'nama' => 'required',
            'nomor_rekening' => 'required',
            'nama_rekening' => 'required',
            'gaji_pokok' => 'required',
            'tmt' => 'required',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Simpan pengaktifan serdos
            $pengaktifan_serdos = new PengaktifanSerdos;
            $pengaktifan_serdos->pegawai_id = $request->pegawai;
            $pengaktifan_serdos->angkatan_id = $request->angkatan;
            $pengaktifan_serdos->gaji_pokok_id = $request->gaji_pokok;
            $pengaktifan_serdos->nama = $request->nama;
            $pengaktifan_serdos->nomor_rekening = $request->nomor_rekening;
            $pengaktifan_serdos->nama_rekening = $request->nama_rekening;
            $pengaktifan_serdos->tmt = DateTimeExt::change($request->tmt);
            $pengaktifan_serdos->bulan_proses = 0;
            $pengaktifan_serdos->tahun_proses = 0;
            $pengaktifan_serdos->save();

            // Redirect
            return redirect()->route('admin.tunjangan-profesi.create')->with(['message' => 'Berhasil menambah data.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::findOrFail($request->id);
        $tunjangan->delete();

        // Redirect
        return redirect()->route('admin.pegawai.detail', ['id' => $tunjangan->pegawai_id, 'tunjangan_profesi' => 1])->with(['message' => 'Berhasil menghapus data.']);
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
            
            // Get tunjangan profesi bulan sebelumnya
            $tunjangan = TunjanganProfesi::where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->get();

            foreach($tunjangan as $t) {
                $pegawai = $t->pegawai_id;

                // Get mutasi
                $mutasi = Mutasi::whereHas('pegawai', function (Builder $query) use ($pegawai) {
                    return $query->whereHas('status_kerja', function (Builder $query2) {
                        return $query2->where('status','=',1);
                    })->where('id','=',$pegawai);
                })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();

                if($mutasi) {
                    if(!$mutasi->gaji_pokok) {
                        var_dump($t->pegawai->nama);
                        return;
                    }
                    // Simpan tunjangan baru
                    $new_tunjangan = TunjanganProfesi::where('pegawai_id','=',$t->pegawai_id)->where('angkatan_id','=',$t->angkatan_id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->first();
                    if(!$new_tunjangan) $new_tunjangan = new TunjanganProfesi;
                    $new_tunjangan->pegawai_id = $t->pegawai_id;
                    $new_tunjangan->angkatan_id = $t->angkatan_id;
                    // $new_tunjangan->unit_id = $t->unit_id;
                    $new_tunjangan->unit_id = $t->pegawai->unit_id;
                    $new_tunjangan->golongan_id = $mutasi->golru->golongan_id;
                    $new_tunjangan->nip = $t->nip;
                    $new_tunjangan->nama = $t->nama;
                    $new_tunjangan->nomor_rekening = $t->nomor_rekening;
                    $new_tunjangan->nama_rekening = $t->nama_rekening;
                    $new_tunjangan->bulan = $request->bulan;
                    $new_tunjangan->tahun = $request->tahun;
                    // $new_tunjangan->tunjangan = $t->tunjangan;
                    // $new_tunjangan->pph = $t->pph;
                    // $new_tunjangan->diterimakan = $t->diterimakan;
                    $new_tunjangan->tunjangan = $t->angkatan->jenis_id == 1 ? 2 * $mutasi->gaji_pokok->gaji_pokok : $mutasi->gaji_pokok->gaji_pokok;
                    $new_tunjangan->pph = $mutasi->golru->golongan_id == 4 ? (15/100) * $new_tunjangan->tunjangan : (5/100) * $new_tunjangan->tunjangan;
                    $new_tunjangan->diterimakan = $new_tunjangan->tunjangan - $new_tunjangan->pph;
                    $new_tunjangan->save();
                }
            }

            // Get pengaktifan serdos
            $pengaktifan_serdos = PengaktifanSerdos::where('tmt','<=',$tanggal)->where('bulan_proses','=',0)->where('tahun_proses','=',0)->get();
            
            foreach($pengaktifan_serdos as $p) {
                $pegawai = $p->pegawai_id;

                // Get mutasi
                $mutasi = Mutasi::whereHas('pegawai', function (Builder $query) use ($pegawai) {
                    return $query->whereHas('status_kerja', function (Builder $query2) {
                        return $query2->where('status','=',1);
                    })->where('id','=',$pegawai);
                })->orderBy('tahun','desc')->orderBy('bulan','desc')->first();

                if($mutasi) {
                    // Simpan tunjangan baru
                    $new_tunjangan = TunjanganProfesi::where('pegawai_id','=',$p->pegawai_id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->first();
                    if(!$new_tunjangan) $new_tunjangan = new TunjanganProfesi;
                    $new_tunjangan->pegawai_id = $p->pegawai_id;
                    $new_tunjangan->angkatan_id = $p->angkatan_id;
                    $new_tunjangan->unit_id = $p->unit_id;
                    $new_tunjangan->golongan_id = $mutasi->golru->golongan_id;
                    $new_tunjangan->nip = $mutasi->pegawai->nip;
                    $new_tunjangan->nama = $p->nama;
                    $new_tunjangan->nomor_rekening = $p->nomor_rekening;
                    $new_tunjangan->nama_rekening = $p->nama_rekening;
                    $new_tunjangan->bulan = $request->bulan;
                    $new_tunjangan->tahun = $request->tahun;
                    $new_tunjangan->tunjangan = $p->gaji_pokok->gaji_pokok; // Belum memuat kehormatan profesor
                    $new_tunjangan->pph = $mutasi->golru->golongan_id == 4 ? (15/100) * $p->gaji_pokok->gaji_pokok : (5/100) * $p->gaji_pokok->gaji_pokok; // Belum memuat kehormatan profesor
                    $new_tunjangan->diterimakan = $new_tunjangan->tunjangan - $new_tunjangan->pph;
                    $new_tunjangan->save();
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
     * Print PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  id
     * @return \Illuminate\Http\Response
     */
    public function print(Request $request, $id)
    {
        // Check the access
        // has_access(method(__METHOD__), Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get angkatan
        $angkatan = Angkatan::whereIn('jenis_id',[1,2,3])->findOrFail($id);

        // Get SK
        if($angkatan->jenis_id == 1)
            $sk = SK::where('jenis_id','=',2)->where('status','=',1)->first();
        elseif($angkatan->jenis_id == 2 || $angkatan->jenis_id == 3)
            $sk = SK::where('jenis_id','=',3)->where('status','=',1)->first();

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::where('angkatan_id','=',$angkatan->id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Set title
        $title = 'Tunjangan '.$angkatan->jenis->nama.' - '.$angkatan->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';

        // PDF
        $pdf = \PDF::loadView('admin/tunjangan-profesi/print', [
            'title' => $title,
            'nama' => 'Tunjangan '.$angkatan->jenis->deskripsi,
            'angkatan' => $angkatan,
            'sk' => $sk,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'tunjangan' => $tunjangan
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Print PDF Non PNS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  id
     * @return \Illuminate\Http\Response
     */
    public function printNonPNS(Request $request)
    {
        // Check the access
        // has_access(method(__METHOD__), Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get SK
        $sk = SK::where('jenis_id','=',4)->where('status','=',1)->first();

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','=',4);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Set title
        $title = 'Tunjangan '.$tunjangan[0]->angkatan->jenis->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';

        // PDF
        $pdf = \PDF::loadView('admin/tunjangan-profesi/print', [
            'title' => $title,
            'nama' => 'Tunjangan '.$tunjangan[0]->angkatan->jenis->deskripsi,
            'sk' => $sk,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'tunjangan' => $tunjangan
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Print SPTJM.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printSPTJM(Request $request)
    {
        // Check the access
        // has_access(method(__METHOD__), Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");
		
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');

		$angkatan = null; $jenis = null;
		if($request->query('angkatan') != null) {
			// Get angkatan
	        $angkatan = Angkatan::whereIn('jenis_id',[1,2,3])->find($request->query('angkatan'));
			
			// Set title
        	$title = 'SPTJM Tunjangan '.$angkatan->jenis->nama.' - '.$angkatan->nama.' ('.$tahun.' '.DateTimeExt::month($bulan).')';
		}
		elseif($request->query('jenis') != null) {
			// Get jenis tunjangan
			$jenis = JenisTunjanganProfesi::find($request->query('jenis'));
			
			// Set title
			$title = 'SPTJM Tunjangan '.$jenis->nama.' ('.$tahun.' '.DateTimeExt::month($bulan).')';
		}
		
        // PDF
        $pdf = \PDF::loadView('admin/tunjangan-profesi/print-sptjm', [
            'title' => $title,
            'angkatan' => $angkatan,
            'jenis' => $jenis,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
        $pdf->setPaper('A4');
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Export to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  id
     * @return \Illuminate\Http\Response
     */
    public function csv(Request $request, $id)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get angkatan
        $angkatan = Angkatan::findOrFail($id);

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::where('angkatan_id','=',$angkatan->id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Download
        return Excel::download(new TunjanganProfesiCSVExport($tunjangan), $angkatan->jenis->file.'_'.$angkatan->nama.'_('.$request->tahun.'_'.\Ajifatur\Helpers\DateTimeExt::month($request->bulan).').csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Export to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  id
     * @return \Illuminate\Http\Response
     */
    public function csvBatch(Request $request, $id)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get jenis
        $jenis = JenisTunjanganProfesi::findOrFail($id);

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function(Builder $query) use ($jenis) {
            return $query->where('jenis_id','=',$jenis->id);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Download
        return Excel::download(new TunjanganProfesiCSVExport($tunjangan), $jenis->file.'_('.$request->tahun.'_'.\Ajifatur\Helpers\DateTimeExt::month($request->bulan).').csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Export to CSV Non PNS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function csvNonPNS(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','=',4);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Download
        return Excel::download(new TunjanganProfesiCSVExport($tunjangan), 'Non-PNS_('.$request->tahun.'_'.\Ajifatur\Helpers\DateTimeExt::month($request->bulan).').csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Export to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function excel(Request $request)
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

        // Set jenis, bulan, tahun
        $jenis = 3;
        $bulan = 6;
        $tahun = 2023;

        // Set file
        if($jenis == 1)
		    $array = Excel::toArray(new TunjanganProfesiImport, public_path('assets/spreadsheets/Serdos Kehormatan Profesor.xlsx'));
        elseif($jenis == 2)
    		$array = Excel::toArray(new TunjanganProfesiImport, public_path('assets/spreadsheets/Serdos GB.xlsx'));
        elseif($jenis == 3)
    		$array = Excel::toArray(new TunjanganProfesiImport, public_path('assets/spreadsheets/Serdos Non GB.xlsx'));

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
                                $tunjangan->tunjangan = $data[6];
                                $tunjangan->pph = mround((15/100) * $data[6], 1);
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
     * Unit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function unit(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");
        
        // Get tunjangan
        $tunjangan = TunjanganProfesi::where('unit_id','=',0)->get();

        $errors = [];
        foreach($tunjangan as $t) {
            // Get gapok
            $gapok = \App\Models\Gapok::where('nip','=',$t->nip)->latest('tahun')->latest('bulan')->first();
            if($gapok) {
                // Get unit
                if($gapok->kdanak == "00") $anak = 6;
                // elseif($gapok->kdanak == "01") $anak = "PUSAT";
                elseif($gapok->kdanak == "02") $anak = 10;
                elseif($gapok->kdanak == "03") $anak = 9;
                elseif($gapok->kdanak == "04") $anak = 7;
                // elseif($gapok->kdanak == "05") $anak = "BANTUAN PANGAN";
                elseif($gapok->kdanak == "06") $anak = 11;
                elseif($gapok->kdanak == "07") $anak = 4;
                elseif($gapok->kdanak == "08") $anak = 4;
                elseif($gapok->kdanak == "09") $anak = 4;
                elseif($gapok->kdanak == "10") $anak = 1;
                elseif($gapok->kdanak == "11") $anak = 2;
                elseif($gapok->kdanak == "12") $anak = 6;
                else $anak = 0;

                // Update tunjangan
                $new_tunjangan = TunjanganProfesi::find($t->id);
                // $new_tunjangan->unit_id = $anak;
                $new_tunjangan->unit_id = 10;
                $new_tunjangan->save();
            }
            else
                array_push($errors, $t->nama);
        }
        var_dump($errors);
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