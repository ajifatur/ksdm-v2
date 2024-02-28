<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\TunjanganProfesiCSVExport;
use App\Exports\TunjanganProfesiExcelExport;
use App\Imports\ByStartRowImport;
use App\Models\TunjanganProfesi;
use App\Models\JenisTunjanganProfesi;
use App\Models\Angkatan;
use App\Models\Pegawai;
use App\Models\Golongan;
use App\Models\Gaji;
use App\Models\GajiPokok;
use App\Models\Mutasi;
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
            $tunjangan = TunjanganProfesi::where('angkatan_id','=',$request->query('angkatan'))->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kekurangan','=',0)->get();

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
                $tunjangan = TunjanganProfesi::where('angkatan_id','=',$a->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kekurangan','=',0)->get();

                // Get pegawai non aktif
                $pegawai_non_aktif = TunjanganProfesi::where('angkatan_id','=',$a->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->whereHas('pegawai', function (Builder $query) use ($tanggal) {
                    return $query->whereDoesntHave('status_kerja', function (Builder $query) {
                        return $query->where('status','=',1);
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
                if($jenis || (!$jenis && $a->jenis->id != 1)) $total['pegawai'] += $tunjangan->count();
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
            })->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kekurangan','=',0)->get();

            // Get pegawai non aktif
            $pegawai_non_aktif = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
                return $query->where('jenis_id','=',4);
            })->where('bulan','=',$bulan)->where('tahun','=',$tahun)->whereHas('pegawai', function (Builder $query) use ($tanggal) {
                return $query->whereDoesntHave('status_kerja', function (Builder $query) {
                    return $query->where('status','=',1);
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
            // Get SK
            $sk_1 = 19;
            $sk_2 = 20;

            // Set tanggal proses
            $tanggal = $request->tahun.'-'.($request->bulan < 10 ? '0'.$request->bulan : $request->bulan).'-'.$request->tanggal; // Maks tanggal 14
            $tanggal_1 = $request->tahun.'-'.($request->bulan < 10 ? '0'.$request->bulan : $request->bulan).'-01';

            // Set tanggal periode sebelumnya
            $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));
            
            // Get mutasi serdos nonaktif
            $mutasi_nonaktif = collect();
            $mutasi_nonaktif_1 = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('serdos','=',1)->where('status','=',0);
            })->where('tmt','<=',$tanggal)->whereNull('proses_serdos')->whereNull('proses')->get();
            $mutasi_nonaktif_2 = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('serdos','=',1)->where('status','=',0);
            })->where('tmt','<=',$tanggal)->where('proses_serdos','=',$tanggal_1)->get();
            $mutasi_nonaktif = $mutasi_nonaktif->merge($mutasi_nonaktif_1)->merge($mutasi_nonaktif_2);

            foreach($mutasi_nonaktif as $m) {
                // Update proses serdos
                $update = Mutasi::find($m->id);
                $update->proses_serdos = $tanggal_1;
                $update->proses = $tanggal_1;
                $update->save();
            }
            
            // Get tunjangan profesi bulan sebelumnya
            $tunjangan_sebelum = TunjanganProfesi::whereNotIn('pegawai_id',$mutasi_nonaktif->pluck('pegawai_id')->toArray())->where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->where('kekurangan','=',0)->get();

            foreach($tunjangan_sebelum as $t) {
                // Get mutasi
                $mutasi = $t->pegawai->mutasi()->whereHas('pegawai', function (Builder $query) {
                    return $query->whereHas('status_kerja', function (Builder $query) {
                        return $query->where('status','=',1);
                    });
                })->where('tmt','<=',$tanggal_1)->first();

                if($mutasi) {
                    // Get gaji pokok 2024
                    $gaji_pokok = GajiPokok::where('sk_id','=',14)->where('nama','=',$mutasi->gaji_pokok->nama)->first();

                    // Simpan tunjangan baru
                    $tunjangan = TunjanganProfesi::where('pegawai_id','=',$t->pegawai_id)->where('angkatan_id','=',$t->angkatan_id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('kekurangan','=',0)->first();
                    if(!$tunjangan) $tunjangan = new TunjanganProfesi;
                    $tunjangan->pegawai_id = $t->pegawai_id;
                    $tunjangan->sk_id = $t->sk_id;
                    $tunjangan->angkatan_id = $t->angkatan_id;
                    $tunjangan->unit_id = $t->pegawai->unit_id;
                    $tunjangan->golongan_id = $mutasi->golru->golongan_id;
                    $tunjangan->gaji_pokok_id = $gaji_pokok->id;
                    $tunjangan->nip = $t->nip;
                    $tunjangan->nama = $t->nama;
                    $tunjangan->nomor_rekening = $t->nomor_rekening;
                    $tunjangan->nama_rekening = $t->nama_rekening;
                    $tunjangan->tunjangan = $t->angkatan->jenis_id == 1 ? 2 * $gaji_pokok->gaji_pokok : $gaji_pokok->gaji_pokok;
                    $tunjangan->pph = $mutasi->golru->golongan_id == 4 ? (15/100) * $tunjangan->tunjangan : (5/100) * $tunjangan->tunjangan;
                    $tunjangan->diterimakan = $tunjangan->tunjangan - $tunjangan->pph;
                    $tunjangan->bulan = $request->bulan;
                    $tunjangan->tahun = $request->tahun;
                    $tunjangan->kekurangan = 0;
                    $tunjangan->bulan_kurang = 0;
                    $tunjangan->tahun_kurang = 0;
                    $tunjangan->save();
                }
            }

            // Get mutasi serdos aktif
            $mutasi_aktif = collect();
            $mutasi_aktif_1 = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('serdos','=',1)->where('status','=',1);
            })->where('tmt','<=',$tanggal)->whereNull('proses_serdos')->whereNull('proses')->get();
            $mutasi_aktif_2 = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('serdos','=',1)->where('status','=',1);
            })->where('tmt','<=',$tanggal)->where('proses_serdos','=',$tanggal_1)->get();
            $mutasi_aktif = $mutasi_aktif->merge($mutasi_aktif_1)->merge($mutasi_aktif_2);
            
            foreach($mutasi_aktif as $m) {
                // Get tunjangan profesi sebelum
                $tunjangan_sebelum = TunjanganProfesi::where('pegawai_id','=',$m->pegawai_id)->where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->where('kekurangan','=',0)->get();
                
                if(count($tunjangan_sebelum) > 0) {
                    foreach($tunjangan_sebelum as $t) {
                        // Get gaji pokok 2024
                        $gaji_pokok = GajiPokok::where('sk_id','=',14)->where('nama','=',$m->gaji_pokok->nama)->first();

                        // Simpan tunjangan baru
                        $tunjangan = TunjanganProfesi::where('pegawai_id','=',$t->pegawai_id)->where('angkatan_id','=',$t->angkatan_id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('kekurangan','=',0)->first();
                        if(!$tunjangan) $tunjangan = new TunjanganProfesi;
                        $tunjangan->pegawai_id = $t->pegawai_id;
                        $tunjangan->sk_id = $t->angkatan->jenis_id == 1 ? $sk_1 : $sk_2;
                        $tunjangan->angkatan_id = $t->angkatan_id;
                        $tunjangan->unit_id = $t->pegawai->unit_id;
                        $tunjangan->golongan_id = $m->golru->golongan_id;
                        $tunjangan->gaji_pokok_id = $gaji_pokok->id;
                        $tunjangan->nip = $t->nip;
                        $tunjangan->nama = $t->nama;
                        $tunjangan->nomor_rekening = $t->nomor_rekening;
                        $tunjangan->nama_rekening = $t->nama_rekening;
                        $tunjangan->tunjangan = $t->angkatan->jenis_id == 1 ? 2 * $gaji_pokok->gaji_pokok : $gaji_pokok->gaji_pokok;
                        $tunjangan->pph = $m->golru->golongan_id == 4 ? (15/100) * $tunjangan->tunjangan : (5/100) * $tunjangan->tunjangan;
                        $tunjangan->diterimakan = $tunjangan->tunjangan - $tunjangan->pph;
                        $tunjangan->bulan = $request->bulan;
                        $tunjangan->tahun = $request->tahun;
                        $tunjangan->kekurangan = 0;
                        $tunjangan->bulan_kurang = 0;
                        $tunjangan->tahun_kurang = 0;
                        $tunjangan->save();
                    }
                }
                else {
                    if(in_array($m->jenis->nama, ['Serdos Baru','Pengaktifan Kembali Serdos','Inpassing Serdos'])) {
                        // Get gaji pokok 2024
                        $gaji_pokok = GajiPokok::where('sk_id','=',14)->where('nama','=',$m->gaji_pokok->nama)->first();

                        // Simpan tunjangan baru
                        $tunjangan = TunjanganProfesi::where('pegawai_id','=',$m->pegawai_id)->where('angkatan_id','=',$m->pegawai->angkatan_id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('kekurangan','=',0)->first();
                        if(!$tunjangan) $tunjangan = new TunjanganProfesi;
                        $tunjangan->pegawai_id = $m->pegawai_id;
                        $tunjangan->sk_id = $m->pegawai->angkatan->jenis_id == 1 ? $sk_1 : $sk_2;
                        $tunjangan->angkatan_id = $m->pegawai->angkatan_id;
                        $tunjangan->unit_id = $m->pegawai->unit_id;
                        $tunjangan->golongan_id = $m->golru->golongan_id;
                        $tunjangan->gaji_pokok_id = $gaji_pokok->id;
                        $tunjangan->nip = $m->pegawai->nip;
                        $tunjangan->nama = $m->pegawai->nama_supplier;
                        $tunjangan->nomor_rekening = $m->pegawai->norek_btn;
                        $tunjangan->nama_rekening = $m->pegawai->nama_btn;
                        $tunjangan->tunjangan = $t->angkatan->jenis_id == 1 ? 2 * $gaji_pokok->gaji_pokok : $gaji_pokok->gaji_pokok;
                        $tunjangan->pph = $m->golru->golongan_id == 4 ? (15/100) * $tunjangan->tunjangan : (5/100) * $tunjangan->tunjangan;
                        $tunjangan->diterimakan = $tunjangan->tunjangan - $tunjangan->pph;
                        $tunjangan->bulan = $request->bulan;
                        $tunjangan->tahun = $request->tahun;
                        $tunjangan->kekurangan = 0;
                        $tunjangan->bulan_kurang = 0;
                        $tunjangan->tahun_kurang = 0;
                        $tunjangan->save();
                    }
                }

                // Update proses serdos
                $update = Mutasi::find($m->id);
                $update->proses_serdos = $tanggal_1;
                $update->proses = $tanggal_1;
                $update->save();
            }
            return;

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

        // Get kekurangan
        // 0: Semua, 1: Ya, 2: Tidak
        $kekurangan = $request->query('kekurangan') ?: 0;

        // Set tunjangan, kekurangan tunjangan
        $tunjangan = [];
        $kekurangan_tunjangan = [];

        if($jenis) {
            if($kekurangan == 0 || $kekurangan == 2) {
                // Get tunjangan profesi
                $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($jenis) {
                    return $query->where('jenis_id','=',$jenis->id);
                })->where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->where('kekurangan','=',0)->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();
            }

            if($kekurangan == 0 || $kekurangan == 1) {
                // Get kekurangan tunjangan profesi
                $kekurangan_tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($jenis) {
                    return $query->where('jenis_id','=',$jenis->id);
                })->where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->where('kekurangan','=',1)->groupBy('pegawai_id')->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();
                foreach($kekurangan_tunjangan as $key=>$k) {
                    $kekurangan_tunjangan[$key]->detail = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($jenis) {
                        return $query->where('jenis_id','=',$jenis->id);
                    })->where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->where('kekurangan','=',1)->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();
                }
            }

            // Download
            return Excel::download(new TunjanganProfesiExcelExport([
                'tunjangan' => $tunjangan,
                'kekurangan' => $kekurangan_tunjangan,
            ]), 'Tunjangan Profesi ('.$jenis->nama.') - '.$request->tahun.' '.DateTimeExt::month($request->bulan).'.xlsx');
        }
        else {
            if($kekurangan == 0 || $kekurangan == 2) {
                // Get tunjangan profesi
                $tunjangan = TunjanganProfesi::where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->where('kekurangan','=',0)->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();
            }

            if($kekurangan == 0 || $kekurangan == 1) {
                // Get kekurangan tunjangan profesi
                $kekurangan_tunjangan = TunjanganProfesi::where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->where('kekurangan','=',1)->groupBy('pegawai_id')->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();
                foreach($kekurangan_tunjangan as $key=>$k) {
                    $kekurangan_tunjangan[$key]->detail = TunjanganProfesi::where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->where('kekurangan','=',1)->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();
                }
            }

            // Download
            return Excel::download(new TunjanganProfesiExcelExport([
                'tunjangan' => $tunjangan,
                'kekurangan' => $kekurangan_tunjangan,
            ]), 'Tunjangan Profesi - '.$request->tahun.' '.DateTimeExt::month($request->bulan).'.xlsx');
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

        $bulan = 2;
        $tahun = 2024;
        $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/Serdos Februari 2024.xlsx'));

        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[1])->orWhere('npu','=',$data[1])->first();
                    if(!$pegawai) array_push($error, $data[2]);

                    // Get jenis
                    $jenis = $data[12];

                    // Get SK
                    if($jenis == 1)
                        $sk = SK::where('jenis_id','=',2)->where('status','=',1)->first();
                    elseif($jenis == 2 || $jenis == 3)
                        $sk = SK::where('jenis_id','=',3)->where('status','=',1)->first();
                    if($jenis == 4)
                        $sk = SK::where('jenis_id','=',4)->where('status','=',1)->first();

                    // Get angkatan
                    if($jenis == 1 || $jenis == 2) {
                        if(in_array($data[10], [2014,2015]))
                            $angkatan = Angkatan::where('jenis_id','=',$jenis)->where('nama','=','2014-2015')->first();
                        else
                            $angkatan = Angkatan::where('jenis_id','=',$jenis)->where('nama','=',$data[10])->first();
                    }
                    elseif($jenis == 3 || $jenis == 4) {
                        if(in_array($data[10], [2020,2021,2022,2023]))
                            $angkatan = Angkatan::where('jenis_id','=',$jenis)->where('nama','=','2020-2023')->first();
                        else
                            $angkatan = Angkatan::where('jenis_id','=',$jenis)->where('nama','=',$data[10])->first();
                    }

                    // Get tunjangan profesi yang sudah ada
                    $tp = $pegawai->tunjangan_profesi()->whereHas('angkatan', function(Builder $query) use ($jenis) {
                        return $query->where('jenis_id','=',$jenis);
                    })->first();

                    // Simpan tunjangan profesi
                    if($data[9] > 0) {
                        $tunjangan = TunjanganProfesi::whereHas('angkatan', function(Builder $query) use ($jenis) {
                            return $query->where('jenis_id','=',$jenis);
                        })->where('pegawai_id','=',$pegawai->id)->where('sk_id','=',$sk->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                        if(!$tunjangan) $tunjangan = new TunjanganProfesi;
                        $tunjangan->pegawai_id = $pegawai->id;
                        $tunjangan->sk_id = $sk->id;
                        $tunjangan->angkatan_id = $angkatan->id;
                        $tunjangan->unit_id = $pegawai->unit_id;
                        $tunjangan->golongan_id = $pegawai->golongan_id;
                        $tunjangan->nip = $pegawai->nip;
                        $tunjangan->nama = $tp ? $tp->nama : '';
                        $tunjangan->nomor_rekening = $tp ? $tp->nomor_rekening : '';
                        $tunjangan->nama_rekening = $tp ? $tp->nama_rekening : '';
                        $tunjangan->bulan = $bulan;
                        $tunjangan->tahun = $tahun;
                        $tunjangan->tunjangan = $data[9];
                        $tunjangan->pph = ($tunjangan->golongan_id == 4) ? (15 / 100) * $tunjangan->tunjangan : (5 / 100) * $tunjangan->tunjangan;
                        $tunjangan->diterimakan = $tunjangan->tunjangan - $tunjangan->pph;
                        $tunjangan->kekurangan = 0;
                        $tunjangan->bulan_kurang = 0;
                        $tunjangan->tahun_kurang = 0;
                        $tunjangan->save();
                    }

                    /*
                    $tp = $pegawai->tunjangan_profesi()->where('tahun','!=',2024)->first();
                    $tunjangan = TunjanganProfesi::where('pegawai_id','=',$pegawai->id)->where('sk_id','=',$sk->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                    if($tunjangan->nama == '') {
                        $tunjangan->nama = $tp ? $tp->nama : '';
                        $tunjangan->nomor_rekening = $tp ? $tp->nomor_rekening : '';
                        $tunjangan->nama_rekening = $tp ? $tp->nama_rekening : '';
                        $tunjangan->save();

                        $pegawai->nama_supplier = $tunjangan->nama;
                        $pegawai->nama_btn = $tunjangan->nama_rekening;
                        $pegawai->norek_btn = $tunjangan->nomor_rekening;
                        $pegawai->save();
                    }
                    */
                }
            }
        }
        var_dump($error);
        return;
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
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");

        // Get bulan, tahun, tanggal
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Get tunjangan bulan ini
        $tunjangan_bulan_ini = TunjanganProfesi::where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kekurangan','=',0)->get();

        // Set tanggal sebelumnya
        $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

        // Get tunjangan bulan sebelumnya
        $tunjangan_bulan_sebelumnya = TunjanganProfesi::where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->where('kekurangan','=',0)->pluck('pegawai_id')->toArray();

        // Pegawai masuk
        $cek_bulan_ini = [];
        if(count($tunjangan_bulan_ini) > 0) {
            foreach($tunjangan_bulan_ini->pluck('pegawai_id')->toArray() as $t) {
                if(!in_array($t, $tunjangan_bulan_sebelumnya))
                    array_push($cek_bulan_ini, $t);
            }
        }
		$pegawai_on = Pegawai::whereIn('id', $cek_bulan_ini)->get();
        foreach($pegawai_on as $key=>$p) {
            $pegawai_on[$key]->mutasi_serdos = $p->mutasi()->whereHas('jenis', function(Builder $query) {
                return $query->where('serdos','=',1);
            })->where('proses_serdos','=',$tanggal)->first();
        }

        // Pegawai keluar
        $cek_bulan_sebelumnya = [];
        if(count($tunjangan_bulan_sebelumnya) > 0) {
            foreach($tunjangan_bulan_sebelumnya as $t) {
                if(!in_array($t, $tunjangan_bulan_ini->pluck('pegawai_id')->toArray()))
                    array_push($cek_bulan_sebelumnya, $t);
            }
        }
		$pegawai_off = Pegawai::whereIn('id', $cek_bulan_sebelumnya)->get();
        foreach($pegawai_off as $key=>$p) {
            $pegawai_off[$key]->mutasi_serdos = $p->mutasi()->whereHas('jenis', function(Builder $query) {
                return $query->where('serdos','=',1);
            })->where('proses_serdos','=',$tanggal)->first();
        }

        // Perubahan tunjangan
        $perubahan_tunjangan = [];
        foreach($tunjangan_bulan_ini as $t) {
            // Get tunjangan bulan sebelumnya
            $ts = TunjanganProfesi::where('pegawai_id','=',$t->pegawai_id)->where('angkatan_id','=',$t->angkatan_id)->where('bulan','=',date('n', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->where('kekurangan','=',0)->first();
            if($ts) {
                if($t->tunjangan != $ts->tunjangan) {
                    // Get mutasi serdos
                    $mutasi_serdos = $t->pegawai->mutasi()->whereHas('jenis', function(Builder $query) {
                        return $query->where('serdos','=',1);
                    })->where('proses_serdos','=',$tanggal)->first();

                    array_push($perubahan_tunjangan, ['pegawai' => $t->pegawai, 'mutasi_serdos' => $mutasi_serdos, 'sebelum' => $ts->tunjangan, 'sesudah' => $t->tunjangan]);
                }
            }
        }
		
        // View
        return view('admin/tunjangan-profesi/change', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
            'tunjangan_bulan_ini' => $tunjangan_bulan_ini,
            'tunjangan_bulan_sebelumnya' => $tunjangan_bulan_sebelumnya,
            'pegawai_on' => $pegawai_on,
            'pegawai_off' => $pegawai_off,
            'perubahan_tunjangan' => $perubahan_tunjangan,
        ]);
    }
}