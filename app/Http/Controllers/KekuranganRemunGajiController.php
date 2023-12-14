<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\RemunGajiImport;
use App\Models\KekuranganRemunGaji;
use App\Models\RemunGaji;
use App\Models\Pegawai;
use App\Models\Unit;

class KekuranganRemunGajiController extends Controller
{   

    /**
     * Monitoring.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monitoring(Request $request)
    {
        // Get unit
        $unit = Unit::whereIn('id',KekuranganRemunGaji::where('kekurangan_id','=',1)->groupBy('unit_id')->pluck('unit_id')->toArray())->where('nama','!=','-')->orderBy('pusat','asc')->orderBy('num_order','asc')->get();

        $data = [];
        $isPusat = 0;
        foreach($unit as $u) {
            $nominal = [];
            $nominal_pusat = [];
            for($i=1; $i<=2; $i++) {
                // Append pusat
                if($isPusat == 0 && $u->pusat == 1) {
                    // Get unit pusat
                    $unit_pusat = Unit::whereIn('id',KekuranganRemunGaji::where('kekurangan_id','=',1)->groupBy('unit_id')->pluck('unit_id')->toArray())->where('pusat','=',1)->pluck('id')->toArray();
    
                    // Count kekurangan
                    $kekurangan = KekuranganRemunGaji::whereIn('unit_id',$unit_pusat)->where('kekurangan_id','=',1)->where('kategori','=',$i)->get();
    
                    // Get pegawai
                    $pegawai = $kekurangan->pluck('pegawai_id')->toArray();

                    array_push($nominal_pusat, [
                        'kategori' => $i,
                        'pegawai' => $kekurangan->count(),
                        'kekurangan' => $kekurangan->where('selisih','>=',0)->sum('selisih'),
                    ]);
                }

                // Count kekurangan
                $kekurangan = KekuranganRemunGaji::where('unit_id','=',$u->id)->where('kekurangan_id','=',1)->where('kategori','=',$i)->get();

                // Get pegawai
                $pegawai = $kekurangan->pluck('pegawai_id')->toArray();

                array_push($nominal, [
                    'kategori' => $i,
                    'pegawai' => $kekurangan->count(),
                    'kekurangan' => $kekurangan->where('selisih','>=',0)->sum('selisih'),
                ]);
            }

            // Append pusat
            if($isPusat == 0 && $u->pusat == 1) {
                array_push($data, [
                    'unit' => 'Pusat',
                    'kekurangan' => 1,
                    'nominal' => $nominal_pusat
                ]);
            }

            if($nominal[0]['pegawai'] > 0 || $nominal[1]['pegawai'] > 0) {
                array_push($data, [
                    'unit' => $u,
                    'kekurangan' => 1,
                    'nominal' => $nominal
                ]);
            }

            $isPusat = $u->pusat;
        }

        // View
        return view('admin/remun-gaji/kekurangan/monitoring', [
            'unit' => $unit,
            'data' => $data,
        ]);
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
		
		$kekurangan = KekuranganRemunGaji::whereHas('pegawai', function(Builder $query) {
			return $query->whereIn('nama',['Sinta Saraswati','Sungkowo Edy Mulyono']);
		})->get();
		foreach($kekurangan as $k) {
			// Cek remun April
			$remun_gaji_april = RemunGaji::where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',4)->orderBy('tahun','desc')->orderBy('bulan','desc')->first();

			$update = KekuranganRemunGaji::find($k->id);
			$update->seharusnya3 = $remun_gaji_april->remun_gaji;
			$update->selisih3 = $update->seharusnya3 - $update->dibayarkan3;
			$update->seharusnya = $update->seharusnya1 + $update->seharusnya2 + $update->seharusnya3;
			$update->selisih = $update->selisih1 + $update->selisih2 + $update->selisih3;
			$update->save();
		}
		
		/*
		$kekurangan = KekuranganRemunGaji::all();
		foreach($kekurangan as $k) {
			// Cek remun maksimal Maret
			$remun_gaji = RemunGaji::where('pegawai_id','=',$k->pegawai_id)->where('bulan','<=',3)->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
			if($remun_gaji && $remun_gaji->status_kepeg_id == 2) {
				// Cek remun April
				$remun_gaji_april = RemunGaji::where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',4)->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
				
				$update = KekuranganRemunGaji::find($k->id);
				$update->seharusnya3 = $remun_gaji_april->remun_gaji;
				$update->selisih3 = $update->seharusnya3 - $update->dibayarkan3;
				$update->seharusnya = $update->seharusnya1 + $update->seharusnya2 + $update->seharusnya3;
				$update->selisih = $update->selisih1 + $update->selisih2 + $update->selisih3;
				$update->save();
			}
		}
		*/
		
