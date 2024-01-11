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
use Ajifatur\Helpers\FileExt;
use App\Exports\UangMakanExport;
use App\Imports\UangMakanImport;
use App\Models\UangMakan;
use App\Models\AnakSatker;
use App\Models\Pegawai;
use App\Models\PegawaiNonAktif;

class UangMakanController extends Controller
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
        $id = $request->query('id') ?: 0;
        $jenis = $request->query('jenis') ?: 1;

        // Get anak satker
        $as = AnakSatker::find($id);

        // Get anak satker
        $anak_satker = AnakSatker::where('jenis','=',$jenis)->where('nama','!=','Bantuan Pangan')->get();

        // Get uang makan
        $uang_makan = [];
        if($id != 0)
            $uang_makan = UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
                return $query->where('jenis','=',$jenis);
            })->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->where('kdanak','=',$as->kode)->get();

        // View
        return view('admin/uang-makan/index', [
            'anak_satker' => $anak_satker,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'jenis' => $jenis,
            'uang_makan' => $uang_makan,
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
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $jenis = $request->query('jenis') ?: 1;

        // Get anak satker
        $anak_satker = AnakSatker::where('jenis','=',$jenis)->where('nama','!=','Bantuan Pangan')->get();

        $data = [];
        $total = [
            'dosen_jumlah' => 0,
            'dosen_kotor' => 0,
            'dosen_bersih' => 0,
            'tendik_jumlah' => 0,
            'tendik_kotor' => 0,
            'tendik_bersih' => 0,
        ];
        foreach($anak_satker as $a) {
            $uang_makan = UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
                return $query->where('jenis','=',$jenis);
            })->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->where('kdanak','=',$a->kode)->get();

            // Set angka
            $dosen_jumlah = $uang_makan->where('jenis','=',1)->count();
            $dosen_kotor = $uang_makan->where('jenis','=',1)->sum('kotor');
            $dosen_bersih = $uang_makan->where('jenis','=',1)->sum('bersih');
            $tendik_jumlah = $uang_makan->where('jenis','=',2)->count();
            $tendik_kotor = $uang_makan->where('jenis','=',2)->sum('kotor');
            $tendik_bersih = $uang_makan->where('jenis','=',2)->sum('bersih');

            // Push data
            array_push($data, [
                'anak_satker' => $a,
                'dosen_jumlah' => $dosen_jumlah,
                'dosen_kotor' => $dosen_kotor,
                'dosen_bersih' => $dosen_bersih,
                'tendik_jumlah' => $tendik_jumlah,
                'tendik_kotor' => $tendik_kotor,
                'tendik_bersih' => $tendik_bersih,
            ]);

            // Count total
            $total['dosen_jumlah'] += $dosen_jumlah;
            $total['dosen_kotor'] += $dosen_kotor;
            $total['dosen_bersih'] += $dosen_bersih;
            $total['tendik_jumlah'] += $tendik_jumlah;
            $total['tendik_kotor'] += $tendik_kotor;
            $total['tendik_bersih'] += $tendik_bersih;
        }

        // View
        return view('admin/uang-makan/monitoring', [
            'anak_satker' => $anak_satker,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'jenis' => $jenis,
            'data' => $data,
            'total' => $total,
        ]);
    }

    /**
     * Recap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recap(Request $request)
    {
        $tahun = $request->query('tahun') ?: date('Y');
        $jenis = $request->query('jenis') ?: 1;

        // Get uang makan
        $uang_makan = [];
        for($i=1; $i<=12; $i++) {
            array_push($uang_makan, [
                'bulan' => DateTimeExt::month($i),
                'pegawai' => UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
                    return $query->where('jenis','=',$jenis);
                })->where('bulan','=',($i < 10 ? '0'.$i : $i))->where('tahun','=',$tahun)->count(),
                'nominal_kotor' => UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
                    return $query->where('jenis','=',$jenis);
                })->where('bulan','=',($i < 10 ? '0'.$i : $i))->where('tahun','=',$tahun)->sum('kotor'),
                'nominal_bersih' => UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
                    return $query->where('jenis','=',$jenis);
                })->where('bulan','=',($i < 10 ? '0'.$i : $i))->where('tahun','=',$tahun)->sum('bersih'),
            ]);
        }

        // Total
        $total_pegawai = UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
            return $query->where('jenis','=',$jenis);
        })->where('tahun','=',$tahun)->count();
        $total_nominal_kotor = UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
            return $query->where('jenis','=',$jenis);
        })->where('tahun','=',$tahun)->sum('kotor');
        $total_nominal_bersih = UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
            return $query->where('jenis','=',$jenis);
        })->where('tahun','=',$tahun)->sum('bersih');

        // View
        return view('admin/uang-makan/recap', [
            'tahun' => $tahun,
            'jenis' => $jenis,
            'uang_makan' => $uang_makan,
            'total_pegawai' => $total_pegawai,
            'total_nominal_kotor' => $total_nominal_kotor,
            'total_nominal_bersih' => $total_nominal_bersih,
        ]);
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

        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $jenis = $request->query('jenis') ?: 1;

        // Get anak satker
        $anak_satker = AnakSatker::find($request->query('id'));

        // Set kategori
        $kategori = $request->kategori == 1 || $request->kategori == 2 ? $request->kategori == 1 ? 'Dosen' : 'Tendik' : '';

        if($anak_satker) {
            // Get uang makan
            $uang_makan = UangMakan::where('kdanak','=',$anak_satker->kode)->where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('jenis','=',$request->query('kategori'))->get();

            if(count($uang_makan) <= 0) {
                echo "Tidak ada data!";
                return;
            }

            // Return
            return Excel::download(new UangMakanExport([
                'uang_makan' => $uang_makan
            ]), 'Uang-Makan '.$anak_satker->nama.' '.$tahun.' '.DateTimeExt::month($bulan).' ('.$kategori.').xlsx');
        }
        elseif(!$anak_satker) {
            if(in_array($request->kategori, [1,2])) {
                // Get uang makan
                $uang_makan = UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
                    return $query->where('jenis','=',$jenis);
                })->where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('jenis','=',$request->query('kategori'))->get();

                if(count($uang_makan) <= 0) {
                    echo "Tidak ada data!";
                    return;
                }

                // Return
                return Excel::download(new UangMakanExport([
                    'uang_makan' => $uang_makan,
                ]), 'Uang-Makan '.($jenis == 1 ? 'PNS' : 'PPPK').' '.$tahun.' '.DateTimeExt::month($bulan).' ('.$kategori.').xlsx');
            }
            else {
                // Get uang makan
                $uang_makan = UangMakan::whereHas('anak_satker', function(Builder $query) use ($jenis) {
                    return $query->where('jenis','=',$jenis);
                })->where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->get();
    
                if(count($uang_makan) <= 0) {
                    echo "Tidak ada data!";
                    return;
                }
    
                // Return
                return Excel::download(new UangMakanExport([
                    'uang_makan' => $uang_makan,
                ]), 'Uang-Makan '.($jenis == 1 ? 'PNS' : 'PPPK').' '.$tahun.' '.DateTimeExt::month($bulan).'.xlsx');
            }
        }
    }
    
    /**
     * Sync
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sync(Request $request)
    {
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");

        // Get uang makan
        $uang_makan = UangMakan::all();
        
        foreach($uang_makan as $um) {
            // Get anak satker
            $anak_satker = AnakSatker::where('kode','=',$um->kdanak)->first();

            // Update
            $update = UangMakan::find($um->id);
            $update->anak_satker_id = $anak_satker->id;
            $update->save();
        }
    }

    public function kdanak_to_unit($kdanak) {
        if($kdanak == "00") $anak = 6;
        elseif($kdanak == "01") $anak = 26;
        elseif($kdanak == "02") $anak = 10;
        elseif($kdanak == "03") $anak = 9;
        elseif($kdanak == "04") $anak = 7;
        elseif($kdanak == "05") $anak = 0;
        elseif($kdanak == "06") $anak = 11;
        elseif($kdanak == "07") $anak = 4;
        elseif($kdanak == "08") $anak = 4;
        elseif($kdanak == "09") $anak = 4;
        elseif($kdanak == "10") $anak = 1;
        elseif($kdanak == "11") $anak = 2;
        elseif($kdanak == "12") $anak = 12;
        else $anak = 0;

        return $anak;
    }
}
