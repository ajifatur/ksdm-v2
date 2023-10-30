<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Models\Pegawai;
use App\Models\SLKS;
use App\Models\SLKSDetail;
use App\Models\Blacklist;

class SLKSController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get Satyalancana
        $slks = SLKS::orderBy('periode','desc')->get();

        // Get pegawai
        $pegawai = Pegawai::where('status_kepeg_id','=',1)->orderBy('nip','asc')->get();

        // View
        return view('admin/slks/index', [
            'slks' => $slks,
            'pegawai' => $pegawai,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Get pegawai
        $pegawai = Pegawai::where('status_kepeg_id','=',1)->orderBy('nip','asc')->get();

        // View
        return view('admin/slks/create', [
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
            'no_keppres' => 'required',
            'tanggal_keppres' => 'required',
            'no_keprektor' => 'required',
            'tanggal_keprektor' => 'required',
            'periode' => 'required',
        ]);
        
        // Check errors
        if($validator->fails()) {
            // Back to form page with validation error messages
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        else {
            // Simpan satyalancana
            $slks = new SLKS;
            $slks->no_keppres = $request->no_keppres;
            $slks->tanggal_keppres = DateTimeExt::change($request->tanggal_keppres);
            $slks->no_keprektor = $request->no_keprektor;
            $slks->tanggal_keprektor = DateTimeExt::change($request->tanggal_keprektor);
            $slks->periode = $request->periode;
            $slks->save();

            // Redirect
            return redirect()->route('admin.slks.index')->with(['message' => 'Berhasil menambah data.']);
        }
    }

    /**
     * Add pegawai.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        // Get satyalancana
        $slks = SLKS::find($request->id);

        // Simpan detail
        if(count($request->get('pegawai')) > 0) {
            foreach($request->get('pegawai') as $p) {
                $slks_detail = new SLKSDetail;
                $slks_detail->slks_id = $slks->id;
                $slks_detail->pegawai_id = $p;
                $slks_detail->tahun = $request->tahun;
                $slks_detail->save();
            }
        }

        // Redirect
        return redirect()->route('admin.slks.index')->with(['message' => 'Berhasil menambah pegawai.']);
    }

    /**
     * Nominasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function nomination(Request $request)
    {		
		// Set TMT minimal
		$tmt_minimal = date('Y-m-d', strtotime("-11 years", strtotime('2024-08-01')));
		
        // Get pegawai blacklist
        $blacklist = Blacklist::pluck('pegawai_id')->toArray();

        // Get pegawai
        $pegawai = Pegawai::where('status_kepeg_id','=',1)->whereHas('status_kerja', function (Builder $query) {
            return $query->where('status','=',1);
        })->whereNotIn('id',$blacklist)->where('tmt_cpns','<=',$tmt_minimal)->orderBy('nip','asc')->get();

        foreach($pegawai as $key=>$p) {
            // Set MK pada Mei 2024
            $pegawai[$key]->mk_mei = DateTimeExt::diff($p->tmt_cpns, '2024-05-01');

            // Set MK pada Agustus 2024
            $pegawai[$key]->mk_agustus = DateTimeExt::diff($p->tmt_cpns, '2024-08-01');

            // Cek apakah sudah menerima satyalancana
            $pegawai[$key]->sudah_xxx = SLKSDetail::has('slks')->where('pegawai_id','=',$p->id)->where('tahun','=','XXX')->count() > 0 ? true : false;
            $pegawai[$key]->sudah_xx = SLKSDetail::has('slks')->where('pegawai_id','=',$p->id)->where('tahun','=','XX')->count() > 0 ? true : false;
            $pegawai[$key]->sudah_x = SLKSDetail::has('slks')->where('pegawai_id','=',$p->id)->where('tahun','=','X')->count() > 0 ? true : false;

            // Set rekomendasi Mei 2024
            $rekomendasi_mei = '';
            if($pegawai[$key]->mk_mei >= 30 && $pegawai[$key]->sudah_xxx == false)
                $rekomendasi_mei = 'Diusulkan XXX';
            elseif($pegawai[$key]->mk_mei > 20 && $pegawai[$key]->mk_mei < 30 && $pegawai[$key]->sudah_xx == false)
                $rekomendasi_mei = 'Diusulkan XX';
            elseif($pegawai[$key]->mk_mei > 10 && $pegawai[$key]->mk_mei <= 20 && $pegawai[$key]->sudah_x == false)
                $rekomendasi_mei = 'Diusulkan X';
            $pegawai[$key]->rekomendasi_mei = $rekomendasi_mei;

            // Set rekomendasi Agustus 2024
            $rekomendasi_agustus = '';
            if($pegawai[$key]->mk_agustus >= 30 && $pegawai[$key]->sudah_xxx == false)
                $rekomendasi_agustus = 'Diusulkan XXX';
            elseif($pegawai[$key]->mk_agustus > 20 && $pegawai[$key]->mk_agustus < 30 && $pegawai[$key]->sudah_xx == false)
                $rekomendasi_agustus = 'Diusulkan XX';
            elseif($pegawai[$key]->mk_agustus > 10 && $pegawai[$key]->mk_agustus <= 20 && $pegawai[$key]->sudah_x == false)
                $rekomendasi_agustus = 'Diusulkan X';
            $pegawai[$key]->rekomendasi_agustus = $rekomendasi_agustus;
        }

        // View
        return view('admin/slks/nomination', [
            'pegawai' => $pegawai
        ]);
    }

    /**
     * Blacklist.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function blacklist(Request $request)
    {
        // Get blacklist
        $blacklist = Blacklist::has('pegawai')->orderBy('keterangan','desc')->get();

        // View
        return view('admin/slks/blacklist', [
            'blacklist' => $blacklist
        ]);
    }
}