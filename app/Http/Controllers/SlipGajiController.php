<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Models\SlipGaji;
use App\Models\Pegawai;
use App\Models\TTD;

class SlipGajiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get slip gaji
        $slip_gaji = SlipGaji::orderBy('tanggal','desc')->orderBy('tahun','desc')->orderBy('bulan','desc')->get();

        // View
        return view('admin/slip-gaji/index', [
            'slip_gaji' => $slip_gaji,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get pegawai
        $pegawai = Pegawai::whereHas('status_kerja', function (Builder $query) {
            return $query->where('status','=',1);
        })->whereHas('status_kepegawaian', function (Builder $query) {
            return $query->whereIn('nama', ['PNS','CPNS','PPPK']);
        })->get();

        // View
        return view('admin/slip-gaji/create', [
            'pegawai' => $pegawai
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'pegawai' => 'required',
            'bulan' => 'required',
            'tahun' => 'required',
            'jabatan' => 'required',
            'additional_allowance' => 'required',
            'tanggal' => 'required',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Get pegawai
            $pegawai = Pegawai::findOrFail($request->pegawai);

            // Get gaji induk
            $gaji_induk = $pegawai->gaji()->where('jenis_id','=',1)->where('bulan','=',$request->bulan < 10 ? '0'.$request->bulan : $request->bulan)->where('tahun','=',$request->tahun)->first();

            // Get number of children
            $children = $gaji_induk ? $gaji_induk->tjanak / (($gaji_induk->gjpokok * 2) / 100) : 0;

            // Simpan slip gaji
            $slip_gaji = new SlipGaji;
            $slip_gaji->pegawai_id = $pegawai->id;
            $slip_gaji->golru_id = $pegawai->golru_id;
            $slip_gaji->bulan = $request->bulan;
            $slip_gaji->tahun = $request->tahun;
            $slip_gaji->jabatan = $request->jabatan;
            $slip_gaji->position = $request->position;
            $slip_gaji->children = $children;
            $slip_gaji->additional_allowance = str_replace(',','',$request->additional_allowance);
            $slip_gaji->tanggal = DateTimeExt::change($request->tanggal);
            $slip_gaji->save();

            // Redirect
            return redirect()->route('admin.slip-gaji.index')->with(['message' => 'Berhasil menambah data.']);
        }
    }

    /**
     * Edit.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Get slip gaji
        $slip_gaji = SlipGaji::findOrFail($id);

        // Get tanggal sebelum
        $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($slip_gaji->tahun.'-'.($slip_gaji->bulan < 10 ? '0'.$slip_gaji->bulan : $slip_gaji->bulan).'-01')));
			
		// Get remun gaji
		$remun_gaji = $slip_gaji->pegawai->remun_gaji()->where('tahun','=',$slip_gaji->tahun)->where('bulan','=',$slip_gaji->bulan)->first();

		// Get lebih kurang
		$lebih_kurang = $slip_gaji->pegawai->lebih_kurang()->where('tahun_proses','=',$slip_gaji->tahun)->where('bulan_proses','=',$slip_gaji->bulan)->sum('selisih');

        // Get uang makan
        $uang_makan = $slip_gaji->pegawai->uang_makan()->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->where('bulan','=',date('m', strtotime($tanggal_sebelum)))->first();

        // View
        return view('admin/slip-gaji/edit', [
            'slip_gaji' => $slip_gaji,
            'remun_gaji' => ($remun_gaji ? $remun_gaji->remun_gaji : 0) + $lebih_kurang,
            'uang_makan' => ($uang_makan ? $uang_makan->bersih : 0)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'bulan' => 'required',
            'tahun' => 'required',
            'jabatan' => 'required',
            'additional_allowance' => 'required',
            'tanggal' => 'required',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Get slip gaji
            $slip_gaji = SlipGaji::findOrFail($request->id);

            // Get pegawai
            $pegawai = Pegawai::findOrFail($slip_gaji->pegawai_id);

            // Get gaji induk
            $gaji_induk = $pegawai->gaji()->where('jenis_id','=',1)->where('bulan','=',$request->bulan < 10 ? '0'.$request->bulan : $request->bulan)->where('tahun','=',$request->tahun)->first();

            // Get number of children
            $children = $gaji_induk ? $gaji_induk->tjanak / (($gaji_induk->gjpokok * 2) / 100) : 0;

            // Simpan slip gaji
            $slip_gaji->bulan = $request->bulan;
            $slip_gaji->tahun = $request->tahun;
            $slip_gaji->jabatan = $request->jabatan;
            $slip_gaji->position = $request->position;
            $slip_gaji->children = $children;
            $slip_gaji->additional_allowance = str_replace(',','',$request->additional_allowance);
            $slip_gaji->tanggal = DateTimeExt::change($request->tanggal);
            $slip_gaji->save();

            // Redirect
            return redirect()->route('admin.slip-gaji.index')->with(['message' => 'Berhasil mengupdate data.']);
        }
    }

    /**
     * Delete.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // Delete slip gaji
        $slip_gaji = SlipGaji::findOrFail($request->id);
        $slip_gaji->delete();

        // Redirect
        return redirect()->route('admin.slip-gaji.index')->with(['message' => 'Berhasil menghapus data.']);
    }

    /**
     * Print PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function print(Request $request, $id)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get slip gaji
        $slip_gaji = SlipGaji::findOrFail($id);

        // Set bahasa
        $bahasa = $request->query('lang') == 'en' ? 'en' : 'id';

        // Set mata uang
        $mata_uang = $bahasa == 'en' ? 'IDR' : 'Rp';

        // Set bulan in English
        $month = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        // Get gaji induk
        $gaji_induk = $slip_gaji->pegawai->gaji()->where('jenis_id','=',1)->where('bulan','=',$slip_gaji->bulan < 10 ? '0'.$slip_gaji->bulan : $slip_gaji->bulan)->where('tahun','=',$slip_gaji->tahun)->first();

        // Get tunjangan profesi
        $tunjangan_profesi = $slip_gaji->pegawai->tunjangan_profesi()->whereHas('angkatan', function(Builder $query) {
            return $query->whereIn('jenis_id',[2,3,4]);
        })->where('bulan','=',$slip_gaji->bulan)->where('tahun','=',$slip_gaji->tahun)->first();

        // Get tunjangan kehormatan profesor
        $tunjangan_kehormatan_profesor = $slip_gaji->pegawai->tunjangan_profesi()->whereHas('angkatan', function(Builder $query) {
            return $query->whereIn('jenis_id',[1]);
        })->where('bulan','=',$slip_gaji->bulan)->where('tahun','=',$slip_gaji->tahun)->first();

        // Set gross earnings
        $gross_earnings = $gaji_induk->gjpokok + $gaji_induk->tjistri + $gaji_induk->tjanak + $gaji_induk->tjupns + $gaji_induk->tjstruk + $gaji_induk->tjfungs + $gaji_induk->pembul + $gaji_induk->tjberas + $gaji_induk->tjpph + ($tunjangan_profesi ? $tunjangan_profesi->diterimakan : 0) + ($tunjangan_kehormatan_profesor ? $tunjangan_kehormatan_profesor->diterimakan : 0) + $slip_gaji->additional_allowance;

        // Set salary cuts
        $salary_cuts = $gaji_induk->potpfk10 + $gaji_induk->bpjs + $gaji_induk->potpph;

        // Get bendahara pengeluaran
        $bendahara_pengeluaran = TTD::where('kode','=','bpeng')->where('tanggal_mulai','<=',$slip_gaji->tanggal)->where('tanggal_selesai','>=',$slip_gaji->tanggal)->first();

        // Set title
        $title = ($bahasa == 'en' ? 'Salary Slip' : 'Slip Gaji').'_'.$slip_gaji->pegawai->nip;

        // PDF
        $pdf = \PDF::loadView('admin/slip-gaji/print', [
            'slip_gaji' => $slip_gaji,
            'bahasa' => $bahasa,
            'bulan_english' => $month[date('n', strtotime($slip_gaji->tanggal)) - 1],
            'mata_uang' => $mata_uang,
            'month' => $month,
            'gaji_induk' => $gaji_induk,
            'tunjangan_profesi' => $tunjangan_profesi,
            'tunjangan_kehormatan_profesor' => $tunjangan_kehormatan_profesor,
            'gross_earnings' => $gross_earnings,
            'salary_cuts' => $salary_cuts,
            'bendahara_pengeluaran' => $bendahara_pengeluaran,
            'title' => $title,
        ]);
        $pdf->setPaper([0, 0 , 612, 935]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Get Additional.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function additional(Request $request)
    {
		if($request->ajax()) {
			// Get pegawai
			$pegawai = Pegawai::find($request->pegawai);
			if(!$pegawai)
				return response()->json([
					'message' => 'Empty data'
				]);

            // Get tanggal sebelum
            $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($request->tahun.'-'.($request->bulan < 10 ? '0'.$request->bulan : $request->bulan).'-01')));
			
			// Get remun gaji
			$remun_gaji = $pegawai->remun_gaji()->where('tahun','=',$request->tahun)->where('bulan','=',$request->bulan)->first();
			
			// Get lebih kurang
			$lebih_kurang = $pegawai->lebih_kurang()->where('tahun_proses','=',$request->tahun)->where('bulan_proses','=',$request->bulan)->sum('selisih');
			
			// Get uang makan
			$uang_makan = $pegawai->uang_makan()->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->where('bulan','=',date('m', strtotime($tanggal_sebelum)))->first();

            // Get total
            $total = ($remun_gaji ? $remun_gaji->remun_gaji : 0) + $lebih_kurang + ($uang_makan ? $uang_makan->bersih : 0);
		
			// Response
			return response()->json([
				'pegawai' => $pegawai,
				'remun_gaji' => number_format(($remun_gaji ? $remun_gaji->remun_gaji : 0) + $lebih_kurang,0,',',','),
				'uang_makan' => number_format(($uang_makan ? $uang_makan->bersih : 0),0,',',','),
				'total' => number_format($total,0,',',',')
			]);
		}
	}
}
