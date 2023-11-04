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

        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get anak satker
        $as = AnakSatker::find($id);

        // Get anak satker
        $anak_satker = AnakSatker::all();

        // Get gaji
        $uang_makan = [];
        if($id != 0)
            $uang_makan = Gaji::where('jenis_id','=',$jenis->id)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->where('kdanak','=',$as->kode)->get();

        // View
        return view('admin/gaji/index', [
            'jenis' => $jenis,
            'anak_satker' => $anak_satker,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'gaji' => $uang_makan,
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

        // Get anak satker
        $anak_satker = AnakSatker::all();

        $data = [];
        $total = [
            'dosen_jumlah' => 0,
            'dosen_bersih' => 0,
            'tendik_jumlah' => 0,
            'tendik_bersih' => 0,
        ];
        foreach($anak_satker as $a) {
            $uang_makan = UangMakan::where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->where('kdanak','=',$a->kode)->get();

            // Set angka
            $dosen_jumlah = $uang_makan->where('jenis','=',1)->count();
            $dosen_bersih = $uang_makan->where('jenis','=',1)->sum('bersih');
            $tendik_jumlah = $uang_makan->where('jenis','=',2)->count();
            $tendik_bersih = $uang_makan->where('jenis','=',2)->sum('bersih');

            // Push data
            array_push($data, [
                'anak_satker' => $a,
                'dosen_jumlah' => $dosen_jumlah,
                'dosen_bersih' => $dosen_bersih,
                'tendik_jumlah' => $tendik_jumlah,
                'tendik_bersih' => $tendik_bersih,
            ]);

            // Count total
            $total['dosen_jumlah'] += $dosen_jumlah;
            $total['dosen_bersih'] += $dosen_bersih;
            $total['tendik_jumlah'] += $tendik_jumlah;
            $total['tendik_bersih'] += $tendik_bersih;
        }

        // View
        return view('admin/uang-makan/monitoring', [
            'anak_satker' => $anak_satker,
            'bulan' => $bulan,
            'tahun' => $tahun,
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
    public function excel(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");

        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');

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
            ]), 'Uang-Makan '.$anak_satker->nama.' '.$tahun.' '.\Ajifatur\Helpers\DateTimeExt::month($bulan).' ('.$kategori.').xlsx');
        }
        else {
            // Get uang makan
            $uang_makan = UangMakan::where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('jenis','=',$request->query('kategori'))->get();

            if(count($uang_makan) <= 0) {
                echo "Tidak ada data!";
                return;
            }

            // Return
            return Excel::download(new UangMakanExport([
                'uang_makan' => $uang_makan,
            ]), 'Uang-Makan '.$tahun.' '.\Ajifatur\Helpers\DateTimeExt::month($bulan).' ('.$kategori.').xlsx');
        }
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
        
        // Make directory if not exists
        if(!File::exists(public_path('storage/spreadsheets/um')))
            File::makeDirectory(public_path('storage/spreadsheets/um'));

        // Get the file
        $file = $request->file('file');
        $filename = FileExt::info($file->getClientOriginalName())['nameWithoutExtension'];
        $extension = FileExt::info($file->getClientOriginalName())['extension'];
        $new = date('Y-m-d-H-i-s').'_'.$filename.'.'.$extension;

        // Move the file
		$file->move(public_path('storage/spreadsheets/um'), $new);

        // Get array
		$array = Excel::toArray(new UangMakanImport, public_path('storage/spreadsheets/um/'.$new));

        $anak_satker = '';
        $bulan = '';
        $bulanAngka = '';
        $tahun = '';
        if(count($array)>0) {
            foreach($array[0] as $key=>$data) {
                if($data[1] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[5])->first();

                    // Get uang makan
                    $uang_makan = UangMakan::where('kdanak','=',$request->anak_satker)->where('bulan','=',$data[1])->where('tahun','=',$data[2])->where('nip','=',$data[5])->first();
                    if(!$uang_makan) $uang_makan = new UangMakan;

                    // Simpan uang makan
                    $uang_makan->pegawai_id = $pegawai ? $pegawai->id : 0;
                    $uang_makan->unit_id = $this->kdanak_to_unit($request->anak_satker);
                    $uang_makan->jenis = $pegawai ? $pegawai->jenis : 0;
                    $uang_makan->kdanak = $request->anak_satker;
                    $uang_makan->bulan = $data[1];
                    $uang_makan->tahun = $data[2];
                    $uang_makan->nip = $data[5];
                    $uang_makan->nama = $data[6];
                    $uang_makan->jmlhari = $data[14];
                    $uang_makan->tarif = $data[15];
                    $uang_makan->pph = $data[16];
                    $uang_makan->kotor = $data[17];
                    $uang_makan->potongan = $data[18];
                    $uang_makan->bersih = $data[19];
                    $uang_makan->save();

                    // Get anak satker, bulan, tahun
                    if($key == 0) {
                        $a = AnakSatker::where('kode','=',$request->anak_satker)->first();
                        $anak_satker = $a->nama;
                        $bulan = DateTimeExt::month((int)$data[1]);
                        $bulanAngka = (int)$data[1];
                        $tahun = $data[2];
                    }
                }
            }
        }

        // Rename the file
		File::move(public_path('storage/spreadsheets/um/'.$new), public_path('storage/spreadsheets/um/'.$anak_satker.'_'.$tahun.'_'.$bulan.'.'.$extension));

        // Delete the file
        File::delete(public_path('storage/spreadsheets/um/'.$new));

        // Redirect
        return redirect()->route('admin.uang-makan.monitoring', ['bulan' => $bulanAngka, 'tahun' => $tahun])->with(['message' => 'Berhasil memproses data.']);
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
        $uang_makan_bulan_ini = Gaji::where('jenis_id','=',1)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();

        // Set tanggal sebelumnya
        $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

        // Get gaji bulan sebelumnya
        $uang_makan_bulan_sebelumnya = Gaji::where('jenis_id','=',1)->where('bulan','=',date('m', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();

        // Pegawai masuk
        $cek_bulan_ini = [];
        if(count($uang_makan_bulan_ini) > 0) {
            foreach($uang_makan_bulan_ini->pluck('pegawai_id')->toArray() as $t) {
                if(!in_array($t, $uang_makan_bulan_sebelumnya))
                    array_push($cek_bulan_ini, $t);
            }
        }
		$pegawai_on = Pegawai::whereIn('id', $cek_bulan_ini)->get();

        // Pegawai keluar
        $cek_bulan_sebelumnya = [];
        if(count($uang_makan_bulan_sebelumnya) > 0) {
            foreach($uang_makan_bulan_sebelumnya as $t) {
                if(!in_array($t, $uang_makan_bulan_ini->pluck('pegawai_id')->toArray()))
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
		foreach($uang_makan_bulan_ini as $g) {
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
            'gaji_bulan_ini' => $uang_makan_bulan_ini,
            'gaji_bulan_sebelumnya' => $uang_makan_bulan_sebelumnya,
            'pegawai_on' => $pegawai_on,
            'pegawai_off' => $pegawai_off,
            'perubahan_gjpokok' => $perubahan_gjpokok,
            'perubahan_tjfungs' => $perubahan_tjfungs,
            'perubahan_tjistri' => $perubahan_tjistri,
            'perubahan_tjanak' => $perubahan_tjanak,
            'perubahan_unit' => $perubahan_unit,
        ]);
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
