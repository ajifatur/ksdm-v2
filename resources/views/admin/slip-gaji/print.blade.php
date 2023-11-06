<html>
<head>
    <title>{{ $bahasa == 'en' ? 'Salary Slip' : 'Slip Gaji' }} - {{ $slip_gaji->pegawai->nip }}</title>
    <style>
        /* Page */
		@page {margin: 0px;}
		html {margin: 0px;}
		body {font-size: 16px; line-height: 16px;}
		.page-break {page-break-after: always;}

        /* Header */
		#header {position: absolute; top: 0px; left: 105px; right: 60px;}
        #header img {width: 100%;}

        /* Title */
        #title {position: absolute; top: 130px; left: 120px; right: 70px; font-size: 20px; text-align: center; font-weight: bold; text-decoration: underline;}

        /* Content */
        #content {position: absolute; top: 180px; left: 120px; right: 70px; line-height: 16px;}
        #content table tr td {vertical-align: top;}
        #content ol {margin-top: 0px; padding-left: 18px;}
        .amount tr td:first-child {text-align: right;}
        .amount tr td:last-child {text-align: right;}
        .amount .border-top {border-top: 3px solid #333;}
        .amount .bottom td:first-child {text-align: left;}

        /* Sign */
        #sign {line-height: 18px;}
    </style>
</head>
<body>
    <div id="header"><img src="{{ public_path('assets/images/default/kop.png') }}"></div>
    <div id="title">{{ $bahasa == 'en' ? 'SALARY SLIP' : 'SURAT KETERANGAN PEMBAYARAN GAJI' }}</div>
    <div id="content">
        <table border="0">
            <tr>
                <td width="140">{{ $bahasa == 'en' ? 'Month' : 'Bulan' }}</td>
                <td width="5">:</td>
                <td>{{ $bahasa == 'en' ? $bulan_english : \Ajifatur\Helpers\DateTimeExt::month($slip_gaji->bulan) }} {{ $slip_gaji->tahun }}</td>
            </tr>
            <tr>
                <td>{{ $bahasa == 'en' ? 'Name' : 'Nama' }}</td>
                <td>:</td>
                <td>{{ title_name($slip_gaji->pegawai->nama, $slip_gaji->pegawai->gelar_depan, $slip_gaji->pegawai->gelar_belakang) }}</td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $slip_gaji->pegawai->nip }}</td>
            </tr>
            <tr>
                <td>{{ $bahasa == 'en' ? 'Rank/Level' : 'Pangkat/Gol.' }}</td>
                <td>:</td>
                <td>{{ $bahasa == 'en' ? $slip_gaji->golru->english : $slip_gaji->golru->indonesia }}, {{ $slip_gaji->golru->nama }}</td>
            </tr>
            <tr>
                <td>{{ $bahasa == 'en' ? 'Position' : 'Jabatan' }}</td>
                <td>:</td>
                <td>{{ $bahasa == 'en' ? $slip_gaji->position : $slip_gaji->jabatan }}<br>Universitas Negeri Semarang</td>
            </tr>
            <tr>
                <td>{{ $bahasa == 'en' ? 'Number of children who get monthly allowance' : 'Banyaknya anak yang mendapat tunjangan' }}</td>
                <td>:</td>
                <td>{{ $slip_gaji->children }} {{ $bahasa == 'en' ? 'person' : 'orang' }}</td>
            </tr>
        </table>
        <br><br><br>
        <b>{{ $bahasa == 'en' ? 'Earnings' : 'Pendapatan' }}:</b>
        <table border="0" width="100%" class="amount">
            <tr>
                <td width="15">1.</td>
                <td>{{ $bahasa == 'en' ? 'Basic Salary' : 'Gaji Pokok' }}</td>
                <td width="5">:</td>
                <td width="30">{{ $mata_uang }}</td>
                <td width="100">{{ number_format($gaji_induk->gjpokok,2,',','.') }}</td>
            </tr>
            <tr>
                <td>2.</td>
                <td>{{ $bahasa == 'en' ? 'Spouse Allowance' : 'Tunjangan Istri/Suami' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->tjistri,2,',','.') }}</td>
            </tr>
            <tr>
                <td>3.</td>
                <td>{{ $bahasa == 'en' ? 'Children Allowance' : 'Tunjangan Anak' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->tjanak,2,',','.') }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td colspan="3" class="border-top"></td>
            </tr>
            <tr>
                <td></td>
                <td>{{ $bahasa == 'en' ? 'Total earnings without additional allowance' : 'Jumlah penghasilan tanpa tunjangan' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->gjpokok + $gaji_induk->tjistri + $gaji_induk->tjanak,2,',','.') }}</td>
            </tr>
            <tr>
                <td>4.</td>
                <td>{{ $bahasa == 'en' ? 'Refinement Allowance' : 'Tunjangan Perbaikan Penghasilan' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format(0,2,',','.') }}</td>
            </tr>
            <tr>
                <td>5.</td>
                <td>{{ $bahasa == 'en' ? 'General Allowance' : 'Tunjangan Umum' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->tjupns,2,',','.') }}</td>
            </tr>
            <tr>
                <td>6.</td>
                <td>{{ $bahasa == 'en' ? 'General Allowance Extra' : 'Tambahan Tunjangan Umum' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format(0,2,',','.') }}</td>
            </tr>
            <tr>
                <td>7.</td>
                <td>{{ $bahasa == 'en' ? 'Special Responsibility Allowance' : 'Tunjangan Struktural' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->tjstruk,2,',','.') }}</td>
            </tr>
            <tr>
                <td>8.</td>
                <td>{{ $bahasa == 'en' ? 'Functional Responsibility Allowance' : 'Tunjangan Fungsional' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->tjfungs,2,',','.') }}</td>
            </tr>
            <tr>
                <td>9.</td>
                <td>{{ $bahasa == 'en' ? 'Rounding' : 'Pembulatan' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->pembul,2,',','.') }}</td>
            </tr>
            <tr>
                <td>10.</td>
                <td>{{ $bahasa == 'en' ? 'Food Allowance' : 'Tunjangan Beras' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->tjberas,2,',','.') }}</td>
            </tr>
            <tr>
                <td>11.</td>
                <td>{{ $bahasa == 'en' ? 'Income Tax Allowance' : 'Tunjangan Khusus PPh' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->tjpph,2,',','.') }}</td>
            </tr>
            <tr>
                <td>12.</td>
                <td>{{ $bahasa == 'en' ? 'Lecturer Allowance' : 'Tunjangan Profesi Dosen' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($tunjangan_profesi ? $tunjangan_profesi->diterimakan : 0,2,',','.') }}</td>
            </tr>
            <tr>
                <td>13.</td>
                <td>{{ $bahasa == 'en' ? 'Professor Allowance' : 'Tunjangan Kehormatan Profesor' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($tunjangan_kehormatan_profesor ? $tunjangan_kehormatan_profesor->diterimakan : 0,2,',','.') }}</td>
            </tr>
            <tr>
                <td>14.</td>
                <td>{{ $bahasa == 'en' ? 'Additional Allowance' : 'Tunjangan Lain-Lain' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($slip_gaji->additional_allowance,2,',','.') }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td colspan="3" class="border-top"></td>
            </tr>
            <tr class="bottom">
                <td colspan="2"><b>{{ $bahasa == 'en' ? 'Gross Earnings' : 'Jumlah penerimaan gaji kotor' }}</b></td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gross_earnings,2,',','.') }}</td>
            </tr>
        </table>
        <br>
        <b>{{ $bahasa == 'en' ? 'Cuts' : 'Potongan' }}:</b>
        <table border="0" width="100%" class="amount">
            <tr>
                <td width="15">a.</td>
                <td>{{ $bahasa == 'en' ? 'Deductions' : 'Iuran Wajib' }}</td>
                <td width="5">:</td>
                <td width="30">{{ $mata_uang }}</td>
                <td width="100">{{ number_format($gaji_induk->potpfk10,2,',','.') }}</td>
            </tr>
            <tr>
                <td>b.</td>
                <td>{{ $bahasa == 'en' ? 'Medical Insurance' : 'BPJS' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->bpjs,2,',','.') }}</td>
            </tr>
            <tr>
                <td>c.</td>
                <td>{{ $bahasa == 'en' ? 'Income Tax' : 'Pajak Penghasilan' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($gaji_induk->potpph,2,',','.') }}</td>
            </tr>
            <tr>
                <td>d.</td>
                <td>{{ $bahasa == 'en' ? 'House Savings' : 'Tabungan Perumahan' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format(0,2,',','.') }}</td>
            </tr>
            <tr>
                <td>e.</td>
                <td>{{ $bahasa == 'en' ? 'Food Deductions' : 'Lain-lain' }}</td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format(0,2,',','.') }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td colspan="3" class="border-top"></td>
            </tr>
            <tr class="bottom">
                <td colspan="2"><b>{{ $bahasa == 'en' ? 'Salary Cuts' : 'Jumlah potongan' }}</b></td>
                <td>:</td>
                <td>{{ $mata_uang }}</td>
                <td>{{ number_format($salary_cuts,2,',','.') }}</td>
            </tr>
        </table>
        <br>
        <table border="0" width="100%" class="amount">
            <tr>
                <td></td>
                <td></td>
                <td colspan="3" class="border-top"></td>
            </tr>
            <tr class="bottom">
                <td colspan="2"><b>{{ $bahasa == 'en' ? 'Net Salary' : 'Penerimaan gaji bersih (THP) / yang dibayarkan' }}</b></td>
                <td width="5">:</td>
                <td width="30">{{ $mata_uang }}</td>
                <td width="100">{{ number_format($gross_earnings - $salary_cuts,2,',','.') }}</td>
            </tr>
        </table>
        @if($bahasa == 'id')
        <br>
        <p>Demikian surat keterangan ini dibuat untuk keperluan sebagaimana mestinya.</p>
        @endif
        <br><br><br>
        <table id="sign" width="100%">
            <tr>
                <td width="60%"></td>
                <td width="40%">
                    Semarang, {{ $bahasa == 'en' ? date('d', strtotime($slip_gaji->tanggal)).' '.$bulan_english.' '.date('Y', strtotime($slip_gaji->tanggal)) : \Ajifatur\Helpers\DateTimeExt::full(date('Y-m-d', strtotime($slip_gaji->tanggal))) }}
                    <br>
                    {{ $bahasa == 'en' ? 'Expenditure Treasury' : 'Bendahara Pengeluaran' }},
                    <br><br><br><br><br>
                    Daru Lestariningsih, S.E.
                    <br>
                    NIP. 198508032020122003
                </td>
            </tr>
        </table>
    </div>
</body>
</html>