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
		body {font-family: 'FiraSans-Regular'; font-size: 14px; line-height: 14px;}
		.page-break {page-break-after: always;}

        /* Header */
		#header {position: absolute; top: 0px; left: 105px; right: 60px;}
        #header img {width: 100%;}

        /* Title */
        #title {position: absolute; top: 130px; left: 120px; right: 70px; font-family: 'FiraSans-Bold'; font-size: 16px; text-align: center;}

        /* Content */
        #content {position: absolute; top: 200px; left: 120px; right: 70px; line-height: 18px; text-align: justify;}
        #content ol {margin-top: 0px; padding-left: 18px;}

        /* Sign */
        #sign {line-height: 14px;}
    </style>
</head>
<body>
    <div id="header"><img src="{{ public_path('assets/images/default/kop.png') }}"></div>
    <div id="title">SURAT PERNYATAAN TANGGUNG JAWAB MUTLAK</div>
    <div id="content">
        Yang bertanda tangan di bawah ini:
        <br>
        <table border="0">
            <tr>
                <td width="60">Nama</td>
                <td width="5">:</td>
                <td>{{ title_name(ttd('kpa', $tanggal)->pegawai->nama, ttd('kpa', $tanggal)->pegawai->gelar_depan, ttd('kpa', $tanggal)->pegawai->gelar_belakang) }}</td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>{{ ttd('ppk', $tanggal)->pegawai->nip }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>Kuasa Pengguna Anggaran Universitas Negeri Semarang</td>
            </tr>
        </table>
        <br>
        menyatakan dengan sesungguhnya bahwa:
        <ol>
            <li>Perhitungan yang terdapat pada daftar pembayaran Tunjangan {{ $angkatan || $jenis ? $angkatan ? $angkatan->jenis->deskripsi : $jenis->deskripsi : '' }} {{ $angkatan ? 'Angkatan '.$angkatan->nama : '' }} Bulan {{ \Ajifatur\Helpers\DateTimeExt::month($bulan) }} {{ $tahun }} bagi satuan kerja Universitas Negeri Semarang telah dihitung dengan benar.</li>
            <li>Apabila di kemudian hari terdapat kelebihan atas pembayaran tunjangan tersebut, kami bersedia untuk menyetor kelebihan tersebut ke Kas Negara.</li>
        </ol>
        Demikian pernyataan ini kami buat dengan sebenar-benarnya.
        <br><br><br>
        <table id="sign" width="100%">
            <tr>
                <td width="60%"></td>
                <td width="40%">
                    Semarang, {{ \Ajifatur\Helpers\DateTimeExt::full(date('Y-m-d')) }}
                    <br>
                    Kuasa Pengguna Anggaran,
                    <br><br><br><br><br>
                    Prof. Dr. S Martono, M.Si.
                    <br>
                    NIP. 196603081989011001
                </td>
            </tr>
        </table>
    </div>
</body>
</html>