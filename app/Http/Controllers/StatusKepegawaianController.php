<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\StatusKepegawaian;

class StatusKepegawaianController extends Controller
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

        // Get status kepegawaian
        $status_kepegawaian = StatusKepegawaian::orderBy('grup_id','asc')->orderBy('persentase','desc')->get();
		
		// View
		return view('admin/status-kepegawaian/index', [
			'status_kepegawaian' => $status_kepegawaian
		]);
    }
}
