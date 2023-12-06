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
            'dosen_kotor' => 0,
            'dosen_bersih' => 0,
            'tendik_jumlah' => 0,
            'tendik_kotor' => 0,
            'tendik_bersih' => 0,
        ];
        foreach($anak_satker as $a) {
            $uang_makan = UangMakan::where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->where('kdanak','=',$a->kode)->get();

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

        // Get uang makan
        $uang_makan = [];
        for($i=1; $i<=12; $i++) {
            array_push($uang_makan, [
                'bulan' => DateTimeExt::month($i),
                'pegawai' => UangMakan::where('bulan','=',($i < 10 ? '0'.$i : $i))->where('tahun','=',$tahun)->count(),
                'nominal_kotor' => UangMakan::where('bulan','=',($i < 10 ? '0'.$i : $i))->where('tahun','=',$tahun)->sum('kotor'),
                'nominal_bersih' => UangMakan::where('bulan','=',($i < 10 ? '0'.$i : $i))->where('tahun','=',$tahun)->sum('bersih'),
            ]);
        }

        // Total
        $total_pegawai = UangMakan::where('tahun','=',$tahun)->count();
        $total_nominal_kotor = UangMakan::where('tahun','=',$tahun)->sum('kotor');
        $total_nominal_bersih = UangMakan::where('tahun','=',$tahun)->sum('bersih');

        // View
        return view('admin/uang-makan/recap', [
            'tahun' => $tahun,
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
        elseif(!$anak_satker) {
            if(in_array($request->kategori, [1,2])) {
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
            else {
                // Get uang makan
                $uang_makan = UangMakan::where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->get();
    
                if(count($uang_makan) <= 0) {
                    echo "Tidak ada data!";
                    return;
                }
    
                // Return
                return Excel::download(new UangMakanExport([
                    'uang_makan' => $uang_makan,
                ]), 'Uang-Makan '.$tahun.' '.\Ajifatur\Helpers\DateTimeExt::month($bulan).'.xlsx');
            }
        }
    }
    
    /**
     * Import
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        if($request->method() == 'GET') {
            // Get anak satker
            $anak_satker = AnakSatker::where('nama','!=','Bantuan Pangan')->get();

            // View
            return view('admin/uang-makan/import', [
                'anak_satker' => $anak_satker,
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
    }
    
    /**
     * Import (Format Lama)
     *
     * @return \Illuminate\Http\Response
     */
    public function importOld(Request $request)
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

                        // Get uang makan
                        $uang_makan = UangMakan::where('kdanak','=',$request->anak_satker)->where('bulan','=',($request->bulan < 10 ? '0'.$request->bulan : $request->bulan))->where('tahun','=',$request->tahun)->where('nip','=',$data[3])->first();
                        if(!$uang_makan) $uang_makan = new UangMakan;

                        // Simpan uang makan
                        $uang_makan->pegawai_id = $pegawai ? $pegawai->id : 0;
                        $uang_makan->unit_id = $this->kdanak_to_unit($request->anak_satker);
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
