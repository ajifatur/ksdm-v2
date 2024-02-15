<table border="1" style="width: 100%">
    <thead>
        <tr>
            <th align="center" width="5"><b>NO</b></th>
            <th align="center" width="25"><b>NIP</b></th>
            <th align="center" width="60"><b>NAMA SUPPLIER</b></th>
            <th align="center" width="60"><b>NAMA PEMILIK REKENING</b></th>
            <th align="center" width="25"><b>NO REKENING</b></th>
            <th align="center" width="15"><b>TUNJANGAN</b></th>
            <th align="center" width="15"><b>PPH</b></th>
            <th align="center" width="15"><b>DITERIMAKAN</b></th>
            <th align="center" width="10"><b>BULAN</b></th>
            <th align="center" width="10"><b>TAHUN</b></th>
            <th align="center" width="20"><b>JENIS</b></th>
            <th align="center" width="20"><b>ANGKATAN</b></th>
            <th align="center" width="20"><b>UNIT</b></th>
            <th align="center" width="20"><b>KETERANGAN</b></th>

        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach($data['tunjangan'] as $key=>$d)
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $d->pegawai->npu != null ? $d->pegawai->npu : $d->pegawai->nip }}</td>
                <td>{{ $d->nama }}</td>
                <td>{{ $d->nama_rekening }}</td>
                <td>{{ $d->nomor_rekening }}</td>
                <td>{{ $d->tunjangan }}</td>
                <td>{{ $d->pph }}</td>
                <td>{{ $d->diterimakan }}</td>
                <td>{{ $d->bulan }}</td>
                <td>{{ $d->tahun }}</td>
                <td>{{ $d->angkatan->jenis->nama }}</td>
                <td>{{ $d->angkatan->nama }}</td>
                <td>{{ $d->unit->nama }}</td>
                <td></td>
            </tr>
            @php $i++; @endphp
        @endforeach
        @foreach($data['kekurangan'] as $key=>$d)
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $d->pegawai->npu != null ? $d->pegawai->npu : $d->pegawai->nip }}</td>
                <td>{{ $d->nama }}</td>
                <td>{{ $d->nama_rekening }}</td>
                <td>{{ $d->nomor_rekening }}</td>
                <td>{{ $d->detail->sum('tunjangan') }}</td>
                <td>{{ $d->detail->sum('pph') }}</td>
                <td>{{ $d->detail->sum('diterimakan') }}</td>
                <td>{{ $d->bulan }}</td>
                <td>{{ $d->tahun }}</td>
                <td>{{ $d->angkatan->jenis->nama }}</td>
                <td>{{ $d->angkatan->nama }}</td>
                <td>{{ $d->unit->nama }}</td>
                <td>Kekurangan</td>
            </tr>
            @php $i++; @endphp
        @endforeach
    </tbody>
</table>