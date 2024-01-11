<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Ajifatur\Helpers\DateTimeExt;
use Ajifatur\Helpers\FileExt;
use App\Imports\GajiNonASNImport;
use App\Models\GajiNonASN;
use App\Models\Pegawai;
use App\Models\Golru;
use App\Models\SK;
use App\Models\Mutasi;

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
        // Get mutasi peralihan BLU ke PTNBH
        $mutasi = Mutasi::where('jenis_id','=',13)->get();
        foreach($mutasi as $key=>$m) {
            // Get gaji
            $mutasi[$key]->gaji = GajiNonASN::where('pegawai_id','=',$m->pegawai_id)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
        }

        // View
        return view('admin/gaji-non-asn/index', [
            'mutasi' => $mutasi,
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
            $tahun_grup = Gaji::where('jenis_id','=',$jenis->id)->orderBy('tahun','desc')->groupBy('tahun')->pluck('tahun')->toArray();

            // Get bulan grup
            foreach($tahun_grup as $t) {
                $bulan_grup = Gaji::where('jenis_id','=',$jenis->id)->where('tahun','=',$t)->orderBy('bulan','desc')->groupBy('bulan')->pluck('bulan')->toArray();
                array_push($tahun_bulan_grup, [
                    'tahun' => $t,
                    'bulan' => $bulan_grup
                ]);
            }

            // Get bulan dan tahun
            $bulan = $request->query('bulan') ?: (int)$tahun_bulan_grup[0]['bulan'][0];
            $tahun = $request->query('tahun') ?: $tahun_bulan_grup[0]['tahun'];
        }
        elseif($jenis->grup == 0) {
            // Get bulan dan tahun
            $bulan = $request->query('bulan') ?: date('n');
            $tahun = $request->query('tahun') ?: date('Y');
        }

        // Get jenis gaji
        $jenis_gaji = JenisGaji::all();

        // Get anak satker
        $anak_satker = AnakSatker::all();

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
            if($jenis)
                $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->where('kdanak','=',$a->kode)->get();
            else
                $gaji = Gaji::where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->where('kdanak','=',$a->kode)->get();


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

            // Count total
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
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tahun_bulan_grup' => $tahun_bulan_grup,
            'jenis_gaji' => $jenis_gaji,
            'data' => $data,
            'total' => $total,
        ]);
    }

    /**
     * Monthly Recap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monthly(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        $tahun = $request->query('tahun') ?: date('Y');

        // Get jenis
        $jenis = JenisGaji::find($request->query('jenis'));

        // Get jenis gaji
        $jenis_gaji = JenisGaji::all();

        // Get anak satker
        $anak_satker_all = AnakSatker::all();

        // Get anak satker
        $anak_satker = AnakSatker::find($request->query('id'));

        // Get gaji
        $gaji = [];
        if($anak_satker) {
            if($jenis)
                $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('kdanak','=',$anak_satker->kode)->where('tahun','=',$tahun)->get();
            else
                $gaji = Gaji::where('kdanak','=',$anak_satker->kode)->where('tahun','=',$tahun)->get();
        }

        // Get kategori gaji
        $kategori_gaji = ['gjpokok', 'tjistri', 'tjanak', 'tjupns', 'tjstruk', 'tjfungs', 'tjdaerah', 'tjpencil', 'tjlain', 'tjkompen', 'pembul', 'tjberas', 'tjpph', 'potpfkbul', 'potpfk2', 'potpfk10', 'potpph', 'potswrum', 'potkelbtj', 'potlain', 'pottabrum', 'bpjs', 'bpjs2'];

        // View
        return view('admin/gaji/monthly', [
            'tahun' => $tahun,
            'anak_satker_all' => $anak_satker_all,
            'anak_satker' => $anak_satker,
            'gaji' => $gaji,
            'jenis' => $jenis,
            'jenis_gaji' => $jenis_gaji,
            'kategori_gaji' => $kategori_gaji,
        ]);
    }

    /**
     * Annually Recap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function annually(Request $request)
    {
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");

        // Get tipe
        $tipe = $request->query('tipe');

        // Get tahun
        $tahun = $request->query('tahun') ?: date('Y');

        // Get jenis
        $jenis = JenisGaji::find($request->query('jenis'));

        // Get jenis gaji
        $jenis_gaji = JenisGaji::all();

        // Get anak satker
        $anak_satker = AnakSatker::where('jenis','=',$tipe)->get();

        // Get gaji
        $gaji = [];
        if($jenis && in_array($request->query('kategori'), [1,2])) {
            $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use ($tipe) {
                return $query->where('jenis','=',$tipe);
            })->where('jenis_id','=',$jenis->id)->where('jenis','=',$request->query('kategori'))->where('tahun','=',$tahun)->get();
        }

        // Get kategori gaji
        $kategori_gaji = ['gjpokok', 'tjistri', 'tjanak', 'tjupns', 'tjstruk', 'tjfungs', 'pembul', 'tjberas', 'tjpph'];

        // View
        return view('admin/gaji/annually', [
            'tipe' => $tipe,
            'tahun' => $tahun,
            'anak_satker' => $anak_satker,
            'gaji' => $gaji,
            'jenis' => $jenis,
            'jenis_gaji' => $jenis_gaji,
            'kategori_gaji' => $kategori_gaji,
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

        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get anak satker
        $anak_satker = AnakSatker::find($request->query('id'));

        // Set kategori
        $kategori = ($request->query('kategori') == 1 || $request->query('kategori') == 2) ? $request->query('kategori') == 1 ? 'Dosen' : 'Tendik' : '';
    
        // Get kategori gaji
        $kategori_gaji = ['gjpokok', 'tjistri', 'tjanak', 'tjupns', 'tjstruk', 'tjfungs', 'tjdaerah', 'tjpencil', 'tjlain', 'tjkompen', 'pembul', 'tjberas', 'tjpph', 'potpfkbul', 'potpfk2', 'potpfk10', 'potpph', 'potswrum', 'potkelbtj', 'potlain', 'pottabrum', 'bpjs', 'bpjs2'];

        // Jika anak satker dan kategori diketahui
        if($anak_satker && $kategori != '') {
            // Get gaji
            $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('kdanak','=',$anak_satker->kode)->where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('jenis','=',$request->query('kategori'))->get();

            // Set nama file
            $filename = $jenis->kode.' '.$anak_satker->nama.' '.$tahun.' '.DateTimeExt::month($bulan).' ('.$kategori.').xlsx';
        }
        // Jika anak satker tidak diketahui dan kategori diketahui
        elseif(!$anak_satker && $kategori != '') {
            // Get gaji
            $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('jenis','=',$request->query('kategori'))->get();

            // Set nama file
            $filename = $jenis->kode.' '.$tahun.' '.DateTimeExt::month($bulan).' ('.$kategori.').xlsx';
        }
        // Jika anak satker dan kategori tidak diketahui
        else {
            // Get gaji
            $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->get();

            // Set nama file
            $filename = $jenis->kode.' '.$tahun.' '.DateTimeExt::month($bulan).'.xlsx';
        }

        if(count($gaji) <= 0) {
            echo "Tidak ada data!";
            return;
        }

        // Return
        return Excel::download(new GajiExport([
            'gaji' => $gaji,
            'kategori' => $kategori,
            'kategori_gaji' => $kategori_gaji,
        ]), $filename);
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
            $array = Excel::toArray(new GajiNonASNImport, public_path('storage/spreadsheets/gaji-non-asn/'.$filename['name']));
            foreach($array[0] as $key=>$data) {
                if($data[1] != null && $data[2] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[1])->orWhere('npu','=',$data[1])->first();

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
                    $gaji->bpjskes1 = is_int($data[3]) ? $data[11] : $data[12];
                    $gaji->bpjsket3 = is_int($data[3]) ? $data[12] : $data[13];
                    $gaji->nominal = is_int($data[3]) ? $data[9] : $data[10];
                    $gaji->save();
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

        // Get SK
        $sk = SK::where('jenis_id','=',5)->where('status','=',1)->first();

        // Get gaji
        $gaji = Gaji::where('jenis_id','=',$request->query('jenis'))->get();
        
        foreach($gaji as $g) {
            // Get anak satker
            $anak_satker = AnakSatker::where('kode','=',$g->kdanak)->first();

            // Update
            $update = Gaji::find($g->id);
            $update->sk_id = $sk->id;
            $update->anak_satker_id = $anak_satker->id;
            $update->save();
        }
    }

    // Sum array
    public function array_sum_range($array, $first, $last) {
        $sum = 0;
        for($i=$first; $i<=$last; $i++) {
            $sum += $array[$i];
        }
        return $sum;
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