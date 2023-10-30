<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\FileExt;

class PresensiController extends Controller
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

        // Get files
        $files = collect(File::allFiles(public_path('assets/txt')))
            ->sortByDesc(function ($file) {
                return $file->getCTime();
        });

        // View
        return view('admin/presensi/index', [
            'files' => $files
        ]);
    }

    /**
     * Show the detail of the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);
        
        $contents = trim(file_get_contents(public_path('assets/txt/'.$request->query('file'))));
	    $contents = explode(PHP_EOL, $contents);
        $data = [];
        $nip = '';
        foreach($contents as $content) {
            $split = preg_split('/\s+/', trim($content));
            if($split[0] != $nip) {
                $nip = $split[0];
                $data[$nip] = [];
            }
            array_push($data[$split[0]], $split[1]);
        }

        // View
        return view('admin/presensi/detail', [
            'data' => $data,
        ]);
    }
    
    /**
     * Import
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
		ini_set("memory_limit", "-1");
        
        // Make directory if not exists
        if(!File::exists(public_path('assets/txt')))
            File::makeDirectory(public_path('assets/txt'));

        // Get the file
        $file = $request->file('file');
        $filename = FileExt::info($file->getClientOriginalName())['nameWithoutExtension'];

        // Move the file
		$file->move(public_path('assets/txt'), date('Y-m-d-H-i-s').'_'.$filename.'.txt');

        // Redirect
        return redirect()->route('admin.presensi.index')->with(['message' => 'Berhasil mengupload file.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);
        
        // Delete the file
        File::delete(public_path('assets/txt/'.$request->id));

        // Redirect
        return redirect()->route('admin.presensi.index')->with(['message' => 'Berhasil menghapus data.']);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteBulk(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Explode ids
        $ids = explode(",", $request->ids);

        if(count($ids) > 0) {
            foreach($ids as $id) {
                // Delete the file
                File::delete(public_path('assets/txt/'.$id));
            }

            // Redirect
            return redirect()->route('admin.presensi.index')->with(['message' => 'Berhasil menghapus data.']);
        }
    }
}
