<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('auth.login');
});

Route::group(['middleware' => ['faturhelper.admin']], function() {
    // Presensi
    Route::get('/admin/presensi', 'PresensiController@index')->name('admin.presensi.index');
    Route::get('/admin/presensi/detail', 'PresensiController@detail')->name('admin.presensi.detail');
    Route::post('/admin/presensi/import', 'PresensiController@import')->name('admin.presensi.import');
    Route::post('/admin/presensi/delete', 'PresensiController@delete')->name('admin.presensi.delete');
    Route::post('/admin/presensi/delete-bulk', 'PresensiController@deleteBulk')->name('admin.presensi.delete-bulk');

    // Pegawai
    Route::get('/admin/pegawai', 'PegawaiController@index')->name('admin.pegawai.index');
    Route::get('/admin/pegawai/active', 'PegawaiController@active')->name('admin.pegawai.active');
    Route::get('/admin/pegawai/inactive', 'PegawaiController@inactive')->name('admin.pegawai.inactive');
    Route::get('/admin/pegawai/search', 'PegawaiController@search')->name('admin.pegawai.search');
    Route::get('/admin/pegawai/detail/{id}', 'PegawaiController@detail')->name('admin.pegawai.detail');
    Route::get('/admin/pegawai/edit/{id}', 'PegawaiController@edit')->name('admin.pegawai.edit');
    Route::post('/admin/pegawai/update', 'PegawaiController@update')->name('admin.pegawai.update');

    // Mutasi
    Route::get('/admin/mutasi', 'MutasiController@index')->name('admin.mutasi.index');
    Route::get('/admin/mutasi/new', 'MutasiController@new')->name('admin.mutasi.new');
    Route::get('/admin/mutasi/kp', 'MutasiController@kp')->name('admin.mutasi.kp');
    Route::get('/admin/mutasi/kgb', 'MutasiController@kgb')->name('admin.mutasi.kgb');
    Route::get('/admin/mutasi/{id}/create', 'MutasiController@create')->name('admin.mutasi.create');
    Route::post('/admin/mutasi/store', 'MutasiController@store')->name('admin.mutasi.store');
    Route::get('/admin/mutasi/{id}/edit/{mutasi_id}', 'MutasiController@edit')->name('admin.mutasi.edit');
    Route::post('/admin/mutasi/update', 'MutasiController@update')->name('admin.mutasi.update');
    Route::post('/admin/mutasi/delete', 'MutasiController@delete')->name('admin.mutasi.delete');

    // Remun Gaji
    Route::get('/admin/remun-gaji', 'RemunGajiController@index')->name('admin.remun-gaji.index');
    Route::get('/admin/remun-gaji/process', 'RemunGajiController@process')->name('admin.remun-gaji.process');
    Route::post('/admin/remun-gaji/process', 'RemunGajiController@process')->name('admin.remun-gaji.process');
    Route::get('/admin/remun-gaji/monitoring', 'RemunGajiController@monitoring')->name('admin.remun-gaji.monitoring');
    Route::get('/admin/remun-gaji/print', 'RemunGajiController@print')->name('admin.remun-gaji.print');
    Route::get('/admin/remun-gaji/excel', 'RemunGajiController@excel')->name('admin.remun-gaji.excel');
    Route::get('/admin/remun-gaji/excel-pusat', 'RemunGajiController@excelPusat')->name('admin.remun-gaji.excel-pusat');
    Route::get('/admin/remun-gaji/recap/excel', 'RemunGajiController@excelRekap')->name('admin.remun-gaji.recap.excel');
    Route::get('/admin/remun-gaji/change', 'RemunGajiController@change')->name('admin.remun-gaji.change');

    // Lebih Kurang
    Route::post('/admin/lebih-kurang/update', 'LebihKurangController@update')->name('admin.lebih-kurang.update');
    Route::post('/admin/lebih-kurang/delete', 'LebihKurangController@delete')->name('admin.lebih-kurang.delete');

    // Remun Insentif
    Route::get('/admin/remun-insentif', 'RemunInsentifController@index')->name('admin.remun-insentif.index');
    Route::get('/admin/remun-insentif/monitoring', 'RemunInsentifController@monitoring')->name('admin.remun-insentif.monitoring');
    Route::get('/admin/remun-insentif/excel', 'RemunInsentifController@excel')->name('admin.remun-insentif.excel');
    Route::get('/admin/remun-insentif/excel-pusat', 'RemunInsentifController@excelPusat')->name('admin.remun-insentif.excel-pusat');
    Route::get('/admin/remun-insentif/print-potongan', 'RemunInsentifController@printPotongan')->name('admin.remun-insentif.print-potongan');
	
    // Referensi
    Route::get('/admin/referensi', 'ReferensiController@index')->name('admin.referensi.index');
	
    // Jabatan
    Route::get('/admin/jabatan', 'JabatanController@index')->name('admin.jabatan.index');
    Route::get('/admin/jabatan/remun', 'JabatanController@remun')->name('admin.jabatan.remun');

    // Unit
    Route::get('/admin/unit', 'UnitController@index')->name('admin.unit.index');

    // Anak Satker
    Route::get('/admin/anak-satker', 'AnakSatkerController@index')->name('admin.anak-satker.index');

    // Status Kepegawaian
    Route::get('/admin/status-kepegawaian', 'StatusKepegawaianController@index')->name('admin.status-kepegawaian.index');

    // Tunjangan Profesi
    Route::get('/admin/tunjangan-profesi', 'TunjanganProfesiController@index')->name('admin.tunjangan-profesi.index');
    Route::get('/admin/tunjangan-profesi/create', 'TunjanganProfesiController@create')->name('admin.tunjangan-profesi.create');
    Route::post('/admin/tunjangan-profesi/store', 'TunjanganProfesiController@store')->name('admin.tunjangan-profesi.store');
    Route::post('/admin/tunjangan-profesi/delete', 'TunjanganProfesiController@delete')->name('admin.tunjangan-profesi.delete');
    Route::get('/admin/tunjangan-profesi/monitoring', 'TunjanganProfesiController@monitoring')->name('admin.tunjangan-profesi.monitoring');
    Route::get('/admin/tunjangan-profesi/process', 'TunjanganProfesiController@process')->name('admin.tunjangan-profesi.process');
    Route::post('/admin/tunjangan-profesi/process', 'TunjanganProfesiController@process')->name('admin.tunjangan-profesi.process');
    Route::get('/admin/tunjangan-profesi/print/{id}', 'TunjanganProfesiController@print')->name('admin.tunjangan-profesi.print');
    Route::get('/admin/tunjangan-profesi/print-non-pns', 'TunjanganProfesiController@printNonPNS')->name('admin.tunjangan-profesi.print-non-pns');
    Route::get('/admin/tunjangan-profesi/excel', 'TunjanganProfesiController@excel')->name('admin.tunjangan-profesi.excel');
    Route::get('/admin/tunjangan-profesi/csv/{id}', 'TunjanganProfesiController@csv')->name('admin.tunjangan-profesi.csv');
    Route::get('/admin/tunjangan-profesi/csv-non-pns', 'TunjanganProfesiController@csvNonPNS')->name('admin.tunjangan-profesi.csv-non-pns');
    Route::get('/admin/tunjangan-profesi/new', 'TunjanganProfesiController@new')->name('admin.tunjangan-profesi.new');
    Route::get('/admin/tunjangan-profesi/change', 'TunjanganProfesiController@change')->name('admin.tunjangan-profesi.change');

    // Gaji Induk
    Route::get('/admin/gaji', 'GajiController@index')->name('admin.gaji.index');
    Route::get('/admin/gaji/monitoring', 'GajiController@monitoring')->name('admin.gaji.monitoring');
    Route::get('/admin/gaji/recap', 'GajiController@recap')->name('admin.gaji.recap');
    Route::get('/admin/gaji/yearly-recap', 'GajiController@yearlyRecap')->name('admin.gaji.yearly-recap');
    Route::get('/admin/gaji/excel', 'GajiController@excel')->name('admin.gaji.excel');
    Route::post('/admin/gaji/import', 'GajiController@import')->name('admin.gaji.import');
    Route::get('/admin/gaji/change', 'GajiController@change')->name('admin.gaji.change');

    // KGB
    Route::get('/admin/kgb', 'KGBController@index')->name('admin.kgb.index');
    
    // Satyalancana Karya Satya
    Route::get('/admin/slks', 'SLKSController@index')->name('admin.slks.index');
    Route::get('/admin/slks/create', 'SLKSController@create')->name('admin.slks.create');
    Route::post('/admin/slks/store', 'SLKSController@store')->name('admin.slks.store');
    Route::post('/admin/slks/add', 'SLKSController@add')->name('admin.slks.add');
    Route::get('/admin/slks/nomination', 'SLKSController@nomination')->name('admin.slks.nomination');
    Route::get('/admin/slks/blacklist', 'SLKSController@blacklist')->name('admin.slks.blacklist');

    // Slip Gaji
    Route::get('/admin/slip-gaji', 'SlipGajiController@index')->name('admin.slip-gaji.index');
    Route::get('/admin/slip-gaji/create', 'SlipGajiController@create')->name('admin.slip-gaji.create');
    Route::post('/admin/slip-gaji/store', 'SlipGajiController@store')->name('admin.slip-gaji.store');
    Route::get('/admin/slip-gaji/edit/{id}', 'SlipGajiController@edit')->name('admin.slip-gaji.edit');
    Route::post('/admin/slip-gaji/update', 'SlipGajiController@update')->name('admin.slip-gaji.update');
    Route::post('/admin/slip-gaji/delete', 'SlipGajiController@delete')->name('admin.slip-gaji.delete');
    Route::get('/admin/slip-gaji/print/{id}', 'SlipGajiController@print')->name('admin.slip-gaji.print');

    // Others
    Route::get('/admin/pegawai/print', 'PegawaiController@print')->name('admin.pegawai.print'); // SPTJM
    // Route::get('/admin/pegawai/import', 'PegawaiController@import')->name('admin.pegawai.import');
    // Route::get('/admin/jabatan/check', 'JabatanController@check')->name('admin.jabatan.check');
    // Route::get('/admin/remun-gaji/import', 'RemunGajiController@import')->name('admin.remun-gaji.import');
    // Route::get('/admin/remun-insentif/import', 'RemunInsentifController@import')->name('admin.remun-insentif.import');
    // Route::get('/admin/tunjangan-profesi/import', 'TunjanganProfesiController@import')->name('admin.tunjangan-profesi.import');
    // Route::get('/admin/mutasi/import', 'MutasiController@import')->name('admin.mutasi.import');
    // Route::get('/admin/jabatan/import', 'JabatanController@import')->name('admin.jabatan.import');
    // Route::get('/admin/jabatan-dasar/import', 'JabatanDasarController@import')->name('admin.jabatan-dasar.import');
    // Route::get('/admin/referensi/import', 'ReferensiController@import')->name('admin.referensi.import');
    // Route::get('/admin/golru/import', 'GolruController@import')->name('admin.golru.import');
    // Route::get('/admin/gaji-pokok/import', 'GajiPokokController@import')->name('admin.gaji-pokok.import');
    // Route::get('/admin/mutasi/check', 'MutasiController@check')->name('admin.mutasi.check');
    // Route::get('/admin/tunjangan-profesi/unit', 'TunjanganProfesiController@unit')->name('admin.tunjangan-profesi.unit');
});

\Ajifatur\Helpers\RouteExt::auth();
\Ajifatur\Helpers\RouteExt::admin();

Route::group(['middleware' => ['faturhelper.admin']], function() {
    Route::get('/admin', 'DashboardController@index')->name('admin.dashboard');
});