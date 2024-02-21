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
use App\Imports\ByStartRowImport;
use App\Models\UangMakan;
use App\Models\AnakSatker;
use App\Models\Pegawai;
use App\Models\PegawaiNonAktif;

class UangMakanImportController extends Controller
{    
    /**
     * Import (PNS)
     *
     * @return \Illuminate\Http\Response
     */
    public function pns(Request $request)
    {
        if($request->method() == 'GET') {
            // Get anak satker
            $anak_satker = AnakSatker::where('jenis','=',1)->where('nama','!=','Bantuan Pangan')->get();

            // View
            return view('admin/uang-makan/import', [
                'anak_satker' => $anak_satker,
                'jenis' => 'PNS',
                'route' => 'admin.uang-makan.import.pns',
            ]);
        }
        elseif($request->method() == 'POST') {
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
            $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/spreadsheets/um/'.$new));

            $anak_satker = '';
            $bulan = '';
            $bulanAngka = '';
            $tahun = '';
            if(count($array)>0) {
                foreach($array[0] as $key=>$data) {
                    if($data[1] != null) {
                        // Get pegawai
                        $pegawai = Pegawai::where('nip','=',$data[5])->first();

                        // Get anak satker
                        $as = AnakSatker::where('kode','=',$request->anak_satker)->first();

                        // Get uang makan
                        $uang_makan = UangMakan::where('kdanak','=',$request->anak_satker)->where('bulan','=',$data[1])->where('tahun','=',$data[2])->where('nip','=',$data[5])->first();
                        if(!$uang_makan) $uang_makan = new UangMakan;

                        // Simpan uang makan
                        $uang_makan->pegawai_id = $pegawai ? $pegawai->id : 0;
                        $uang_makan->unit_id = kdanak_to_unit($request->anak_satker, $pegawai->id);
                        $uang_makan->anak_satker_id = $as->id;
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
            return redirect()->route('admin.uang-makan.monitoring', ['bulan' => $bulanAngka, 'tahun' => $tahun, 'jenis' => 1])->with(['message' => 'Berhasil memproses data.']);
        }
    }

    /**
     * Import (PPPK)
     *
     * @return \Illuminate\Http\Response
     */
    public function pppk(Request $request)
    {
        if($request->method() == 'GET') {
            // Get anak satker
            $anak_satker = AnakSatker::where('jenis','=',2)->get();

            // View
            return view('admin/uang-makan/import', [
                'anak_satker' => $anak_satker,
                'jenis' => 'PPPK',
                'route' => 'admin.uang-makan.import.pppk',
            ]);
        }
        elseif($request->method() == 'POST') {
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
            $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/spreadsheets/um/'.$new));

            $anak_satker = '';
            $bulan = '';
            $bulanAngka = '';
            $tahun = '';
            if(count($array)>0) {
                foreach($array[0] as $key=>$data) {
                    if($data[1] != null) {
                        // Get pegawai
                        $pegawai = Pegawai::where('nip','=',$data[7])->first();

                        // Get PPH
                        if(in_array($data[9], ['XIII','XIV','XV','XVI','XVII'])) {
                            $pph = 15;
                        }
                        elseif(in_array($data[9], ['IX','X','XI','XII'])) {
                            $pph = 5;
                        }
                        else {
                            $pph = 0;
                        }

                        // Get anak satker
                        $as = AnakSatker::where('kode','=',$request->anak_satker)->first();

                        // Get uang makan
                        $uang_makan = UangMakan::where('kdanak','=',$request->anak_satker)->where('bulan','=',$data[2])->where('tahun','=',$data[3])->where('nip','=',$data[7])->first();
                        if(!$uang_makan) $uang_makan = new UangMakan;

                        // Simpan uang makan
                        $uang_makan->pegawai_id = $pegawai ? $pegawai->id : 0;
                        $uang_makan->unit_id = $pegawai->unit_id;
                        $uang_makan->anak_satker_id = $as->id;
                        $uang_makan->jenis = $pegawai ? $pegawai->jenis : 0;
                        $uang_makan->kdanak = $request->anak_satker;
                        $uang_makan->bulan = $data[2];
                        $uang_makan->tahun = $data[3];
                        $uang_makan->nip = $data[7];
                        $uang_makan->nama = $data[10];
                        $uang_makan->jmlhari = $data[11];
                        $uang_makan->tarif = $data[12];
                        $uang_makan->pph = $pph;
                        $uang_makan->kotor = $data[13];
                        $uang_makan->potongan = $data[14];
                        $uang_makan->bersih = $data[15];
                        $uang_makan->save();

                        // Get anak satker, bulan, tahun
                        if($key == 0) {
                            $a = AnakSatker::where('kode','=',$request->anak_satker)->first();
                            $anak_satker = $a->nama;
                            $bulan = DateTimeExt::month((int)$data[2]);
                            $bulanAngka = (int)$data[2];
                            $tahun = $data[3];
                        }
                    }
                }
            }

            // Rename the file
            File::move(public_path('storage/spreadsheets/um/'.$new), public_path('storage/spreadsheets/um/'.$anak_satker.'_'.$tahun.'_'.$bulan.'.'.$extension));

            // Delete the file
            File::delete(public_path('storage/spreadsheets/um/'.$new));

            // Redirect
            return redirect()->route('admin.uang-makan.monitoring', ['bulan' => $bulanAngka, 'tahun' => $tahun, 'jenis' => 2])->with(['message' => 'Berhasil memproses data.']);
        }
    }
    
    /**
     * Import (Format Lama)
     *
     * @return \Illuminate\Http\Response
     */
    public function old(Request $request)
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
		$array = Excel::toArray(new ByStartRowImport(2), public_path('storage/spreadsheets/um/'.$new));

        $anak_satker = '';
        $bulan = '';
        $bulanAngka = '';
        $tahun = '';
        if(count($array)>0) {
            // Check pegawai
            $cek_pegawai = Pegawai::where('nip','=',$array[0][0][3])->first();
            if($cek_pegawai) {
                foreach($array[0] as $key=>$data) {
                    if($data[0] != null) {
                        // Get pegawai
                        $pegawai = Pegawai::where('nip','=',$data[3])->first();

                        // Get tarif
                        if($data[7] == 15) $tarif = 41000;
                        elseif($data[7] == 5) $tarif = 37000;
                        else $tarif = 35000;

                        // Get anak satker
                        $as = AnakSatker::where('kode','=',$request->anak_satker)->first();

                        // Get uang makan
                        $uang_makan = UangMakan::where('kdanak','=',$request->anak_satker)->where('bulan','=',($request->bulan < 10 ? '0'.$request->bulan : $request->bulan))->where('tahun','=',$request->tahun)->where('nip','=',$data[3])->first();
                        if(!$uang_makan) $uang_makan = new UangMakan;

                        // Simpan uang makan
                        $uang_makan->pegawai_id = $pegawai ? $pegawai->id : 0;
                        $uang_makan->unit_id = kdanak_to_unit($request->anak_satker, $pegawai->id);
                        $uang_makan->anak_satker_id = $as->id;
                        $uang_makan->jenis = $pegawai ? $pegawai->jenis : 0;
                        $uang_makan->kdanak = $request->anak_satker;
                        $uang_makan->bulan = $request->bulan < 10 ? '0'.$request->bulan : $request->bulan;
                        $uang_makan->tahun = $request->tahun;
                        $uang_makan->nip = $data[3];
                        $uang_makan->nama = $data[2];
                        $uang_makan->jmlhari = $data[6] / $tarif;
                        $uang_makan->tarif = $tarif;
                        $uang_makan->pph = $data[7];
                        $uang_makan->kotor = $data[6];
                        $uang_makan->potongan = $data[8];
                        $uang_makan->bersih = $data[6] - $data[8];
                        $uang_makan->save();

                        // Get anak satker, bulan, tahun
                        if($key == 0) {
                            $a = AnakSatker::where('kode','=',$request->anak_satker)->first();
                            $anak_satker = $a->nama;
                            $bulan = DateTimeExt::month($request->bulan);
                            $bulanAngka = $request->bulan;
                            $tahun = $request->tahun;
                        }
                    }
                }
            }
            else {
                return redirect()->back()->with(['message' => 'Format file Excel tidak sesuai!']);
            }
        }

        // Rename the file
		File::move(public_path('storage/spreadsheets/um/'.$new), public_path('storage/spreadsheets/um/'.$anak_satker.'_'.$tahun.'_'.$bulan.'.'.$extension));

        // Delete the file
        File::delete(public_path('storage/spreadsheets/um/'.$new));

        // Redirect
        return redirect()->route('admin.uang-makan.monitoring', ['bulan' => $bulanAngka, 'tahun' => $tahun])->with(['message' => 'Berhasil memproses data.']);
    }

    // public function kdanak_to_unit($kdanak) {
    //     if($kdanak == "00") $anak = 6;
    //     elseif($kdanak == "01") $anak = 26;
    //     elseif($kdanak == "02") $anak = 10;
    //     elseif($kdanak == "03") $anak = 9;
    //     elseif($kdanak == "04") $anak = 7;
    //     elseif($kdanak == "05") $anak = 0;
    //     elseif($kdanak == "06") $anak = 11;
    //     elseif($kdanak == "07") $anak = 4;
    //     elseif($kdanak == "08") $anak = 4;
    //     elseif($kdanak == "09") $anak = 4;
    //     elseif($kdanak == "10") $anak = 1;
    //     elseif($kdanak == "11") $anak = 2;
    //     elseif($kdanak == "12") $anak = 12;
    //     else $anak = 0;

    //     return $anak;
    // }
}
