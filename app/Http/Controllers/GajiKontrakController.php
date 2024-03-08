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
use App\Models\JenisGaji;
use App\Models\KategoriKontrak;
use App\Models\Pegawai;
use App\Models\SKKontrak;
use App\Models\StatusKawin;
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
        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get kategori kontrak
        $kategori_kontrak = KategoriKontrak::orderBy('num_order','asc')->get();

        // Get bulan, tahun, kategori
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $kategori = in_array($request->query('kategori'), $kategori_kontrak->pluck('id')->toArray()) ? KategoriKontrak::find($request->query('kategori')) : null;

        // Get gaji
        $gaji = [];
        if($kategori != null) {
            $gaji = GajiKontrak::where('jenis_id','=',$jenis->id)->where('kategori_id','=',$kategori->id)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->orderBy(
                Unit::select('num_order')->whereColumn((new GajiKontrak)->getTable().'.unit_id', (new Unit)->getTable().'.id')
            )->orderBy(
                Pegawai::select('nama')->whereColumn((new GajiKontrak)->getTable().'.pegawai_id', (new Pegawai)->getTable().'.id')
            )->where('tahun','=',$tahun)->get();
        }

        // View
        return view('admin/gaji-kontrak/index', [
            'jenis' => $jenis,
            'kategori_kontrak' => $kategori_kontrak,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $kategori,
            'gaji' => $gaji,
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
        // Get jenis
        $jenis = JenisGaji::find($request->query('jenis'));

        $tahun_bulan_grup = [];
        if($jenis->grup == 1) {
            // Get tahun grup
            $tahun_grup = GajiKontrak::where('jenis_id','=',$jenis->id)->orderBy('tahun','desc')->groupBy('tahun')->pluck('tahun')->toArray();

            // Get bulan grup
            foreach($tahun_grup as $t) {
                $bulan_grup = GajiKontrak::where('jenis_id','=',$jenis->id)->where('tahun','=',$t)->orderBy('bulan','desc')->groupBy('bulan')->pluck('bulan')->toArray();
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

        // Get kategori kontrak
        $kategori_kontrak = KategoriKontrak::orderBy('num_order','asc')->get();

        $data = [];
        $total = [
            'pegawai' => 0,
            'kotor' => 0,
            'bersih' => 0,
        ];
        foreach($kategori_kontrak as $k) {
            // Get gaji
            if($jenis) {
                $gaji = GajiKontrak::where('jenis_id','=',$jenis->id)->where('kategori_id','=',$k->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->get();
            }
            else {
                $gaji = GajiKontrak::where('kategori_id','=',$k->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->get();
            }

            // Set angka
            $pegawai = $gaji->count();
            $kotor = $gaji->sum('kotor');
            $bersih = $gaji->sum('bersih');

            // Push data
            array_push($data, [
                'kategori' => $k,
                'pegawai' => $pegawai,
                'kotor' => $kotor,
                'bersih' => $bersih,
            ]);

            // Total
            $total['pegawai'] += $pegawai;
            $total['kotor'] += $kotor;
            $total['bersih'] += $bersih;
        }

        // View
        return view('admin/gaji-kontrak/monitoring', [
            'kategori_kontrak' => $kategori_kontrak,
            'jenis' => $jenis,
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

        // $gaji = GajiKontrak::all();
        // foreach($gaji as $g) {
        //     $update = GajiKontrak::find($g->id);
        //     $update->status_kawin_id = StatusKawin::where('kode','=',$g->status_kawin)->first()->id;
        //     $update->status_pajak_id = StatusKawin::where('kode','=',$g->status_pajak)->first()->id;
        //     $update->nik = $g->pegawai->nik;
        //     $update->npwp = $g->pegawai->npwp;
        //     $update->save();
        // }
        // return;

        $bulan = 1;
        $tahun = 2024;

        // Get data
        $error = [];
        // $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/Gaji_Kontrak_2024_02.xlsx'));
        $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/Gaji_Kontrak_Susulan_2024_01.xlsx'));
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
                    $gaji_kontrak->jenis_id = 2; // 1
                    $gaji_kontrak->kategori_id = $data[18];
                    $gaji_kontrak->unit_id = $pegawai->unit_id;
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

    /**
     * Print.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function print(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get kategori kontrak
        $kategori_kontrak = KategoriKontrak::orderBy('num_order','asc')->get();

        // Get bulan, tahun, kategori
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $kategori = in_array($request->query('kategori'), $kategori_kontrak->pluck('id')->toArray()) ? KategoriKontrak::find($request->query('kategori')) : null;

        // Get gaji
        $gaji = [];
        if($kategori != null) {
            $gaji = GajiKontrak::where('jenis_id','=',$jenis->id)->where('kategori_id','=',$kategori->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->orderBy(
                Unit::select('num_order')->whereColumn((new GajiKontrak)->getTable().'.unit_id', (new Unit)->getTable().'.id')
            )->orderBy(
                Pegawai::select('nama')->whereColumn((new GajiKontrak)->getTable().'.pegawai_id', (new Pegawai)->getTable().'.id')
            )->get();
        }

        // Set title
        $title = $jenis->nama.' '.($kategori ? $kategori->nama : '').' - ('.$tahun.' '.DateTimeExt::month($bulan).')';

        // PDF
        $pdf = PDF::loadView('admin/gaji-kontrak/print', [
            'title' => $title,
            'jenis' => $jenis,
            'kategori_kontrak' => $kategori_kontrak,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $kategori,
            'gaji' => $gaji,
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }
}
