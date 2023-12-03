<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Models\LebihKurang;

class LebihKurangController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Simpan lebih kurang
        $lebih_kurang = LebihKurang::find($request->id);
        if(!$lebih_kurang) $lebih_kurang = new LebihKurang;
        $lebih_kurang->pegawai_id = $request->pegawai;
        $lebih_kurang->jabatan_terbayar_id = $request->jabatan_terbayar;
        $lebih_kurang->jabatan_seharusnya_id = $request->jabatan_seharusnya;
        $lebih_kurang->bulan = $request->bulan;
        $lebih_kurang->tahun = $request->tahun;
        $lebih_kurang->bulan_proses = $request->bulan_proses;
        $lebih_kurang->triwulan_proses = 0;
        $lebih_kurang->tahun_proses = $request->tahun_proses;
        $lebih_kurang->terbayar = str_replace(',', '', $request->terbayar);
        $lebih_kurang->seharusnya = str_replace(',', '', $request->seharusnya);
        $lebih_kurang->selisih = $lebih_kurang->seharusnya - $lebih_kurang->terbayar;
        $lebih_kurang->save();

        // Redirect
        return redirect()->route('admin.remun-gaji.index', ['kategori' => $request->kategori, 'unit' => $request->unit, 'bulan' => $request->bulan_proses, 'tahun' => $request->tahun_proses])->with(['message' => 'Berhasil mengupdate data.']);
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

        // Delete lebih kurang
        $lebih_kurang = LebihKurang::findOrFail($request->id);
        $lebih_kurang->delete();

        // Redirect
        return redirect()->route('admin.remun-gaji.index', ['kategori' => $request->kategori, 'unit' => $request->unit, 'bulan' => $request->bulan_proses, 'tahun' => $request->tahun_proses])->with(['message' => 'Berhasil menghapus data.']);
    }
}
