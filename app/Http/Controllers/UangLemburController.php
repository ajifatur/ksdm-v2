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
use App\Imports\UangLemburImport;
use App\Models\UangLembur;
use App\Models\AnakSatker;
use App\Models\Golru;
use App\Models\Pegawai;
use App\Models\PegawaiNonAktif;

class UangLemburController extends Controller
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

        // Get anak satker
        $as = AnakSatker::find($id);

        // Get anak satker
        $anak_satker = AnakSatker::all();

        // Get uang lembur
        $uang_lembur = [];
        if($id != 0)
            $uang_lembur = UangLembur::whereHas('anak_satker', function(Builder $query) use ($as) {
                return $query->where('id','=',$as->id);
            })->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();

        // View
        return view('admin/uang-lembur/index', [
            'anak_satker' => $anak_satker,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'uang_lembur' => $uang_lembur,
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
            'jumlah' => 0,
            'kotor' => 0,
            'bersih' => 0,
        ];
        foreach($anak_satker as $a) {
            $uang_lembur = UangLembur::whereHas('anak_satker', function(Builder $query) use ($a) {
                return $query->where('id','=',$a->id);
            })->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();

            // Set angka
            $jumlah = $uang_lembur->count();
            $kotor = $uang_lembur->sum('kotor');
            $bersih = $uang_lembur->sum('bersih');

            // Push data
            array_push($data, [
                'anak_satker' => $a,
                'jumlah' => $jumlah,
                'kotor' => $kotor,
                'bersih' => $bersih,
            ]);

            // Count total
            $total['jumlah'] += $jumlah;
            $total['kotor'] += $kotor;
            $total['bersih'] += $bersih;
        }

        // View
        return view('admin/uang-lembur/monitoring', [
            'anak_satker' => $anak_satker,
            'bulan' => $bulan,
            'tahun' => $tahun,
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
        $ruang = ['a','b','c','d','e'];

        if($request->method() == 'GET') {
            // Get anak satker
            $anak_satker = AnakSatker::where('jenis','=',1)->where('nama','!=','Bantuan Pangan')->get();

            // View
            return view('admin/uang-lembur/import', [
                'anak_satker' => $anak_satker,
            ]);
        }
        elseif($request->method() == 'POST') {
            ini_set("memory_limit", "-1");
            ini_set("max_execution_time", "-1");
            
            // Make directory if not exists
            if(!File::exists(public_path('storage/spreadsheets/ul')))
                File::makeDirectory(public_path('storage/spreadsheets/ul'));

            // Get the file
            $file = $request->file('file');
            $filename = FileExt::info($file->getClientOriginalName())['nameWithoutExtension'];
            $extension = FileExt::info($file->getClientOriginalName())['extension'];
            $new = date('Y-m-d-H-i-s').'_'.$filename.'.'.$extension;

            // Move the file
            $file->move(public_path('storage/spreadsheets/ul'), $new);

            // Get array
            $array = Excel::toArray(new UangLemburImport, public_path('storage/spreadsheets/ul/'.$new));

            $anak_satker = '';
            $bulan = '';
            $bulanAngka = '';
            $tahun = '';
            if(count($array)>0) {
                foreach($array[0] as $key=>$data) {
                    if($data[1] != null) {
                        // Get pegawai
                        $pegawai = Pegawai::where('nip','=',$data[5])->first();

                        // Get gaji bulan berjalan
                        $gaji = $pegawai->gaji()->where('bulan','=',$data[1])->where('tahun','=',$data[2])->first();

                        // Get golru
                        $golru = Golru::where('golongan_id','=',substr($data[7],0,1))->where('ruang','=',$ruang[substr($data[7],1,1)-1])->first();

                        // Get uang lembur
                        $uang_lembur = UangLembur::where('pegawai_id','=',$pegawai->id)->where('bulan','=',$data[1])->where('tahun','=',$data[2])->first();
                        if(!$uang_lembur) $uang_lembur = new UangLembur;

                        // Simpan uang lembur
                        $uang_lembur->pegawai_id = $pegawai->id;
                        $uang_lembur->golru_id = $golru->id;
                        $uang_lembur->unit_id = $gaji->unit_id;
                        $uang_lembur->anak_satker_id = $gaji->anak_satker_id;
                        $uang_lembur->jenis = $pegawai->jenis;
                        $uang_lembur->bulan = $data[1];
                        $uang_lembur->tahun = $data[2];
                        $uang_lembur->tarif_um = $golru->golongan->tarif_um;
                        $uang_lembur->tarif_lembur = $golru->golongan->tarif_lembur;
                        $uang_lembur->jamlemburharikerja = $data[14];
                        $uang_lembur->jamlemburharilibur = $data[15];
                        $uang_lembur->totallembur = $uang_lembur->tarif_lembur * ($uang_lembur->jamlemburharikerja + (2 * $uang_lembur->jamlemburharilibur));
                        $uang_lembur->totalumlembur = $data[16] - $uang_lembur->totallembur;
                        $uang_lembur->totalhari = $uang_lembur->totalumlembur / $uang_lembur->tarif_um;
                        $uang_lembur->kotor = $data[16];
                        $uang_lembur->potongan = $data[17];
                        $uang_lembur->bersih = $data[18];
                        $uang_lembur->save();

                        // Get anak satker, bulan, tahun
                        if($key == 0) {
                            $a = AnakSatker::find($gaji->anak_satker_id);
                            $anak_satker = $a->nama;
                            $bulan = DateTimeExt::month((int)$data[1]);
                            $bulanAngka = (int)$data[1];
                            $tahun = $data[2];
                        }
                    }
                }
            }

            // Rename the file
            File::move(public_path('storage/spreadsheets/ul/'.$new), public_path('storage/spreadsheets/ul/'.$anak_satker.'_'.$tahun.'_'.$bulan.'.'.$extension));

            // Delete the file
            File::delete(public_path('storage/spreadsheets/ul/'.$new));

            // Redirect
            return redirect()->route('admin.uang-lembur.monitoring', ['bulan' => $bulanAngka, 'tahun' => $tahun])->with(['message' => 'Berhasil memproses data.']);
        }
    }
}
