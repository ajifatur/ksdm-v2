<html>
<head>
    <title>{{ $title }}</title>
    <style>
		/* Font */
		@font-face {font-family: 'FiraSans-Regular'; src: url({{ public_path('assets/fonts/Fira_Sans/FiraSans-Regular.ttf') }});}
		@font-face {font-family: 'FiraSans-Bold'; src: url({{ public_path('assets/fonts/Fira_Sans/FiraSans-Bold.ttf') }});}

        /* Page */
		@page {margin: 0px;}
		html {margin: 0px;}
		body {font-family: 'FiraSans-Regular'; font-size: 15px; line-height: 15px;}
		.page-break {page-break-after: always;}

        /* Header */
		#header {position: absolute; top: 0px; left: 85px; right: 50px;}
        #header img {width: 100%;}
		
		/* Content */
		#content {position: absolute; top: 120px; left: 100px; right: 50px; text-align: justify;}
		
		/* Subject, Date, Address, Identity, CC */
		#subject tr td {vertical-align: top;}
		#date {position: absolute; top: 2px; right: 10px;}
		#address tr td {vertical-align: top;}
		#identity tr td {vertical-align: top; line-height: 13px;}
		#cc ol {padding-left: 18px; margin-bottom: 0px;}
    </style>
</head>
<body>
    <div id="header"><img src="{{ public_path('assets/images/default/kop.png') }}"></div>
    <div id="content">
        <table id="subject">
            <tr>
                <td width="50">Nomor</td>
                <td width="5">:</td>
                <td>{{ $spkgb->mutasi->perubahan->no_sk }}</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td>Surat Pemberitahuan Kenaikan<br>Gaji Berkala (SPKGB)<br>a.n. Sdr. {{ $spkgb->nama }}</td>
            </tr>
        </table>
		<div id="date">{{ \Ajifatur\Helpers\DateTimeExt::full($spkgb->mutasi->perubahan->tanggal_sk) }}</div>
        <br>
        <table id="address">
            <tr>
                <td width="20">Yth.</td>
                <td>
                    Kepala KPPN Semarang I
                    <br>
                    Jl. Ki Mangunsarkoro No. 34
                    <br>
                    Semarang
                </td>
            </tr>
        </table>
		<br>
		Diberitahukan dengan hormat, bahwa sehubungan dengan telah dipenuhinya masa kerja dan syarat-syarat lainnya kepada :
		<table id="identity" width="100%">
			<tr>
				<td width="5">1.</td>
				<td colspan="2">Nama</td>
				<td width="5">:</td>
				<td>{{ $spkgb->nama }}</td>
			</tr>
			<tr>
				<td>2.</td>
				<td colspan="2">Tempat, tanggal lahir</td>
				<td>:</td>
				<td>{{ $spkgb->pegawai->tempat_lahir }}, {{ \Ajifatur\Helpers\DateTimeExt::full($spkgb->pegawai->tanggal_lahir) }}</td>
			</tr>
			<tr>
				<td>3.</td>
				<td colspan="2">NIP</td>
				<td>:</td>
				<td>{{ $spkgb->pegawai->nip }}</td>
			</tr>
			<tr>
				<td>4.</td>
				<td colspan="2">Pangkat</td>
				<td>:</td>
				<td>{{ $spkgb->mutasi->golru->indonesia }} - {{ $spkgb->mutasi->golru->nama }}</td>
			</tr>
			<tr>
				<td>5.</td>
				<td colspan="2">Jabatan</td>
				<td>:</td>
				<td>{{ $spkgb->jabfung->nama }} {{ $spkgb->jabstruk ? '('.$spkgb->jabstruk->nama.')' : '' }}</td>
			</tr>
			<tr>
				<td>6.</td>
				<td colspan="2">Unit Kerja</td>
				<td>:</td>
				<td>{{ $spkgb->unit->full }}</td>
			</tr>
			<tr>
				<td>7.</td>
				<td colspan="2">Gaji Pokok Lama</td>
				<td>:</td>
				<td>{{ 'Rp '.number_format($spkgb->mutasi_sebelum->gaji_pokok->gaji_pokok,2,',','.') }}</td>
			</tr>
			<tr>
				<td colspan="5">(atas dasar SK terakhir tentang gaji pokok/pangkat terakhir yang ditetapkan):</td>
			</tr>
			<tr>
				<td></td>
				<td width="5">a.</td>
				<td width="120">oleh pejabat</td>
				<td>:</td>
				<td>{{ $spkgb->mutasi_sebelum->perubahan->pejabat->nama }}</td>
			</tr>
			<tr>
				<td></td>
				<td>b.</td>
				<td>tanggal dan nomor</td>
				<td>:</td>
				<td>{{ \Ajifatur\Helpers\DateTimeExt::full($spkgb->mutasi_sebelum->perubahan->tanggal_sk) }}; {{ $spkgb->mutasi_sebelum->perubahan->no_sk }}</td>
			</tr>
			<tr>
				<td></td>
				<td>c.</td>
				<td>tanggal berlakunya</td>
				<td>:</td>
				<td>{{ \Ajifatur\Helpers\DateTimeExt::full($spkgb->mutasi_sebelum->perubahan->tmt) }}</td>
			</tr>
			<tr>
				<td></td>
				<td>d.</td>
				<td>dalam masa kerja gol.</td>
				<td>:</td>
				<td>{{ $spkgb->mutasi_sebelum->perubahan->mk_tahun }} tahun {{ $spkgb->mutasi_sebelum->perubahan->mk_bulan }} bulan</td>
			</tr>
			<tr>
				<td colspan="5">diberikan kenaikan gaji berkala hingga memperoleh:</td>
			</tr>
			<tr>
				<td>8.</td>
				<td colspan="2">Gaji Pokok Baru</td>
				<td>:</td>
				<td>{{ 'Rp '.number_format($spkgb->mutasi->gaji_pokok->gaji_pokok,2,',','.') }}</td>
			</tr>
			<tr>
				<td>9.</td>
				<td colspan="2">Berdasarkan Masa Kerja</td>
				<td>:</td>
				<td>{{ $spkgb->mutasi->perubahan->mk_tahun }} tahun 0 bulan</td>
			</tr>
			<tr>
				<td>10.</td>
				<td colspan="2">Dalam Golongan Ruang</td>
				<td>:</td>
				<td>{{ $spkgb->mutasi->golru->indonesia }} - {{ $spkgb->mutasi->golru->nama }}</td>
			</tr>
			<tr>
				<td>11.</td>
				<td colspan="2">Terhitung Mulai Tanggal</td>
				<td>:</td>
				<td>{{ \Ajifatur\Helpers\DateTimeExt::full($spkgb->mutasi->tmt) }}</td>
			</tr>
		</table>
		<br>
		Diharap sesuai dengan Peraturan Pemerintah Nomor 15 Tahun 2019, kepada pegawai tersebut mohon dapat dibayarkan penghasilannya berdasarkan gaji pokoknya yang baru.
		<br><br><br>
		<table id="sign" width="100%">
			<tr>
				<td width="55%"></td>
				<td width="45%">
					a.n. Rektor
					<br>
					Wakil Rektor Bidang Perencanaan, Umum, SDM, dan Keuangan
					<br>
					u.b. Direktur Umum dan SDM,
					<br><br><br><br><br>
					{{ title_name($spkgb->ttd->nama, $spkgb->ttd->gelar_depan, $spkgb->ttd->gelar_belakang) }}
					<br>
					NIP. {{ $spkgb->ttd->nip }}
				</td>
			</tr>
		</table>
		<div id="cc">
			Tembusan:
			<ol>
				<li>Rektor</li>
				<li>{{ $spkgb->unit->pimpinan }}</li>
				<li>Sdr. {{ $spkgb->nama }}</li>
			</ol>
			Universitas Negeri Semarang
		</div>
    </div>
</body>
</html>