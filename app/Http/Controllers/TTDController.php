<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\TTD;

class TTDController extends Controller
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

        // Get TTD
        $ttd = TTD::orderBy('nama','asc')->orderBy('tanggal_selesai','desc')->get();

        foreach($ttd as $key=>$t) {
            if($t->tanggal_mulai <= date('Y-m-d') && $t->tanggal_selesai >= date('Y-m-d'))
                $ttd[$key]->status = 'Aktif';
            else
                $ttd[$key]->status = 'Tidak Aktif';
        }
		
		// View
		return view('admin/ttd/index', [
			'ttd' => $ttd
		]);
    }
}
