<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="25"><b>NIP/NPWP</b></th>
            <th align="center" width="40"><b>NAMA</b></th>
            <th align="center" width="15"><b>BANK</b></th>
            <th align="center" width="15"><b>NOMINAL</b></th>
            <th align="center" width="15"><b>PPh21</b></th>
            <th align="center" width="15"><b>PPh26</b></th>
            <th align="center" width="15"><b>PPN</b></th>
            <th align="center" width="15"><b>Pot. Pihak ke-3</b></th>
            <th align="center" width="15"><b>BPJS Kesehatan</b></th>
            <th align="center" width="15"><b>BPJS Ketenagakerjaan</b></th>
            <th align="center" width="15"><b>Pot. Zakat</b></th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $d)
            <?php
                $lebih_kurang = \App\Models\LebihKurang::where('pegawai_id','=',$d->pegawai->id)->where('bulan_proses','=',Request::query('bulan'))->where('tahun_proses','=',Request::query('tahun'))->where('triwulan_proses','=',0)->where('kekurangan','=',0)->get();
                $dibayarkan = $d->remun_gaji + $lebih_kurang->sum('selisih');
            ?>
            <tr>
                <td>{{ $d->pegawai->nip }}</td>
                <td>{{ strtoupper($d->pegawai->nama) }}</td>
                <td>BNI</td>
                <td>{{ $dibayarkan }}</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>{{ $d->jabatan->zakat == 1 ? '2,5' : 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>