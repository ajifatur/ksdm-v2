<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Models\MutasiSerdos;
use App\Models\JenisMutasiSerdos;
use App\Models\Angkatan;
use App\Models\Pegawai;
use App\Models\TunjanganProfesi;

class MutasiSerdosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		// Get mutasi
		$mutasi_serdos = MutasiSerdos::has('pegawai')->orderBy('tmt','desc')->get();

        // View
        return view('admin/tunjangan-profesi/mutasi/index', [
            'mutasi_serdos' => $mutasi_serdos
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get pegawai sudah mendapat tunjangan profesi
        $pegawai_aktif = TunjanganProfesi::groupBy('pegawai_id')->pluck('pegawai_id')->toArray();

        // Get pegawai
        $pegawai = Pegawai::where('jenis','=',1)->whereHas('status_kerja', function (Builder $query) {
            return $query->where('status','=',1);
        })->whereIn('status_kepeg_id',[1,2])->whereNotIn('id',$pegawai_aktif)->get();

        // Get angkatan
        $angkatan = [];
        for($i=1; $i<=3; $i++) {
            $angkatan[$i]['data'] = Angkatan::where('jenis_id','=',$i)->orderBy('nama','asc')->get();
        }
		
		// Get jenis
		$jenis = JenisMutasiSerdos::all();

        // View
        return view('admin/tunjangan-profesi/mutasi/create', [
            'pegawai' => $pegawai,
            'angkatan' => $angkatan,
            'jenis' => $jenis,
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
            'gaji_pokok' => 'required',
            'nama_supplier' => 'required',
            'nomor_rekening' => 'required',
            'nama_rekening' => 'required',
            'angkatan' => 'required',
            'jenis' => 'required',
            'tmt' => 'required',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Simpan mutasi
            $mutasi_serdos = new MutasiSerdos;
            $mutasi_serdos->pegawai_id = $request->pegawai;
            $mutasi_serdos->jenis_id = $request->jenis;
            $mutasi_serdos->angkatan_id = $request->angkatan;
            $mutasi_serdos->unit_id = $request->unit_id;
            $mutasi_serdos->gaji_pokok_id = $request->gaji_pokok;
            $mutasi_serdos->nama_supplier = $request->nama_supplier;
            $mutasi_serdos->nomor_rekening = $request->nomor_rekening;
            $mutasi_serdos->nama_rekening = $request->nama_rekening;
            $mutasi_serdos->tmt = DateTimeExt::change($request->tmt);
            $mutasi_serdos->bulan = 0;
            $mutasi_serdos->tahun = 0;
            $mutasi_serdos->save();

            // Redirect
            return redirect()->route('admin.tunjangan-profesi.mutasi.index')->with(['message' => 'Berhasil menambah data.']);
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
		// Get mutasi serdos
		$mutasi_serdos = MutasiSerdos::has('pegawai')->findOrFail($id);

        // Get angkatan
        $angkatan = [];
        for($i=1; $i<=3; $i++) {
            $angkatan[$i]['data'] = Angkatan::where('jenis_id','=',$i)->orderBy('nama','asc')->get();
        }
		
		// Get jenis
		$jenis = JenisMutasiSerdos::all();

        // View
        return view('admin/tunjangan-profesi/mutasi/edit', [
            'mutasi_serdos' => $mutasi_serdos,
            'angkatan' => $angkatan,
            'jenis' => $jenis,
        ]);
    }

    /**
     * Update.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required',
            'nomor_rekening' => 'required',
            'nama_rekening' => 'required',
            'angkatan' => 'required',
            'jenis' => 'required',
            'tmt' => 'required',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Simpan mutasi
            $mutasi_serdos = MutasiSerdos::find($request->id);
            $mutasi_serdos->jenis_id = $request->jenis;
            $mutasi_serdos->angkatan_id = $request->angkatan;
            $mutasi_serdos->nama_supplier = $request->nama_supplier;
            $mutasi_serdos->nomor_rekening = $request->nomor_rekening;
            $mutasi_serdos->nama_rekening = $request->nama_rekening;
            $mutasi_serdos->tmt = DateTimeExt::change($request->tmt);
            $mutasi_serdos->save();

            // Redirect
            return redirect()->route('admin.tunjangan-profesi.mutasi.index')->with(['message' => 'Berhasil mengupdate data.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // Get mutasi serdos
		$mutasi_serdos = MutasiSerdos::findOrFail($request->id);
        $mutasi_serdos->delete();

        // Redirect
        return redirect()->route('admin.tunjangan-profesi.mutasi.index')->with(['message' => 'Berhasil menghapus data.']);
    }
}