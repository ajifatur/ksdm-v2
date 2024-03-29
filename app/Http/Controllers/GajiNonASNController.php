<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Ajifatur\Helpers\DateTimeExt;
use Ajifatur\Helpers\FileExt;
use App\Exports\GajiNonASNExport;
use App\Imports\ByStartRowImport;
use App\Models\GajiNonASN;
use App\Models\Pegawai;
use App\Models\Golru;
use App\Models\SK;
use App\Models\Mutasi;
use App\Models\UMK;
use App\Models\Unit;

class GajiNonASNController extends Controller
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
        $id = $request->query('id') ?: 0;

        // Get unit
        $u = Unit::find($id);

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->where('nama','!=','-')->orderBy('num_order','asc')->get();

        // Get gaji
        $gaji = [];
        if($id != 0)
            $gaji = GajiNonASN::where('unit_id','=',$u->id)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();

        // View
        return view('admin/gaji-non-asn/index', [
            'unit' => $unit,
            'bulan' => $bulan,
            'tahun' => $tahun,
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
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->where('nama','!=','-')->orderBy('num_order','asc')->get();

        $data = [];
        $total = [
            'dosen_jumlah' => 0,
            'dosen_nominal' => 0,
            'dosen_bersih' => 0,
            'tendik_jumlah' => 0,
            'tendik_nominal' => 0,
            'tendik_bersih' => 0,
        ];
        foreach($unit as $u) {
            // Get gaji
            $gaji = GajiNonASN::where('unit_id','=',$u->id)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();

            // Set angka
            $dosen_jumlah = $gaji->where('jenis','=',1)->count();
            $dosen_nominal = $gaji->where('jenis','=',1)->sum('nominal') + $gaji->where('jenis','=',1)->sum('pembul');
            $dosen_bersih = $gaji->where('jenis','=',1)->sum('bersih');
            $tendik_jumlah = $gaji->where('jenis','=',2)->count();
            $tendik_nominal = $gaji->where('jenis','=',2)->sum('nominal') + $gaji->where('jenis','=',2)->sum('pembul');
            $tendik_bersih = $gaji->where('jenis','=',2)->sum('bersih');

            // Push data
            array_push($data, [
                'unit' => $u,
                'dosen_jumlah' => $dosen_jumlah,
                'dosen_nominal' => $dosen_nominal,
                'dosen_bersih' => $dosen_bersih,
                'tendik_jumlah' => $tendik_jumlah,
                'tendik_nominal' => $tendik_nominal,
                'tendik_bersih' => $tendik_bersih,
            ]);

            // Count total
            $total['dosen_jumlah'] += $dosen_jumlah;
            $total['dosen_nominal'] += $dosen_nominal;
            $total['dosen_bersih'] += $dosen_bersih;
            $total['tendik_jumlah'] += $tendik_jumlah;
            $total['tendik_nominal'] += $tendik_nominal;
            $total['tendik_bersih'] += $tendik_bersih;
        }

        // View
        return view('admin/gaji-non-asn/monitoring', [
            'unit' => $unit,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
            'data' => $data,
            'total' => $total,
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
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");

        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');

        // Get gaji
        $gaji = GajiNonASN::where('tahun','=',$tahun)->where('bulan','=',$bulan)->get();

        // Return
        return Excel::download(new GajiNonASNExport($gaji), 'Gaji Pegawai Tetap Non ASN ('.$tahun.' '.DateTimeExt::month($bulan).').xlsx');
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

        // Get SK
        $sk = SK::where('jenis_id','=',6)->where('status','=',1)->first();

        $error = [];
        $files = FileExt::get(public_path('storage/spreadsheets/gaji-non-asn'));
        foreach($files as $file) {
            // Get file
            $filename = FileExt::info($file->getRelativePathname());

            // Get bulan, tahun
            $months = DateTimeExt::month();
            $explode = explode('_', $filename['nameWithoutExtension']);
            $bulan = array_search($explode[1], $months) + 1;
            $tahun = $explode[2];
            
            // Get data
            $array = Excel::toArray(new ByStartRowImport(6), public_path('storage/spreadsheets/gaji-non-asn/'.$filename['name']));
            foreach($array[0] as $key=>$data) {
                if($data[1] != null && $data[2] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[1])->orWhere('npu','=',$data[1])->first();

                    if($pegawai) {
                        // Simpan gaji
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                        if(!$gaji) $gaji = new GajiNonASN;
                        $gaji->sk_id = $sk->id;
                        $gaji->pegawai_id = $pegawai->id;
                        $gaji->golru_id = 0;
                        $gaji->unit_id = $pegawai->unit_id;
                        $gaji->jenis = $pegawai->jenis;
                        $gaji->bulan = $bulan;
                        $gaji->tahun = $tahun;
                        $gaji->gjpokok = is_int($data[3]) ? $data[3] : $data[4];
                        $gaji->tjistri = is_int($data[3]) ? $data[4] : $data[5];
                        $gaji->tjanak = is_int($data[3]) ? $data[5] : $data[6];
                        $gaji->tjberas = is_int($data[3]) ? $data[6] : $data[7];
                        $gaji->tjumum = is_int($data[3]) ? $data[7] : $data[8];
                        $gaji->tjfungs = is_int($data[3]) ? $data[8] : $data[9];
                        $gaji->pembul = is_int($data[3]) ? $data[16] : $data[17];
                        $gaji->bpjskes1 = is_int($data[3]) ? $data[11] : $data[12];
                        $gaji->bpjsket3 = is_int($data[3]) ? $data[12] : $data[13];
                        $gaji->nominal = is_int($data[3]) ? $data[9] : $data[10];
                        $gaji->upah = is_int($data[3]) ? $data[10] : $data[11];
                        $gaji->bersih = is_int($data[3]) ? $data[15] : $data[16];
                        $gaji->save();
                    }
                    else {
                        array_push($error, $data[2]);
                    }
                }
            }
        }
        var_dump($error);
        return;
    }

    /**
     * Perubahan Gaji Induk
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function change(Request $request)
    {
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");
		
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Get gaji bulan ini
        $gaji_bulan_ini = Gaji::where('jenis_id','=',1)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();

        // Set tanggal sebelumnya
        $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

        // Get gaji bulan sebelumnya
        $gaji_bulan_sebelumnya = Gaji::where('jenis_id','=',1)->where('bulan','=',date('m', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();

        // Pegawai masuk
        $cek_bulan_ini = [];
        if(count($gaji_bulan_ini) > 0) {
            foreach($gaji_bulan_ini->pluck('pegawai_id')->toArray() as $t) {
                if(!in_array($t, $gaji_bulan_sebelumnya))
                    array_push($cek_bulan_ini, $t);
            }
        }
		$pegawai_on = Pegawai::whereIn('id', $cek_bulan_ini)->get();

        // Pegawai keluar
        $cek_bulan_sebelumnya = [];
        if(count($gaji_bulan_sebelumnya) > 0) {
            foreach($gaji_bulan_sebelumnya as $t) {
                if(!in_array($t, $gaji_bulan_ini->pluck('pegawai_id')->toArray()))
                    array_push($cek_bulan_sebelumnya, $t);
            }
        }
		$pegawai_off = Pegawai::whereIn('id', $cek_bulan_sebelumnya)->get();
		
		// Perubahan gaji
		$perubahan_gjpokok = [];
		$perubahan_tjfungs = [];
		$perubahan_tjistri = [];
		$perubahan_tjanak = [];
		$perubahan_unit = [];
		foreach($gaji_bulan_ini as $g) {
			// Get gaji bulan sebelumnya
			$gs = Gaji::where('jenis_id','=',1)->where('pegawai_id','=',$g->pegawai_id)->where('bulan','=',date('m', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->first();
			if($gs) {
				if($g->gjpokok != $gs->gjpokok) array_push($perubahan_gjpokok, ['pegawai' => $g->pegawai, 'sebelum' => $gs->gjpokok, 'sesudah' => $g->gjpokok]);
				if($g->tjfungs != $gs->tjfungs) array_push($perubahan_tjfungs, ['pegawai' => $g->pegawai, 'sebelum' => $gs->tjfungs, 'sesudah' => $g->tjfungs]);
				if(($g->tjistri / (($g->gjpokok * 10) / 100)) != ($gs->tjistri / (($gs->gjpokok * 10) / 100))) array_push($perubahan_tjistri, ['pegawai' => $g->pegawai, 'sebelum' => ($gs->tjistri / (($gs->gjpokok * 10) / 100)), 'sesudah' => ($g->tjistri / (($g->gjpokok * 10) / 100))]);
				if(($g->tjanak / (($g->gjpokok * 2) / 100)) != ($gs->tjanak / (($gs->gjpokok * 2) / 100))) array_push($perubahan_tjanak, ['pegawai' => $g->pegawai, 'sebelum' => ($gs->tjanak / (($gs->gjpokok * 2) / 100)), 'sesudah' => ($g->tjanak / (($g->gjpokok * 2) / 100))]);
				if($g->unit_id != $gs->unit_id) array_push($perubahan_unit, ['pegawai' => $g->pegawai, 'sebelum' => $gs->unit, 'sesudah' => $g->unit]);
			}
		}
		
        // View
        return view('admin/gaji/change', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'gaji_bulan_ini' => $gaji_bulan_ini,
            'gaji_bulan_sebelumnya' => $gaji_bulan_sebelumnya,
            'pegawai_on' => $pegawai_on,
            'pegawai_off' => $pegawai_off,
            'perubahan_gjpokok' => $perubahan_gjpokok,
            'perubahan_tjfungs' => $perubahan_tjfungs,
            'perubahan_tjistri' => $perubahan_tjistri,
            'perubahan_tjanak' => $perubahan_tjanak,
            'perubahan_unit' => $perubahan_unit,
        ]);
    }
}
