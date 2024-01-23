<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="50"><b>Hal Surat</b></th>
            <th align="center" width="50"><b>Pembuka</b></th>
            <th align="center" width="50"><b>Isi Surat</b></th>
            <th align="center" width="50"><b>Penutup</b></th>
            <th align="center" width="20"><b>NIP</b></th>
            <th align="center" width="10"><b>Kode Hirarki</b></th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['spkgb'] as $s)
            <tr>
                <td>SPKGB {{ \Ajifatur\Helpers\DateTimeExt::month($data['bulan']) }} {{ $data['tahun'] }} a.n. {{ strtoupper($s->pegawai->nama) }}</td>
                <td>Diberitahukan dengan hormat, bahwa sehubungan dengan telah dipenuhinya masa kerja dan syarat-syarat lainnya kepada:</td>
                <td>(atas dasar SK terakhir tentang gaji pokok/pangkat terakhir yang ditetapkan):</td>
                <td>diberikan kenaikan gaji berkala hingga memperoleh:</td>
                <td>{{ $s->pegawai->nip }}</td>
                <td>25</td>
            </tr>
        @endforeach
    </tbody>
</table>