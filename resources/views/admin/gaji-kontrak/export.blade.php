<table border="1" style="width: 100%">
    <!-- Head -->
    <tr>
        <td align="center" valign="middle" colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}" height="16"><b>DAFTAR PEMBAYARAN GAJI PEGAWAI TIDAK TETAP {{ $data['kategori'] ? $data['kategori']->kategori == 1 ? 'TENAGA PENDIDIK' : 'TENAGA KEPENDIDIKAN' : '' }}</b></td>
    </tr>
    <tr>
        <td align="center" valign="middle" colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}" height="16"><b>UNIVERSITAS NEGERI SEMARANG</b></td>
    </tr>
    <tr>
        <td align="center" valign="middle" colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}" height="16"><b>{{ strtoupper(\Ajifatur\Helpers\DateTimeExt::month($data['bulan'])) }} {{ $data['tahun'] }}</b></td>
    </tr>

    <tr>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}" height="16"></td>
    </tr>

    <tr>
        <td align="center" valign="middle" width="8" height="60"><b>No</b></td>
        <td align="center" valign="middle" width="35"><b>Nama/NIK/NPWP</b></td>
        <td align="center" valign="middle" width="20"><b>NRP/NPU</b></td>
        <td align="center" valign="middle" width="16"><b>Status Kawin / Status Pajak</b></td>
        <td align="center" valign="middle" width="16"><b>No &amp; Tanggal Perjanjian Kerja</b></td>
        <td align="center" valign="middle" width="14" bgcolor="#00B050"><b>Gaji Pokok</b></td>
        @if(($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null)
        <td align="center" valign="middle" width="14" bgcolor="#00B050"><b>Tunjangan Dosen NIDK</b></td>
        @endif
        <td align="center" valign="middle" width="14" bgcolor="#00B050"><b>Tunjangan Lainnya</b></td>
        <td align="center" valign="middle" width="14" bgcolor="#00B050"><b>Tunjangan BPJS Kesehatan (4%)</b></td>
        <td align="center" valign="middle" width="14" bgcolor="#00B050"><b>Tunjangan BPJS Ketenagakerjaan</b></td>
        <td align="center" valign="middle" width="14"><b>Jumlah Penghasilan Kotor</b></td>
        <td align="center" valign="middle" width="14" bgcolor="#FFFF00"><b>Iuran BPJS Kesehatan (1%)</b></td>
        <td align="center" valign="middle" width="14" bgcolor="#FFFF00"><b>Iuran BPJS Ketenagakerjaan (3%)</b></td>
        <td align="center" valign="middle" width="14" bgcolor="#FFFF00"><b>Jumlah BPJS Kesehatan</b></td>
        <td align="center" valign="middle" width="14" bgcolor="#FFFF00"><b>Jumlah BPJS Ketenagakerjaan</b></td>
        <td align="center" valign="middle" width="14"><b>Jumlah Diterimakan</b></td>
        <td align="center" valign="middle" width="20" bgcolor="#FFFF00"><b>No Rekening</b></td>
    </tr>

    <tr>
        <td align="center" valign="middle" height="12"><em>1</em></td>
        <td align="center" valign="middle"><em>2</em></td>
        <td align="center" valign="middle"><em>3</em></td>
        <td align="center" valign="middle"><em>4</em></td>
        <td align="center" valign="middle"><em>5</em></td>
        <td align="center" valign="middle"><em>6</em></td>
        @if(($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null)
        <td align="center" valign="middle"><em>7</em></td>
        @endif
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 8 : 7 }}</em></td>
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 9 : 8 }}</em></td>
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 10 : 9 }}</em></td>
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? '11 = 6+7+8+9+10' : '10 = 6+7+8+9' }}</em></td>
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 12 : 11 }}</em></td>
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 13 : 12 }}</em></td>
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? '14 = 9+12' : '13 = 8+11' }}</em></td>
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? '15 = 10+13' : '14 = 9+12' }}</em></td>
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? '16 = 11-14-15' : '15 = 10-13-14' }}</em></td>
        <td align="center" valign="middle"><em>{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}</em></td>
    </tr>

    @foreach($data['gaji'] as $key=>$d)
    <tr>
        <td align="center" valign="middle" height="45">{{ $key+1 }}</td>
        <td valign="middle">{{ strtoupper($d->pegawai->nama) }}<br>{{ $d->pegawai->nik }}<br>{{ $d->pegawai->npwp }}</td>
        <td valign="middle">{{ $d->pegawai->npu != null ? $d->pegawai->npu : $d->pegawai->nip }}</td>
        <td align="center" valign="middle">{{ $d->status_kawin }}<br>{{ $d->status_pajak }}</td>
        <td valign="middle">{{ $d->sk->no_sk }}</td>
        <td align="right" valign="middle">{{ $d->gjpokok }}</td>
        @if(($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null)
        <td align="right" valign="middle">{{ $d->tjdosen }}</td>
        @endif
        <td align="right" valign="middle">{{ $d->tjlain }}</td>
        <td align="right" valign="middle">{{ $d->tjbpjskes4 }}</td>
        <td align="right" valign="middle">{{ $d->tjbpjsket }}</td>
        <td align="right" valign="middle">{{ $d->kotor }}</td>
        <td align="right" valign="middle">{{ $d->iuranbpjskes1 }}</td>
        <td align="right" valign="middle">{{ $d->iuranbpjsket3 }}</td>
        <td align="right" valign="middle">{{ $d->jmlbpjskes }}</td>
        <td align="right" valign="middle">{{ $d->jmlbpjsket }}</td>
        <td align="right" valign="middle">{{ $d->bersih }}</td>
        <td align="right" valign="middle">{{ $d->nomor_rekening }}</td>
    </tr>
    @endforeach

    <!-- Break -->
    <tr>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}"></td>
    </tr>
    <tr>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}"></td>
    </tr>

    <!-- TTD -->
    <tr>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 13 : 12 }}"></td>
        <td>Semarang, {{ \Ajifatur\Helpers\DateTimeExt::full(date('Y-m-d')) }}</td>
    </tr>
    <tr>
        <td></td>
        <td>Mengetahui,</td>
    </tr>
    <tr>
        <td></td>
        <td>Pejabat Pembuat Komitmen</td>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 11 : 10 }}"></td>
        <td>PPABP</td>
    </tr>
    <tr>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}"></td>
    </tr>
    <tr>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}"></td>
    </tr>
    <tr>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 17 : 16 }}"></td>
    </tr>
    <tr>
        <td></td>
        <td>Siti Mursidah, S.Pd., M.Si.</td>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 11 : 10 }}"></td>
        <td>Sonny Broma Migusti, S.E.</td>
    </tr>
    <tr>
        <td></td>
        <td>NIP. 197710262005022001</td>
        <td colspan="{{ ($data['kategori'] && $data['kategori']->kategori == 1) || $data['kategori'] == null ? 11 : 10 }}"></td>
        <td>NPU. 1985081220231021001</td>
    </tr>
</table>