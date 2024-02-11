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
use App\Imports\PegawaiImport;
use App\Models\Pegawai;
use App\Models\PegawaiNonAktif;
use App\Models\GajiPokok;
use App\Models\Mutasi;
use App\Models\RemunGaji;
use App\Models\LebihKurang;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pegawai = Pegawai::orderBy('nip','asc')->get();

        // View
        return view('admin/pegawai/index', [
            'pegawai' => $pegawai
        ]);
    }

    /**
     * Active employees.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function active(Request $request)
    {
        // Get pegawai
        $pegawai = Pegawai::whereHas('status_kerja', function(Builder $query) {
            return $query->where('status','=',1);
        })->orderBy('nip','asc')->get();

        foreach($pegawai as $key=>$p) {
            // Get mutasi jabatan
			$mutasi = $p->mutasi()->first();

            // Get jabatan struktural
            $pegawai[$key]->unit_jabstruk = $mutasi ? $mutasi->detail()->whereHas('jabatan', function (Builder $query) {
                return $query->where('jenis_id','=',2);
            })->first() : null;

            // Get masa kerja
            $pegawai[$key]->masa_kerja = $mutasi ? $mutasi->gaji_pokok : null;
        }

        // View
        return view('admin/pegawai/active', [
            'pegawai' => $pegawai
        ]);
    }

    /**
     * Inactive employees.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function inactive(Request $request)
    {
        $pegawai = Pegawai::whereHas('status_kerja', function(Builder $query) {
            return $query->where('status','=',0);
        })->orderBy('tmt_non_aktif','desc')->get();

        // View
        return view('admin/pegawai/inactive', [
            'pegawai' => $pegawai
        ]);
    }

    /**
     * Search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        // Set keyword
        if($request->query('q') != null)
            session()->put('keyword', $request->query('q'));

        // Get pegawai by keyword
        $pegawai = [];
        if(session('keyword') != null && session('keyword') != '')
            $pegawai = Pegawai::where('nama','like','%'.session('keyword').'%')->orWhere('nip','like','%'.session('keyword').'%')->orderBy('nip','asc')->get();

        // View
        return view('admin/pegawai/search', [
            'pegawai' => $pegawai
        ]);
    }

    /**
     * Show the detail of the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request, $id)
    {
        // Get pegawai
        $pegawai = Pegawai::findOrFail($id);

        // Set jabatan, unit, golru, MKG
        $jabatan = '-';
        $unit = '-';
        $golru = '-';
        $mkg = '-';
        if($pegawai->status_kerja->status == 1) {
            // Get mutasi pegawai
            $mutasi = $pegawai->mutasi()->whereHas('jenis', function(Builder $query) {
                return $query->where('status','=',1);
            })->first();

            // Get jabatan dan unit
            $jabatan = [];
            $unit = [];
            if($mutasi) {
                foreach($mutasi->detail as $d) {
                    if($d->jabatan && !in_array($d->jabatan->nama, $jabatan)) {
                        if($d->jabatan->grup->nama == 'Koordinator Program Studi')
                            array_push($jabatan, 'Koordinator Program Studi');
                        else
                            array_push($jabatan, $d->jabatan->nama);
                    }
                    if($d->unit && !in_array($d->unit->nama, $unit)) array_push($unit, $d->unit->nama);
                }
            }
            $jabatan = implode(' / ', $jabatan);
            $unit = implode(' / ', $unit);

            // Get golru dan MKG
            $golru = ($mutasi && $mutasi->golru) ? $mutasi->golru->nama : '-';
            $mkg = ($mutasi && $mutasi->gaji_pokok) ? $mutasi->gaji_pokok->nama : '-';
        }

        // Get remun gaji
        $remun_gaji = [];
        $remun_gaji_total['terbayar'] = 0;
        $remun_gaji_total['seharusnya'] = 0;
        $remun_gaji_total['selisih'] = 0;
        $remun_gaji_total['dibayarkan'] = 0;
        $remun_gaji_expand = false;
        foreach($pegawai->remun_gaji as $r) {
            // Get kekurangan
            $kekurangan = LebihKurang::where('pegawai_id','=',$pegawai->id)->where('bulan_proses','=',$r->bulan)->where('tahun_proses','=',$r->tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();

            if(count($kekurangan) > 0) {
                // Count
                $remun_gaji_total['terbayar'] += $kekurangan->sum('terbayar');
                $remun_gaji_total['seharusnya']  += $kekurangan->sum('seharusnya');
                $remun_gaji_total['selisih']  += $kekurangan->sum('selisih');
                $remun_gaji_total['dibayarkan'] += ($kekurangan->sum('selisih') > 0 ? $kekurangan->sum('selisih') : 0);

                // Push remun gaji
                array_push($remun_gaji, [
                    'kekurangan' => true,
                    'bulan' => $r->bulan,
                    'nama_bulan' => DateTimeExt::month($r->bulan),
                    'tahun' => $r->tahun,
                    'remun_gaji' => $r,
                    'lebih_kurang' => $kekurangan,
                    'dibayarkan' => ($kekurangan->sum('selisih') > 0 ? $kekurangan->sum('selisih') : 0)
                ]);

                // Expand
                $remun_gaji_expand = true;
            }

            // Get lebih kurang
            $lebih_kurang = $pegawai->lebih_kurang()->where('bulan_proses','=',$r->bulan)->where('tahun_proses','=',$r->tahun)->where('triwulan_proses','=',0)->where('selisih','!=',0)->where('kekurangan','=',0)->get();
            $dibayarkan = $r->remun_gaji + $lebih_kurang->sum('selisih');

            // Count
            $remun_gaji_total['terbayar'] += $lebih_kurang->sum('terbayar');
            $remun_gaji_total['seharusnya']  += $lebih_kurang->sum('seharusnya');
            $remun_gaji_total['selisih']  += $lebih_kurang->sum('selisih');
            $remun_gaji_total['dibayarkan'] += $dibayarkan;

            // Push remun gaji
            array_push($remun_gaji, [
                'kekurangan' => false,
                'bulan' => $r->bulan,
                'nama_bulan' => DateTimeExt::month($r->bulan),
                'tahun' => $r->tahun,
                'remun_gaji' => $r,
                'lebih_kurang' => $lebih_kurang,
                'dibayarkan' => $dibayarkan
            ]);

            // Expand
            if(count($lebih_kurang) > 0)
                $remun_gaji_expand = true;
        }

        // View
        return view('admin/pegawai/detail', [
            'pegawai' => $pegawai,
            'jabatan' => $jabatan,
            'unit' => $unit,
            'golru' => $golru,
            'mkg' => $mkg,
            'remun_gaji' => $remun_gaji,
            'remun_gaji_total' => $remun_gaji_total,
            'remun_gaji_expand' => $remun_gaji_expand,
        ]);
    }

    /**
     * Edit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Get pegawai
        $pegawai = Pegawai::findOrFail($id);

        // View
        return view('admin/pegawai/edit', [
            'pegawai' => $pegawai,
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
            'nama' => 'required'
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Update pegawai
            $pegawai = Pegawai::find($request->id);
            $pegawai->nama = $request->nama;
            $pegawai->gelar_depan = $request->gelar_depan;
            $pegawai->gelar_belakang = $request->gelar_belakang;
            $pegawai->nama_supplier = $request->nama_supplier;
            $pegawai->nama_btn = $request->nama_btn;
            $pegawai->norek_btn = $request->norek_btn;
            $pegawai->save();

            // Redirect
			return redirect()->route('admin.pegawai.detail', ['id' => $pegawai->id])->with(['message' => 'Berhasil mengupdate profil pegawai.']);
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

		$array = Excel::toArray(new PegawaiImport, public_path('storage/Konversi NPU.xlsx'));

        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[1] != null) {
                    $pegawai = Pegawai::where('nip','=',$data[0])->first();
                    // Pegawai BLU
                    if($pegawai) {
                        $pegawai->npu = $data[1];
                        $pegawai->save();
                    }
                    // Pegawai Kontrak PPK
                    else {
                        $pegawai = new Pegawai;
                        $pegawai->status_kepeg_id = 5; // Kontrak
                        $pegawai->status_kerja_id = 1; // Aktif
                        $pegawai->golongan_id = 0;
                        $pegawai->golru_id = null;
                        $pegawai->jabfung_id = 0;
                        $pegawai->jabstruk_id = 0;
                        $pegawai->unit_id = 0;
                        $pegawai->jenis = 2; // Tendik
                        $pegawai->nip = $data[1];
                        $pegawai->npu = $data[1];
                        $pegawai->nama = $data[2];
                        $pegawai->gelar_depan = '';
                        $pegawai->gelar_belakang = '';
                        $pegawai->tanggal_lahir = substr($data[1],0,4).'-'.substr($data[1],4,2).'-'.substr($data[1],6,2);
                        $pegawai->tempat_lahir = '';
                        $pegawai->tmt_cpns = null;
                        $pegawai->tmt_golongan = null;
                        $pegawai->tmt_non_aktif = null;
                        $pegawai->nama_supplier = null;
                        $pegawai->nama_btn = null;
                        $pegawai->norek_btn = null;
                        $pegawai->save();
                    }
                }
            }
        }
        var_dump($error);
    }

    /**
     * Edit TMT Golongan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editTMTGolongan($id)
    {
        // Get pegawai
        $pegawai = Pegawai::findOrFail($id);

        // View
        return view('admin/pegawai/edit-tmt-golongan', [
            'pegawai' => $pegawai,
        ]);
	}

    /**
     * Update.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateTMTGolongan(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'tmt_golongan' => 'required',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Update pegawai
            $pegawai = Pegawai::find($request->id);
            $pegawai->tmt_golongan = DateTimeExt::change($request->tmt_golongan);
            $pegawai->save();

            // Redirect
            if(in_array($pegawai->status_kepegawaian->nama, ['CPNS','PNS']))
    			return redirect()->route('admin.pantauan.mkg', ['tipe' => 1])->with(['message' => 'Berhasil mengupdate TMT Golongan pegawai.']);
            elseif(in_array($pegawai->status_kepegawaian->nama, ['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN','Non PNS']))
                return redirect()->route('admin.pantauan.mkg', ['tipe' => 2])->with(['message' => 'Berhasil mengupdate TMT Golongan pegawai.']);
        }
    }
}
