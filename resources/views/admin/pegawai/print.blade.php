<html>
<head>
    <title>SPTJM</title>
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
        #content {position: absolute; top: 180px; left: 120px; right: 70px; line-height: 18px;}
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
                <td>Prof. Dr. S Martono, M.Si.</td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>196603081989011001</td>
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
            <li>Perhitungan yang terdapat pada daftar pembayaran Tunjangan Profesi Dosen Guru Besar Bulan Juli 2023 bagi satuan kerja Universitas Negeri Semarang telah dihitung dengan benar.</li>
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