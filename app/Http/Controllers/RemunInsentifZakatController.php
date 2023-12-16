<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use Ajifatur\Helpers\FileExt;
use App\Imports\RemunInsentifImport;
use App\Models\RemunInsentif;
use App\Models\Pegawai;
use App\Models\Unit;

class RemunInsentifZakatController extends Controller
{
    /**
     * Import
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        if($request->method() == 'GET') {
            // Get periode
            $periode = RemunInsentif::where('tahun','=',2023)->where('triwulan','!=',15)->orderBy('triwulan','desc')->groupBy('triwulan')->get();

            // View
            return view('admin/remun-insentif/zakat/import', [
                'periode' => $periode
            ]);
        }
        elseif($request->method() == 'POST') {
            ini_set("memory_limit", "-1");
            ini_set("max_execution_time", "-1");
            
            // Make directory if not exists
            if(!File::exists(public_path('storage/spreadsheets/zakat')))
                File::makeDirectory(public_path('storage/spreadsheets/zakat'));

            // Get tahun dan triwulan
            $explode = explode('-',$request->periode);
            $tahun = $explode[0];
            $triwulan = $explode[1];

            // Get the file
            $file = $request->file('file');
            $filename = FileExt::info($file->getClientOriginalName())['nameWithoutExtension'];
            $extension = FileExt::info($file->getClientOriginalName())['extension'];
            $new = date('Y-m-d-H-i-s').'_'.$filename.'.'.$extension;

            // Move the file
            $file->move(public_path('storage/spreadsheets/zakat'), $new);

            // Get array
            $array = Excel::toArray(new RemunInsentifImport, public_path('storage/spreadsheets/zakat/'.$new));
    
            $error_p = [];
            $error_r = [];
            if(count($array)>0) {
                foreach($array[0] as $key=>$data) {
                    if($data[3] != null) {
                        // Get pegawai
                        $pegawai = Pegawai::where('nip','=',$data[3])->first();

                        if($pegawai) {
                            // Get remun insentif
                            $remun_insentif = RemunInsentif::where('pegawai_id','=',$pegawai->id)->where('tahun','=',$tahun)->where('triwulan','=',$triwulan)->first();

                            if($remun_insentif) {
                                $remun_insentif->pot_zakat = $data[6];
                                $remun_insentif->save();
                            }
                            else array_push($error_r);
                        }
                        else array_push($error_p);
                    }
                }
            }
            // var_dump($error_p, $error_r);
            
            // Redirect
            return redirect()->route('admin.remun-insentif.monitoring', ['triwulan' => $triwulan, 'tahun' => $tahun])->with(['message' => 'Berhasil memproses data.']);
        }
    }
}
