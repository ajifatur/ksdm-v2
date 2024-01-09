<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\FileExt;
use App\Imports\ReferensiImport;
use App\Models\Referensi;
use App\Models\JabatanDasar;
use App\Models\SK;

class ReferensiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Get SK aktif
        $sk_aktif = SK::where('jenis_id','=',1)->where('status','=',1)->first();

        // Set SK
        $sk_id = $request->query('sk') ?: $sk_aktif->id;

        // Get SK
        $sk = SK::where('jenis_id','=',1)->orderBy('status','desc')->orderBy('tanggal','desc')->get();
		
		// Get referensi
		$referensi = Referensi::where('sk_id','=',$sk_id)->groupBy('jabatan_dasar_id')->orderBy('remun_standar','desc')->get();

        foreach($referensi as $key=>$r) {
            $referensi[$key]->layer_1 = Referensi::where('sk_id','=',$sk_id)->where('jabatan_dasar_id','=',$r->jabatan_dasar_id)->where('layer_id','=',1)->first();
            $referensi[$key]->layer_2 = Referensi::where('sk_id','=',$sk_id)->where('jabatan_dasar_id','=',$r->jabatan_dasar_id)->where('layer_id','=',2)->first();
            $referensi[$key]->layer_3 = Referensi::where('sk_id','=',$sk_id)->where('jabatan_dasar_id','=',$r->jabatan_dasar_id)->where('layer_id','=',3)->first();
        }
		
		// View
		return view('admin/referensi/index', [
			'sk' => $sk,
			'sk_aktif' => $sk_aktif,
			'sk_id' => $sk_id,
			'referensi' => $referensi
		]);
    }

    /**
     * Show the detail of the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);
    }
    
    /**
     * Import from Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        // Get SK
        $sk = SK::find(12); // SK Remun Awal Tahun 2024

		$array = Excel::toArray(new ReferensiImport, public_path('storage/Referensi_2024_01.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                // Get jabatan dasar
                $jabatan_dasar = JabatanDasar::where('sk_id','=',$sk->id)->where('nama','=',$data[0])->first();
                if(!$jabatan_dasar) $jabatan_dasar = new JabatanDasar;

                // Simpan data jabatan dasar
                $jabatan_dasar->sk_id = $sk->id;
                $jabatan_dasar->nama = $data[0];
                $jabatan_dasar->grade = $data[1];
                $jabatan_dasar->nilai = $data[2];
                $jabatan_dasar->koefisien = $data[3];
                $jabatan_dasar->pir = $sk->pir;
                $jabatan_dasar->save();

                // Cek referensi layer 1
                $referensi = Referensi::where('sk_id','=',$sk->id)->where('jabatan_dasar_id','=',$jabatan_dasar->id)->where('layer_id','=',1)->first();
                if(!$referensi) $referensi = new Referensi;

                // Simpan data referensi layer 1
                $referensi->sk_id = $sk->id;
                $referensi->jabatan_dasar_id = $jabatan_dasar->id;
                $referensi->layer_id = 1;
                $referensi->remun_standar = $data[6];
                $referensi->remun_gaji = $data[7];
                $referensi->remun_insentif = $data[8];
                $referensi->poin_standar = $data[9];
                $referensi->harga_per_poin = $data[10];
                $referensi->save();

                // Cek referensi layer 2
                $referensi = Referensi::where('sk_id','=',$sk->id)->where('jabatan_dasar_id','=',$jabatan_dasar->id)->where('layer_id','=',2)->first();
                if(!$referensi) $referensi = new Referensi;

                // Simpan data referensi layer 2
                $referensi->sk_id = $sk->id;
                $referensi->jabatan_dasar_id = $jabatan_dasar->id;
                $referensi->layer_id = 2;
                $referensi->remun_standar = $data[11];
                $referensi->remun_gaji = $data[12];
                $referensi->remun_insentif = $data[13];
                $referensi->poin_standar = $data[14];
                $referensi->harga_per_poin = $data[15];
                $referensi->save();

                // Cek referensi layer 3
                $referensi = Referensi::where('sk_id','=',$sk->id)->where('jabatan_dasar_id','=',$jabatan_dasar->id)->where('layer_id','=',3)->first();
                if(!$referensi) $referensi = new Referensi;

                // Simpan data referensi layer 3
                $referensi->sk_id = $sk->id;
                $referensi->jabatan_dasar_id = $jabatan_dasar->id;
                $referensi->layer_id = 3;
                $referensi->remun_standar = $data[16];
                $referensi->remun_gaji = $data[17];
                $referensi->remun_insentif = $data[18];
                $referensi->poin_standar = $data[19];
                $referensi->harga_per_poin = $data[20];
                $referensi->save();
            }
        }
    }
}
