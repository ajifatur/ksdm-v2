<table border="1" style="width: 100%">
    <!-- Head -->
    <tr>
        <td align="center" valign="middle" colspan="16" height="16"><b>DAFTAR PEMBAYARAN GAJI PEGAWAI TIDAK TETAP TENAGA KEPENDIDIKAN</b></td>
    </tr>
    <tr>
        <td align="center" valign="middle" colspan="16" height="16"><b>UNIVERSITAS NEGERI SEMARANG</b></td>
    </tr>
    <tr>
        <td align="center" valign="middle" colspan="16" height="16"><b>JANUARI 2024</b></td>
    </tr>

    <tr>
        <td colspan="16" height="16"></td>
    </tr>

    <tr>
        <td align="center" valign="middle" width="8" height="60"><b>No</b></td>
        <td align="center" valign="middle" width="35"><b>Nama/NIK/NPWP</b></td>
        <td align="center" valign="middle" width="20"><b>NRP/NPU</b></td>
        <td align="center" valign="middle" width="16"><b>Status Kawin / Status Pajak</b></td>
        <td align="center" valign="middle" width="16"><b>No &amp; Tanggal Perjanjian Kerja</b></td>
        <td align="center" valign="middle" width="14" bgcolor="#00B050"><b>Gaji Pokok</b></td>
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
        <td align="center" valign="middle"><em>7</em></td>
        <td align="center" valign="middle"><em>8</em></td>
        <td align="center" valign="middle"><em>9</em></td>
        <td align="center" valign="middle"><em>10 = 6+7+8+9</em></td>
        <td align="center" valign="middle"><em>11</em></td>
        <td align="center" valign="middle"><em>12</em></td>
        <td align="center" valign="middle"><em>13 = 8+11</em></td>
        <td align="center" valign="middle"><em>14 = 9+12</em></td>
        <td align="center" valign="middle"><em>15 = 10-13-14</em></td>
        <td align="center" valign="middle"><em>16</em></td>
    </tr>

    @foreach($data as $key=>$d)
    <tr>
        <td align="center" valign="middle" height="45">{{ $key+1 }}</td>
        <td valign="middle">{{ strtoupper($d->nama) }}</td>
        <td valign="middle">{{ $d->nip }}</td>
        <td></td>
        <td></td>
        <td align="right" valign="middle">{{ rand(1000000,9999999) }}</td>
    </tr>
    @endforeach
</table>