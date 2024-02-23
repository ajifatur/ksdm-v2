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
use App\Imports\ByStartRowImport;
use App\Models\UangMakanNonASN;
use App\Models\Pegawai;
use App\Models\StatusKepegawaian;
use App\Models\Unit;

class UangMakanNonASNController extends Controller
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
        $id = $request->query('unit') ?: 0;
        $jenis = $request->query('jenis') ?: 1;

        // Get status kepegawaian
        if($jenis == 1)
            $status_kepegawaian = StatusKepegawaian::whereIn('nama', ['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN','Non PNS'])->pluck('id')->toArray();
        elseif($jenis == 2)
            $status_kepegawaian = StatusKepegawaian::whereIn('nama', ['KONTRAK'])->pluck('id')->toArray();

        // Get excluded unit
        $excluded_unit = Unit::whereIn('nama',['DAKK','DPK','DSIH','DUSDM','KPM','KPP','Kantor Hukum','KUI','SPI'])->pluck('id')->toArray();

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->whereNotIn('id',$excluded_unit)->where('nama','!=','-')->orderBy('num_order','asc')->get();

        // Get uang makan
        $uang_makan = [];
        if($id != 0)
            $uang_makan = UangMakanNonASN::where('unit_id','=',$id)->whereIn('status_kepeg_id',$status_kepegawaian)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->get();

        // View
        return view('admin/uang-makan-non-asn/index', [
            'unit' => $unit,
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
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
        $jenis = $request->query('jenis') ?: 1;

        // Get excluded unit
        $excluded_unit = Unit::whereIn('nama',['DAKK','DPK','DSIH','DUSDM','KPM','KPP','Kantor Hukum','KUI','SPI'])->pluck('id')->toArray();

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->whereNotIn('id',$excluded_unit)->where('nama','!=','-')->orderBy('num_order','asc')->get();

        $data = [];
        $total = [
            'dosen_jumlah' => 0,
            'dosen_nominal' => 0,
            'tendik_jumlah' => 0,
            'tendik_nominal' => 0,
        ];
        foreach($unit as $u) {
            // Get status kepegawaian
            if($jenis == 1)
                $status_kepegawaian = StatusKepegawaian::whereIn('nama', ['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN','Non PNS'])->pluck('id')->toArray();
            elseif($jenis == 2)
                $status_kepegawaian = StatusKepegawaian::whereIn('nama', ['KONTRAK'])->pluck('id')->toArray();

            // Get uang makan
            $uang_makan = UangMakanNonASN::where('unit_id','=',$u->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->get();

            // Set angka
            $dosen_jumlah = $uang_makan->whereIn('status_kepeg_id', $status_kepegawaian)->where('jenis','=',1)->count();
            $dosen_nominal = $uang_makan->whereIn('status_kepeg_id', $status_kepegawaian)->where('jenis','=',1)->sum('nominal');
            $tendik_jumlah = $uang_makan->whereIn('status_kepeg_id', $status_kepegawaian)->where('jenis','=',2)->count();
            $tendik_nominal = $uang_makan->whereIn('status_kepeg_id', $status_kepegawaian)->where('jenis','=',2)->sum('nominal');

            // Push data
            array_push($data, [
                'unit' => $u,
                'dosen_jumlah' => $dosen_jumlah,
                'dosen_nominal' => $dosen_nominal,
                'tendik_jumlah' => $tendik_jumlah,
                'tendik_nominal' => $tendik_nominal,
            ]);

            // Count total
            $total['dosen_jumlah'] += $dosen_jumlah;
            $total['dosen_nominal'] += $dosen_nominal;
            $total['tendik_jumlah'] += $tendik_jumlah;
            $total['tendik_nominal'] += $tendik_nominal;
        }

        // View
        return view('admin/uang-makan-non-asn/monitoring', [
            'unit' => $unit,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
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
     * Import
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        if($request->method() == 'GET') {
            // Set tanggal, jenis
            $tanggal = date('Y-m-d');
            $jenis = $request->query('jenis') ?: 1;

            // Get excluded unit
            $excluded_unit = Unit::whereIn('nama',['DAKK','DPK','DSIH','DUSDM','KPM','KPP','Kantor Hukum','KUI','SPI'])->pluck('id')->toArray();

            // Get unit
            $unit = Unit::where(function($query) use ($tanggal) {
                $query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
            })->where(function($query) use ($tanggal) {
                $query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
            })->whereNotIn('id',$excluded_unit)->where('nama','!=','-')->orderBy('num_order','asc')->get();

            // View
            return view('admin/uang-makan-non-asn/import', [
                'jenis' => $jenis,
                'unit' => $unit
            ]);
        }
        elseif($request->method() == 'POST') {
            ini_set("memory_limit", "-1");
            ini_set("max_execution_time", "-1");
            
            // Make directory if not exists
            if(!File::exists(public_path('storage/spreadsheets/um-non-asn')))
                File::makeDirectory(public_path('storage/spreadsheets/um-non-asn'));

            // Get the file
            $file = $request->file('file');
            $filename = FileExt::info($file->getClientOriginalName())['nameWithoutExtension'];
            $extension = FileExt::info($file->getClientOriginalName())['extension'];
            $new = date('Y-m-d-H-i-s').'_'.$filename.'.'.$extension;

            // Move the file
            $file->move(public_path('storage/spreadsheets/um-non-asn'), $new);

            // Get array
            $unit = null;
            $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/spreadsheets/um-non-asn/'.$new));
            if(count($array)>0) {
                foreach($array[0] as $key=>$data) {
                    if($data[0] != null) {
                        // Get pegawai
                        $pegawai = Pegawai::where('nip','=',$data[0])->orWhere('npu','=',$data[0])->first();
    
                        if($pegawai) {
                            // Simpan uang makan
                            $uang_makan = UangMakanNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->first();
                            if(!$uang_makan) $uang_makan = new UangMakanNonASN;
                            $uang_makan->pegawai_id = $pegawai->id;
                            $uang_makan->unit_id = $request->unit;
                            $uang_makan->status_kepeg_id = $pegawai->status_kepeg_id;
                            $uang_makan->jenis = $request->kategori;
                            $uang_makan->bulan = $request->bulan;
                            $uang_makan->tahun = $request->tahun;
                            $uang_makan->nip = $data[0];
                            $uang_makan->nama = $data[1];
                            $uang_makan->nominal = $data[3];
                            $uang_makan->save();
                        }

                        // Get unit
                        if($key == 0) {
                            $unit = Unit::find($request->unit);
                        }
                    }
                }
            }

            // Rename the file
            File::move(public_path('storage/spreadsheets/um-non-asn/'.$new), public_path('storage/spreadsheets/um-non-asn/'.$unit->nama.'_'.$request->tahun.'_'.$request->bulan.'.'.$extension));

            // Delete the file
            File::delete(public_path('storage/spreadsheets/um-non-asn/'.$new));

            // Redirect
            return redirect()->route('admin.uang-makan-non-asn.monitoring', ['bulan' => $request->bulan, 'tahun' => $request->tahun, 'jenis' => $request->jenis])->with(['message' => 'Berhasil memproses data.']);
        }
    }
}