		/*
		$unit = Unit::whereIn('nama',['FIPP','FISIP','FEB','FK','DAKK','DPK','DSIH','DUSDM'])->pluck('id')->toArray();
		$kekurangan = KekuranganRemunGaji::whereIn('unit_id',$unit)->get();
		foreach($kekurangan as $k) {
			$pegawai_non_aktif = Pegawai::where('nama','=',$k->pegawai->nama)->where('status_kerja_id','!=',1)->first();
			var_dump($k->pegawai->nama);
			var_dump($k->pegawai->nip);
			var_dump($k->pegawai_id);
			var_dump($pegawai_non_aktif->id);
			var_dump($pegawai_non_aktif->nip);
			echo "<br>";
			$pegawai_non_aktif = Pegawai::where('nama','=',$k->pegawai->nama)->where('status_kerja_id','!=',1)->first();
			$kk = KekuranganRemunGaji::find($k->id);
			$kk->pegawai_id = $pegawai_non_aktif->id;
			$kk->save();
		}
		*/
		
		/*
		// Get kekurangan 
		$kekurangan = KekuranganRemunGaji::all();
		foreach($kekurangan as $k) {
			// Cek remun maksimal April
			$remun_gaji = RemunGaji::where('pegawai_id','=',$k->pegawai_id)->where('bulan','<=',4)->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
			
			// Simpan data kekurangan
			if($remun_gaji) {
			$kk = KekuranganRemunGaji::find($k->id);
			$kk->status_kepeg_id = $remun_gaji->status_kepeg_id;
			$kk->golru_id = $remun_gaji->status_kepeg_id;
			$kk->jabatan_dasar_id = $remun_gaji->jabatan_dasar_id;
			$kk->jabatan_id = $remun_gaji->jabatan_id;
			$kk->unit_id = $remun_gaji->unit_id;
			$kk->layer_id = $remun_gaji->layer_id;
			$kk->save();
			}
		}
		return;
		*/

		/*
		$array = Excel::toArray(new RemunGajiImport, public_path('storage/Kekurangan Jan-Mar 2023.xlsx'));

        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[1])->first();

                    // Cek remun gaji terakhir
                    $remun_gaji = RemunGaji::where('pegawai_id','=',$pegawai->id)->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
                    if(!$remun_gaji) array_push($error, $data[2]);

                    // Get kekurangan
                    $kekurangan = KekuranganRemunGaji::where('pegawai_id','=',$pegawai->id)->where('kekurangan_id','=',1)->first();
                    if(!$kekurangan) $kekurangan = new KekuranganRemunGaji;

                    // Simpan data kekurangan
                    $kekurangan->pegawai_id = $pegawai->id;
                    $kekurangan->kekurangan_id = 1;
                    $kekurangan->status_kepeg_id = $remun_gaji->status_kepeg_id;
                    $kekurangan->golru_id = $remun_gaji->status_kepeg_id;
                    $kekurangan->jabatan_dasar_id = $remun_gaji->jabatan_dasar_id;
                    $kekurangan->jabatan_id = $remun_gaji->jabatan_id;
                    $kekurangan->unit_id = $remun_gaji->unit_id;
                    $kekurangan->layer_id = $remun_gaji->layer_id;
                    $kekurangan->bulan = 12;
                    $kekurangan->tahun = 2023;
                    $kekurangan->kategori = $remun_gaji->kategori;
                    $kekurangan->dibayarkan = $data[12];
                    $kekurangan->seharusnya = $data[13];
                    $kekurangan->selisih = $data[14];
                    $kekurangan->dibayarkan1 = $data[3];
                    $kekurangan->seharusnya1 = $data[4];
                    $kekurangan->selisih1 = $data[5];
                    $kekurangan->dibayarkan2 = $data[6];
                    $kekurangan->seharusnya2 = $data[7];
                    $kekurangan->selisih2 = $data[8];
                    $kekurangan->dibayarkan3 = $data[9];
                    $kekurangan->seharusnya3 = $data[10];
                    $kekurangan->selisih3 = $data[11];
                    $kekurangan->save();
                }
            }
        }
        var_dump($error);
		*/
    }

    /**
     * Print PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function print(Request $request)
    {
        // Check the access
        // has_access(method(__METHOD__), Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $kategori = $request->query('kategori');
        $unit = $request->query('unit');

        // Get unit
        $unit = Unit::findOrFail($request->query('unit'));

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        // Get kekurangan
        $kekurangan = KekuranganRemunGaji::where('unit_id','=',$request->query('unit'))->where('kekurangan_id','=',1)->where('kategori','=',$kategori)->orderBy('selisih','desc')->orderBy('status_kepeg_id','asc')->get();

        // Set title
        $title = 'Kekurangan Remun Gaji '.$unit->nama.' '.$get_kategori.' (Januari-Maret 2023)';

        // PDF
        $pdf = \PDF::loadView('admin/remun-gaji/kekurangan/print', [
            'title' => $title,
            'unit' => $unit,
            'kategori' => $kategori,
            'kekurangan' => $kekurangan
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }
}
