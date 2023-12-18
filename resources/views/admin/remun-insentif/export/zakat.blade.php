<html>
<body>
    <table style="width: 100%">
        <tbody>
            <?php
                $i = 1;
            ?>
            @foreach($data['remun_insentif_dosen'] as $key=>$r)
                <tr>
                    <td align="center" width="10">{{ $i }}</td>
                    <td width="100">{{ strtoupper($r->pegawai->nama) }}<br>{{ $r->pegawai->nip }}</td>
                    <td align="right" width="20">{{ $r->pot_zakat }}</td>
                </tr>
                <?php
                    $i++;
                ?>
            @endforeach
            @foreach($data['remun_insentif_tendik'] as $key=>$r)
                <tr>
                    <td align="center" width="10">{{ $i }}</td>
                    <td width="100">{{ strtoupper($r->pegawai->nama) }}<br>{{ $r->pegawai->nip }}</td>
                    <td align="right" width="20">{{ $r->pot_zakat }}</td>
                </tr>
                <?php
                    $i++;
                ?>
            @endforeach
        </tbody>
    </table>
</body>
</html>