<html>
<head>
    <title>{{ $title }}</title>
    <style>
        #title {width: 100%; text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 25px; line-height: 20px;}
        table {border-collapse: collapse; font-size: 14px;}
        table thead tr th {border: #333 solid 2px; padding: 5px;}
        table tbody tr td {border: #333 solid 1px; padding: 5px;}
    </style>
</head>
<body>
    <div id="title">REKAPITULASI SURAT PEMBERITAHUAN KENAIKAN GAJI BERKALA (SPKGB)<br>(TMT: {{ strtoupper(\Ajifatur\Helpers\DateTimeExt::full($tanggal)) }})</div>
    <table style="width: 100%">
        <thead>
            <tr>
                <th align="center" width="20"><b>No</b></th>
                <th align="center"><b>NIP</b></th>
                <th align="center"><b>Nama</b></th>
                <th align="center"><b>KGB</b></th>
                <th align="center"><b>Unit</b></th>
            </tr>
        </thead>
        <tbody>
            @foreach($spkgb as $key=>$s)
                <tr>
                    <td align="center">{{ ($key+1) }}</td>
                    <td>{{ $s->pegawai->nip }}</td>
                    <td>{{ $s->pegawai->nama }}</td>
                    <td>{{ $s->mutasi->golru->nama }} {{ $s->mutasi->perubahan->mk_tahun }} tahun {{ $s->mutasi->perubahan->mk_bulan }} bulan</td>
                    <td>{{ $s->unit->nama }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>