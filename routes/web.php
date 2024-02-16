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
    Route::get('/admin/pegawai/edit-tmt-golongan/{id}', 'PegawaiController@editTMTGolongan')->name('admin.pegawai.edit-tmt-golongan');
    Route::post('/admin/pegawai/update-tmt-golongan', 'PegawaiController@updateTMTGolongan')->name('admin.pegawai.update-tmt-golongan');

    // Mutasi
    Route::get('/admin/mutasi', 'MutasiController@index')->name('admin.mutasi.index');
    Route::get('/admin/mutasi/{id}/create', 'MutasiController@create')->name('admin.mutasi.create');
    Route::post('/admin/mutasi/store', 'MutasiController@store')->name('admin.mutasi.store');
    Route::get('/admin/mutasi/{id}/edit/{mutasi_id}', 'MutasiController@edit')->name('admin.mutasi.edit');
    Route::post('/admin/mutasi/update', 'MutasiController@update')->name('admin.mutasi.update');
    Route::post('/admin/mutasi/delete', 'MutasiController@delete')->name('admin.mutasi.delete');

    // KP
    Route::get('/admin/mutasi/kp', 'KPController@index')->name('admin.kp.index');
    Route::get('/admin/mutasi/kp/monitoring', 'KPController@monitoring')->name('admin.kp.monitoring');

    // KGB
    Route::get('/admin/mutasi/kgb', 'KGBController@index')->name('admin.kgb.index');
    Route::get('/admin/mutasi/kgb/monitoring', 'KGBController@monitoring')->name('admin.kgb.monitoring');

    // PGP
    Route::get('/admin/mutasi/pgp', 'PGPController@index')->name('admin.pgp.index');

    // Remun Gaji
    Route::get('/admin/remun-gaji', 'RemunGajiController@index')->name('admin.remun-gaji.index');
    Route::get('/admin/remun-gaji/process', 'RemunGajiController@process')->name('admin.remun-gaji.process');
    Route::post('/admin/remun-gaji/process', 'RemunGajiController@process')->name('admin.remun-gaji.process');
    Route::get('/admin/remun-gaji/monitoring', 'RemunGajiController@monitoring')->name('admin.remun-gaji.monitoring');
    Route::get('/admin/remun-gaji/print', 'RemunGajiController@print')->name('admin.remun-gaji.print');
    Route::get('/admin/remun-gaji/change', 'RemunGajiController@change')->name('admin.remun-gaji.change');
    Route::get('/admin/remun-gaji/change/all', 'RemunGajiController@changeAll')->name('admin.remun-gaji.change.all');
    Route::get('/admin/remun-gaji/export/single', 'RemunGajiExportController@single')->name('admin.remun-gaji.export.single');
    Route::get('/admin/remun-gaji/export/pusat', 'RemunGajiExportController@pusat')->name('admin.remun-gaji.export.pusat');
    Route::get('/admin/remun-gaji/export/recap', 'RemunGajiExportController@recap')->name('admin.remun-gaji.export.recap');

    // Lebih Kurang
    Route::post('/admin/lebih-kurang/update', 'LebihKurangController@update')->name('admin.lebih-kurang.update');
    Route::post('/admin/lebih-kurang/delete', 'LebihKurangController@delete')->name('admin.lebih-kurang.delete');

    // Kekurangan Remun Gaji
    Route::get('/admin/remun-gaji/kekurangan/monitoring', 'RemunGajiKekuranganController@monitoring')->name('admin.remun-gaji.kekurangan.monitoring');
    Route::get('/admin/remun-gaji/kekurangan/print', 'RemunGajiKekuranganController@print')->name('admin.remun-gaji.kekurangan.print');
    Route::get('/admin/remun-gaji/kekurangan/export/single', 'RemunGajiKekuranganExportController@single')->name('admin.remun-gaji.kekurangan.export.single');
    Route::get('/admin/remun-gaji/kekurangan/export/pusat', 'RemunGajiKekuranganExportController@pusat')->name('admin.remun-gaji.kekurangan.export.pusat');
    Route::get('/admin/remun-gaji/kekurangan/export/recap', 'RemunGajiKekuranganExportController@recap')->name('admin.remun-gaji.kekurangan.export.recap');

    // Remun Insentif
    Route::get('/admin/remun-insentif', 'RemunInsentifController@index')->name('admin.remun-insentif.index');
    Route::get('/admin/remun-insentif/monitoring', 'RemunInsentifController@monitoring')->name('admin.remun-insentif.monitoring');
    Route::get('/admin/remun-insentif/export/single', 'RemunInsentifExportController@single')->name('admin.remun-insentif.export.single');
    Route::get('/admin/remun-insentif/export/pusat', 'RemunInsentifExportController@pusat')->name('admin.remun-insentif.export.pusat');
    Route::get('/admin/remun-insentif/export/recap', 'RemunInsentifExportController@recap')->name('admin.remun-insentif.export.recap');
    Route::get('/admin/remun-insentif/export/zakat', 'RemunInsentifExportController@zakat')->name('admin.remun-insentif.export.zakat');

    // Remun Insentif (Print)
    Route::get('/admin/remun-insentif/print/potongan', 'RemunInsentifPrintController@potongan')->name('admin.remun-insentif.print.potongan');
    Route::get('/admin/remun-insentif/print/zakat', 'RemunInsentifPrintController@zakat')->name('admin.remun-insentif.print.zakat');
    Route::get('/admin/remun-insentif/print/kwitansi', 'RemunInsentifPrintController@kwitansi')->name('admin.remun-insentif.print.kwitansi');

    // Remun Insentif (Zakat)
    Route::get('/admin/remun-insentif/zakat/import', 'RemunInsentifZakatController@import')->name('admin.remun-insentif.zakat.import');
    Route::post('/admin/remun-insentif/zakat/import', 'RemunInsentifZakatController@import')->name('admin.remun-insentif.zakat.import');

    // Remun 15
    Route::get('/admin/remun-15', 'Remun15Controller@index')->name('admin.remun-15.index');
    Route::get('/admin/remun-15/monitoring', 'Remun15Controller@monitoring')->name('admin.remun-15.monitoring');
    Route::get('/admin/remun-15/export/single', 'Remun15ExportController@single')->name('admin.remun-15.export.single');
    Route::get('/admin/remun-15/export/pusat', 'Remun15ExportController@pusat')->name('admin.remun-15.export.pusat');
    Route::get('/admin/remun-15/export/recap', 'Remun15ExportController@recap')->name('admin.remun-15.export.recap');
	
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
	
    // SK
    Route::get('/admin/sk', 'SKController@index')->name('admin.sk.index');
	
    // Gaji Pokok
    Route::get('/admin/gaji-pokok', 'GajiPokokController@index')->name('admin.gaji-pokok.index');
	
    // Pejabat Penandatangan
    Route::get('/admin/ttd', 'TTDController@index')->name('admin.ttd.index');

    // Tunjangan Profesi
    Route::get('/admin/tunjangan-profesi', 'TunjanganProfesiController@index')->name('admin.tunjangan-profesi.index');
    Route::post('/admin/tunjangan-profesi/delete', 'TunjanganProfesiController@delete')->name('admin.tunjangan-profesi.delete');
    Route::get('/admin/tunjangan-profesi/monitoring', 'TunjanganProfesiController@monitoring')->name('admin.tunjangan-profesi.monitoring');
    Route::get('/admin/tunjangan-profesi/process', 'TunjanganProfesiController@process')->name('admin.tunjangan-profesi.process');
    Route::post('/admin/tunjangan-profesi/process', 'TunjanganProfesiController@process');
    Route::get('/admin/tunjangan-profesi/export', 'TunjanganProfesiController@export')->name('admin.tunjangan-profesi.export');
    Route::get('/admin/tunjangan-profesi/new', 'TunjanganProfesiController@new')->name('admin.tunjangan-profesi.new');
    Route::get('/admin/tunjangan-profesi/change', 'TunjanganProfesiController@change')->name('admin.tunjangan-profesi.change');
	
	// Tunjangan Profesi (Print)
    Route::get('/admin/tunjangan-profesi/print/single/{id}', 'TunjanganProfesiPrintController@single')->name('admin.tunjangan-profesi.print.single');
    Route::get('/admin/tunjangan-profesi/print/batch/{id}', 'TunjanganProfesiPrintController@batch')->name('admin.tunjangan-profesi.print.batch');
    Route::get('/admin/tunjangan-profesi/print/non-pns', 'TunjanganProfesiPrintController@nonPNS')->name('admin.tunjangan-profesi.print.non-pns');
    Route::get('/admin/tunjangan-profesi/print/sptjm', 'TunjanganProfesiPrintController@sptjm')->name('admin.tunjangan-profesi.print.sptjm');
	
	// Tunjangan Profesi (CSV)
    Route::get('/admin/tunjangan-profesi/csv/single/{id}', 'TunjanganProfesiCSVController@single')->name('admin.tunjangan-profesi.csv.single');
    Route::get('/admin/tunjangan-profesi/csv/non-pns', 'TunjanganProfesiCSVController@nonPNS')->name('admin.tunjangan-profesi.csv.non-pns');
    Route::get('/admin/tunjangan-profesi/csv/batch/{id}', 'TunjanganProfesiCSVController@batch')->name('admin.tunjangan-profesi.csv.batch');
	
	// Tunjangan Profesi (Unit)
    Route::get('/admin/tunjangan-profesi/unit/recap', 'TunjanganProfesiUnitController@recap')->name('admin.tunjangan-profesi.unit.recap');
    Route::get('/admin/tunjangan-profesi/unit/export/{id}', 'TunjanganProfesiUnitController@export')->name('admin.tunjangan-profesi.unit.export');
	
	// Tunjangan Profesi (Bulan)
    Route::get('/admin/tunjangan-profesi/bulan/recap', 'TunjanganProfesiBulanController@recap')->name('admin.tunjangan-profesi.bulan.recap');

	// Tunjangan Profesi (Kekurangan)
    Route::get('/admin/tunjangan-profesi/kekurangan/monitoring', 'TunjanganProfesiKekuranganController@monitoring')->name('admin.tunjangan-profesi.kekurangan.monitoring');

    // Gaji ASN
    Route::get('/admin/gaji', 'GajiController@index')->name('admin.gaji.index');
    Route::get('/admin/gaji/monitoring', 'GajiController@monitoring')->name('admin.gaji.monitoring');
    Route::get('/admin/gaji/monthly', 'GajiController@monthly')->name('admin.gaji.monthly');
    Route::get('/admin/gaji/annually', 'GajiController@annually')->name('admin.gaji.annually');
    Route::get('/admin/gaji/print/{id}', 'GajiController@print')->name('admin.gaji.print');
    Route::get('/admin/gaji/export', 'GajiController@export')->name('admin.gaji.export');
    Route::get('/admin/gaji/import', 'GajiController@import')->name('admin.gaji.import');
    Route::post('/admin/gaji/import', 'GajiController@import')->name('admin.gaji.import');
    Route::get('/admin/gaji/change', 'GajiController@change')->name('admin.gaji.change');

    // Gaji Pegawai Tetap Non ASN
    Route::get('/admin/gaji-non-asn', 'GajiNonASNController@index')->name('admin.gaji-non-asn.index');
    Route::get('/admin/gaji-non-asn/monitoring', 'GajiNonASNController@monitoring')->name('admin.gaji-non-asn.monitoring');
    Route::get('/admin/gaji-non-asn/import', 'GajiNonASNController@import')->name('admin.gaji-non-asn.import');

    // Gaji Pegawai Tidak Tetap
    Route::get('/admin/gaji-kontrak', 'GajiKontrakController@index')->name('admin.gaji-kontrak.index');
    Route::get('/admin/gaji-kontrak/monitoring', 'GajiKontrakController@monitoring')->name('admin.gaji-kontrak.monitoring');
    Route::get('/admin/gaji-kontrak/export', 'GajiKontrakController@export')->name('admin.gaji-kontrak.export');

    // Uang Makan ASN
    Route::get('/admin/uang-makan', 'UangMakanController@index')->name('admin.uang-makan.index');
    Route::get('/admin/uang-makan/monitoring', 'UangMakanController@monitoring')->name('admin.uang-makan.monitoring');
    Route::get('/admin/uang-makan/recap', 'UangMakanController@recap')->name('admin.uang-makan.recap');
    Route::get('/admin/uang-makan/print/{id}', 'UangMakanController@print')->name('admin.uang-makan.print');
    Route::get('/admin/uang-makan/export', 'UangMakanController@export')->name('admin.uang-makan.export');
    Route::get('/admin/uang-makan/import/pns', 'UangMakanImportController@pns')->name('admin.uang-makan.import.pns');
    Route::post('/admin/uang-makan/import/pns', 'UangMakanImportController@pns')->name('admin.uang-makan.import.pns');
    Route::get('/admin/uang-makan/import/pppk', 'UangMakanImportController@pppk')->name('admin.uang-makan.import.pppk');
    Route::post('/admin/uang-makan/import/pppk', 'UangMakanImportController@pppk')->name('admin.uang-makan.import.pppk');
    Route::post('/admin/uang-makan/import/old', 'UangMakanImportController@old')->name('admin.uang-makan.import.old');

    // Uang Lembur ASN
    Route::get('/admin/uang-lembur', 'UangLemburController@index')->name('admin.uang-lembur.index');
    Route::get('/admin/uang-lembur/monitoring', 'UangLemburController@monitoring')->name('admin.uang-lembur.monitoring');
    Route::get('/admin/uang-lembur/recap', 'UangLemburController@recap')->name('admin.uang-lembur.recap');
    Route::get('/admin/uang-lembur/import', 'UangLemburController@import')->name('admin.uang-lembur.import');
    Route::post('/admin/uang-lembur/import', 'UangLemburController@import')->name('admin.uang-lembur.import');

    // SPKGB
    Route::get('/admin/spkgb', 'SPKGBController@index')->name('admin.spkgb.index');
    Route::get('/admin/spkgb/monitoring', 'SPKGBController@monitoring')->name('admin.spkgb.monitoring');
    Route::get('/admin/spkgb/create/{id}', 'SPKGBController@create')->name('admin.spkgb.create');
    Route::post('/admin/spkgb/store', 'SPKGBController@store')->name('admin.spkgb.store');
    Route::get('/admin/spkgb/edit/{id}', 'SPKGBController@edit')->name('admin.spkgb.edit');
    Route::post('/admin/spkgb/update', 'SPKGBController@update')->name('admin.spkgb.update');
    Route::get('/admin/spkgb/export', 'SPKGBController@export')->name('admin.spkgb.export');

    // SPKGB Print
    Route::get('/admin/spkgb/print/single/{id}', 'SPKGBPrintController@single')->name('admin.spkgb.print.single');
    Route::get('/admin/spkgb/print/recap', 'SPKGBPrintController@recap')->name('admin.spkgb.print.recap');
    Route::get('/admin/spkgb/print/batch', 'SPKGBPrintController@batch')->name('admin.spkgb.print.batch');
    
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

    // Pantauan
    Route::get('/admin/pantauan/mkg', 'PantauanController@mkg')->name('admin.pantauan.mkg');
    Route::get('/admin/pantauan/pensiun', 'PantauanController@pensiun')->name('admin.pantauan.pensiun');
    Route::get('/admin/pantauan/gaji-pokok', 'PantauanController@gajiPokok')->name('admin.pantauan.gaji-pokok');
    Route::get('/admin/pantauan/status-kepegawaian', 'PantauanController@statusKepegawaian')->name('admin.pantauan.status-kepegawaian');

    // Others
    // Route::get('/admin/kgb/import', 'KGBController@import')->name('admin.kgb.import');
    // Route::get('/admin/kp/import', 'SPKGBController@import')->name('admin.kp.import');
    // Route::get('/admin/jabatan/import', 'JabatanController@import')->name('admin.jabatan.import');
    // Route::get('/admin/jabatan-dasar/import', 'JabatanDasarController@import')->name('admin.jabatan-dasar.import');
    // Route::get('/admin/pegawai/import', 'PegawaiController@import')->name('admin.pegawai.import');
    // Route::get('/admin/remun-gaji/import', 'RemunGajiController@import')->name('admin.remun-gaji.import');
    // Route::get('/admin/remun-gaji/kekurangan/import', 'KekuranganRemunGajiController@import')->name('admin.remun-gaji.kekurangan.import');
    // Route::get('/admin/remun-insentif/import', 'RemunInsentifController@import')->name('admin.remun-insentif.import');
    // Route::get('/admin/remun-15/import', 'Remun15Controller@import')->name('admin.remun-15.import');
    // Route::get('/admin/tunjangan-profesi/import', 'TunjanganProfesiController@import')->name('admin.tunjangan-profesi.import');
    // Route::get('/admin/mutasi/import', 'MutasiController@import')->name('admin.mutasi.import');
    // Route::get('/admin/mutasi/import-blu', 'MutasiController@importBLU')->name('admin.mutasi.import-blu');
    // Route::get('/admin/referensi/import', 'ReferensiController@import')->name('admin.referensi.import');
    // Route::get('/admin/golru/import', 'GolruController@import')->name('admin.golru.import');
    // Route::get('/admin/gaji-pokok/import', 'GajiPokokController@import')->name('admin.gaji-pokok.import');
    Route::get('/admin/gaji-kontrak/import', 'GajiKontrakController@import')->name('admin.gaji-kontrak.import');
    // Route::get('/admin/gaji/sync', 'GajiController@sync')->name('admin.gaji.sync');
    // Route::get('/admin/uang-makan/sync', 'UangMakanController@sync')->name('admin.uang-makan.sync');
    // Route::get('/admin/prodi/import', 'ProdiController@import')->name('admin.prodi.import');
    Route::get('/admin/mutasi/pgp/import', 'PGPController@import')->name('admin.pgp.import');
    Route::get('/admin/test/export', 'TestController@export')->name('admin.test.export');
    Route::get('/admin/test/import', 'TestController@import')->name('admin.test.import');
});

\Ajifatur\Helpers\RouteExt::auth();
\Ajifatur\Helpers\RouteExt::admin();

Route::group(['middleware' => ['faturhelper.admin']], function() {
    Route::get('/admin', 'DashboardController@index')->name('admin.dashboard');
});