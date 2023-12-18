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
		
		/* Content */
		.content-1 {position: absolute; top: 15px; left: 15px; right: 15px; height: 47.5%; text-align: justify; border: 2px solid #333;}
		.content-2 {position: absolute; top: 50%; left: 15px; right: 15px; height: 47.5%; text-align: justify; border: 2px solid #333;}
		.subject tr td, .description tr td, .sign tr td {vertical-align: top;}
		.sign .name {font-family: 'FiraSans-Bold'; text-decoration: underline;}
        .subject {position: absolute; top: 30px; right: 120px; font-size: 13px; line-height: 13px;}
		.title {position: absolute; top: 110px; width: 100%; font-family: 'FiraSans-Bold'; font-size: 20px; text-align: center; text-decoration: underline;}
        .description {position: absolute; width: 96%; top: 160px; left: 15px;}
        .sign {position: absolute; top: 310px; left: 15px; right: 15px; width: 100%;}
    </style>
</head>
<body>
	@for($i=1; $i<=2; $i++)
	<div class="content-{{ $i }}">
		<table class="subject">
			<tr>
				<td width="60">TA</td>
				<td width="5">:</td>
				<td>2023</td>
			</tr>
			<tr>
				<td>Nomor Bukti</td>
				<td>:</td>
				<td></td>
			</tr>
		</table>
		<div class="title">KUITANSI/BUKTI PEMBAYARAN</div>
		<table class="description">
			<tr>
				<td width="120">Sudah terima dari</td>
				<td width="5">:</td>
				<td>Rektor Universitas Negeri Semarang</td>
			</tr>
			<tr>
				<td>Jumlah Uang</td>
				<td>:</td>
				<td>Rp {{ number_format($potongan_zakat->sum('pot_zakat'),2,',','.') }}</td>
			</tr>
			<tr>
				<td>Terbilang</td>
				<td>:</td>
				<td>{{ ucwords(counted($potongan_zakat->sum('pot_zakat'))) }} Rupiah</td>
			</tr>
			<tr>
				<td>Untuk pembayaran</td>
				<td>:</td>
				<td>Amal Jariyah Para Pegawai {{ $pensiun != 1 ? $pusat == 1 ? 'Pusat' : $unit->full : 'Pensiun' }} ({{ count($potongan_zakat) }} Orang)</td>
			</tr>
		</table>
		<table class="sign">
			<tr>
				<td width="60%">
					<br>
					Lunas Dibayar
					<br>
					Bendahara Pengeluaran
					<br>
					<br>
					<br>
					<br>
					<span class="name">Daru Lestariningsih, S.E., M.Si.</span>
					<br>
					NIP. 198508032010122003
				</td>
				<td width="40%">
					Semarang,
					<br>
					<br>
					Penerima
					<br>
					<br>
					<br>
					<br>
					<span class="name">Dr. Iwan Junaedi, M.Pd.</span>
					<br>
					NIP. 197103281999031001
				</td>
			</tr>
		</table>
	</div>
	@endfor
</body>
</html>