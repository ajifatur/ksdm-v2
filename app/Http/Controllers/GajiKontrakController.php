<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Ajifatur\Helpers\DateTimeExt;
use Ajifatur\Helpers\FileExt;
use App\Imports\ByStartRowImport;
use App\Models\GajiKontrak;
use App\Models\JenisGajiKontrak;
use App\Models\Pegawai;
use App\Models\SKKontrak;
use App\Models\Unit;

class GajiKontrakController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get jenis gaji
        $jenis_gaji = JenisGajiKontrak::all();

        // Get bulan, tahun, jenis
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $jenis = in_array($request->query('jenis'), $jenis_gaji->pluck('id')->toArray()) ? $request->query('jenis') : null;

        // Get gaji
        $gaji_kontrak = [];
        if($jenis != null) {
            $gaji_kontrak = GajiKontrak::where('jenis_id','=',$jenis)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();
        }

        // View
        return view('admin/gaji-kontrak/index', [
            'jenis_gaji' => $jenis_gaji,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'jenis' => $jenis,
            'gaji_kontrak' => $gaji_kontrak,
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
        echo "Sedang Maintenance";
        return;
        
        // Get jenis dan status
        $jenis = JenisGaji::find($request->query('jenis'));
        $status = $request->query('status');

        $tahun_bulan_grup = [];
        if($jenis->grup == 1) {
            // Get tahun grup
            $tahun_grup = Gaji::whereHas('anak_satker', function(Builder $query) use($status) {
                return $query->where('jenis','=',$status);
            })->where('jenis_id','=',$jenis->id)->orderBy('tahun','desc')->groupBy('tahun')->pluck('tahun')->toArray();

            // Get bulan grup
            foreach($tahun_grup as $t) {
                $bulan_grup = Gaji::whereHas('anak_satker', function(Builder $query) use($status) {
                    return $query->where('jenis','=',$status);
                })->where('jenis_id','=',$jenis->id)->where('tahun','=',$t)->orderBy('bulan','desc')->groupBy('bulan')->pluck('bulan')->toArray();
                array_push($tahun_bulan_grup, [
                    'tahun' => $t,
                    'bulan' => $bulan_grup
                ]);
            }

            // Get bulan dan tahun
            if(count($tahun_bulan_grup) > 0) {
                $bulan = $request->query('bulan') ?: (int)$tahun_bulan_grup[0]['bulan'][0];
                $tahun = $request->query('tahun') ?: $tahun_bulan_grup[0]['tahun'];
            }
            else {
                $bulan = $request->query('bulan') ?: date('n');
                $tahun = $request->query('tahun') ?: date('Y');
            }
        }
        elseif($jenis->grup == 0) {
            // Get bulan dan tahun
            $bulan = $request->query('bulan') ?: date('n');
            $tahun = $request->query('tahun') ?: date('Y');
        }

        // Get jenis gaji
        $jenis_gaji = JenisGaji::all();

        // Get anak satker
        $anak_satker = AnakSatker::where('jenis','=',$status)->get();

        $data = [];
        $total = [
            'dosen_jumlah' => 0,
            'dosen_nominal' => 0,
            'dosen_potongan' => 0,
            'tendik_jumlah' => 0,
            'tendik_nominal' => 0,
            'tendik_potongan' => 0,
        ];
        foreach($anak_satker as $a) {
            // Get gaji
            if($jenis) {
                $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use($status, $a) {
                    return $query->where('jenis','=',$status)->where('id','=',$a->id);
                })->where('jenis_id','=',$jenis->id)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();
            }
            else {
                $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use($status, $a) {
                    return $query->where('jenis','=',$status)->where('id','=',$a->id);
                })->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();
            }


            // Set angka
            $dosen_jumlah = $gaji->where('jenis','=',1)->count();
            $dosen_nominal = $gaji->where('jenis','=',1)->sum('nominal');
            $dosen_potongan = $gaji->where('jenis','=',1)->sum('potongan');
            $tendik_jumlah = $gaji->where('jenis','=',2)->count();
            $tendik_nominal = $gaji->where('jenis','=',2)->sum('nominal');
            $tendik_potongan = $gaji->where('jenis','=',2)->sum('potongan');

            // Push data
            array_push($data, [
                'anak_satker' => $a,
                'dosen_jumlah' => $dosen_jumlah,
                'dosen_nominal' => $dosen_nominal,
                'dosen_potongan' => $dosen_potongan,
                'tendik_jumlah' => $tendik_jumlah,
                'tendik_nominal' => $tendik_nominal,
                'tendik_potongan' => $tendik_potongan,
            ]);

            // Total
            $total['dosen_jumlah'] += $dosen_jumlah;
            $total['dosen_nominal'] += $dosen_nominal;
            $total['dosen_potongan'] += $dosen_potongan;
            $total['tendik_jumlah'] += $tendik_jumlah;
            $total['tendik_nominal'] += $tendik_nominal;
            $total['tendik_potongan'] += $tendik_potongan;
        }

        // View
        return view('admin/gaji/monitoring', [
            'anak_satker' => $anak_satker,
            'jenis' => $jenis,
            'status' => $status,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tahun_bulan_grup' => $tahun_bulan_grup,
            'jenis_gaji' => $jenis_gaji,
            'data' => $data,
            'total' => $total,
        ]);
    }
    
    /**
     * Import
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");

        $bulan = 1;
        $tahun = 2024;

        // Get data
        $error = [];
        $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/Gaji_Kontrak_2024_01.xlsx'));
        if(count($array)>0) {
            foreach($array[0] as $key=>$data) {
                if($data[2] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[2])->orWhere('npu','=',$data[2])->first();
                    $pegawai->norek_btn = $data[17];
                    $pegawai->save();

                    // Get unit
                    $unit = Unit::where('nama','=',$data[4])->first();

                    // Get / update SK
                    $sk = SKKontrak::where('pegawai_id','=',$pegawai->id)->where('no_sk','=',$data[5])->first();
                    if(!$sk) $sk = new SKKontrak;
                    $sk->pegawai_id = $pegawai->id;
                    $sk->no_sk = $data[5];
                    $sk->tanggal = null;
                    $sk->gjpokok = $data[6];
                    $sk->save();

                    // Get / update gaji kontrak
                    $gaji_kontrak = GajiKontrak::where('pegawai_id','=',$pegawai->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                    if(!$gaji_kontrak) $gaji_kontrak = new GajiKontrak;
                    $gaji_kontrak->pegawai_id = $pegawai->id;
                    $gaji_kontrak->sk_id = $sk->id;
                    $gaji_kontrak->jenis_id = $data[18];
                    $gaji_kontrak->unit_id = $unit ? $unit->id : 0;
                    $gaji_kontrak->status_kawin = $pegawai->status_kawin;
                    $gaji_kontrak->status_pajak = $pegawai->status_pajak;
                    $gaji_kontrak->gjpokok = $data[6];
                    $gaji_kontrak->tjdosen = $data[7];
                    $gaji_kontrak->tjlain = $data[8];
                    $gaji_kontrak->tjbpjskes4 = $data[9];
                    $gaji_kontrak->tjbpjsket = $data[10];
                    $gaji_kontrak->kotor = $data[11];
                    $gaji_kontrak->iuranbpjskes1 = $data[12];
                    $gaji_kontrak->iuranbpjsket3 = $data[13];
                    $gaji_kontrak->jmlbpjskes = $data[14];
                    $gaji_kontrak->jmlbpjsket = $data[15];
                    $gaji_kontrak->bersih = $data[16];
                    $gaji_kontrak->bulan = $bulan;
                    $gaji_kontrak->tahun = $tahun;
                    $gaji_kontrak->nomor_rekening = $data[17];
                    $gaji_kontrak->save();
                }
            }
        }
        var_dump($error);
    }
}
