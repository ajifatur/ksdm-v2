<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\AnakSatker;

class AnakSatkerController extends Controller
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

        // Get anak satker
        $anak_satker = AnakSatker::orderBy('kode','asc')->get();
		
		// View
		return view('admin/anak-satker/index', [
			'anak_satker' => $anak_satker
		]);
    }
}
