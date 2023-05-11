<?php

include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsistatement.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';

session_start();

$idUser    = '';
$tokenCSRF = '';

extract($_SESSION);

//DESKRIPSI ID USER
$idUserAsli = dekripsi($idUser, $kunciRahasia);

//MENGECEK APAKAH ID USER YANG LOGIN ADA PADA DATABASE
$sqlCekUser = $db->prepare('SELECT idUser from balistars_user where idUser=?');
$sqlCekUser->execute([$idUserAsli]);
$dataCekUser = $sqlCekUser->fetch();

//MENGECEK DATA LOGIN PEGAWAI
$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
    $idUserAsli,
    'form_penyetujuan_additional'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    extract($_POST);

    $idJabatan = intval($dataLogin['idJabatan']);
    $idPegawai = intval($dataLogin['idPegawai']);
    $area = $dataLogin['area'];

    $idCabangCakupan = [];

    if ($idJabatan === 9) {
        $tahapan = ['Kontrol Area', 'Reject Dari Headoffice'];

        $dataCabang = selectStatement(
            $db,
            'SELECT idCabang FROM balistars_cabang WHERE area = ?',
            [$area],
        );

        $idCabangCakupan = array_column($dataCabang, 'idCabang');
    } else if ($idJabatan === 1) {
        if ($idPegawai === 164) {
            $tahapan = ['Pak Swi'];
        } else {
            $tahapan = [];
        }
    } else if ($idJabatan === 2) {
        $tahapan = ['Headoffice'];
    } else {
        $tahapan = [];
    }

?>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center">NO</th>
                <th class="text-center">AKSI</th>
                <th class="text-center">CUSTOMER</th>
                <th class="text-center">TGL</th>
                <th class="text-center">BIAYA</th>
                <th class="text-center">OMSET</th>
                <th class="text-center">PROFIT</th>
                <th class="text-center">RATIO</th>
                <th class="text-center">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $tanggal = explode(' - ', $rentang);
            if (empty($tahapan)) {
            ?>
                <tr>
                    <td class="text-center table-active" colspan="9"><i class="fas fa-exclamation-circle pr-4"></i><strong>MAAF, ANDA TIDAK MEMILIKI AKSES UNTUK MELAKUKAN PENYETUJUAN</strong></td>
                </tr>
                <?php
            } else {

                if (isset($tanggal[0]) && isset($tanggal[1])) {

                    $questionMarkTahapan = join(',', array_fill(0, count($tahapan), '?'));

                    if ($tahapan === ['Kontrol Area', 'Reject Dari Headoffice']) {
                        $questionMark = join(',', array_fill(0, count($idCabangCakupan), '?'));
                        $parameter['cabang'] = "AND idCabang IN ({$questionMark})";
                    } else {
                        $parameter['cabang'] = '';
                    }

                    $tanggalAwal = konversiTanggal($tanggal[0]);
                    $tanggalAkhir = konversiTanggal($tanggal[1]);

                    switch ($status) {
                        case 'Belum Diproses':
                            $parameter['status'] = 'AND data_penyetujuan.idPenyetujuan IS NULL';
                            break;
                        case 'Sudah Diproses':
                            $parameter['status'] = 'AND data_penyetujuan.idPenyetujuan IS NOT NULL';
                            break;
                        case 'Reject':
                            $parameter['status'] = 'AND data_penyetujuan.idPenyetujuan IS NOT NULL AND data_penyetujuan.hasil = \'Reject\'';
                            break;

                        default:
                            $parameter['status'] = '';
                            break;
                    }

                    $dataAdditional = selectStatement(
                        $db,
                        "SELECT 
                            balistars_pengajuan_additional.*
                        FROM 
                            balistars_pengajuan_additional
                            LEFT JOIN (
                                SELECT
                                    *
                                FROM
                                    balistars_penyetujuan
                                WHERE
                                    jenisPengajuan = ?
                                    AND statusPenyetujuan = ?
                                    AND idUserPenyetuju = ?
                                    AND tahapan IN ({$questionMarkTahapan})
                            ) data_penyetujuan ON balistars_pengajuan_additional.idAdditional = data_penyetujuan.idPengajuan
                        WHERE 
                            balistars_pengajuan_additional.statusAdditional = ?
                            {$parameter['status']}
                            AND (balistars_pengajuan_additional.tglPengajuan BETWEEN ? AND ?)
                            {$parameter['cabang']}
                    ",
                        array_merge(
                            [
                                'Additional', 'Aktif', $idUserAsli,
                            ],
                            $tahapan,
                            [
                                'Aktif',  $tanggalAwal, $tanggalAkhir
                            ],
                            $idCabangCakupan
                        )
                    );

                    if (count($dataAdditional) === 0) {
                ?>
                        <tr>
                            <td class="text-center table-active" colspan="9"><i class="fas fa-info-circle pr-4"></i><strong>DATA TIDAK DITEMUKAN</strong></td>
                        </tr>
                        <?php
                    } else {
                        $n = 1;
                        foreach ($dataAdditional as $row) {
                        ?>
                            <tr>
                                <td class="text-center"><?= $n ?></td>
                                <td class="text-center" class="align-middle">
                                    <button type="button" class="btn btn-info" onclick="getFormAdditional('<?= $row['idAdditional'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php
                                    if (in_array('Pak Swi', $tahapan)) {
                                    ?>
                                        <button type="button" class="btn btn-success" onclick="prosesPenyetujuan($(this), '<?= $row['idAdditional'] ?>', 'Disetujui')">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td class="text-center"><?= $row['namaCustomer'] ?></td>
                                <td class="text-center"><?= ubahTanggalIndo($row['tglPengajuan']) ?></td>
                                <td class="text-right">Rp <?= ubahToRp($row['biaya']) ?></td>
                                <td class="text-right">Rp <?= ubahToRp($row['omset']) ?></td>
                                <td class="text-right">Rp <?= ubahToRp($row['profit']) ?></td>
                                <td class="text-right"><?= $row['ratio'] ?></td>
                                <td class="text-center">
                                    <?php
                                    $dataFeedback = selectStatement(
                                        $db,
                                        "SELECT
                                            tahapan,
                                            lamaWaktu
                                        FROM
                                            balistars_penyetujuan
                                        WHERE
                                            idPengajuan = ?
                                            AND jenisPengajuan = ?
                                            AND statusPenyetujuan = ?
                                            AND tahapan IN ({$questionMarkTahapan})
                                            AND idUserPenyetuju = ?
                                    ",
                                        array_merge([
                                            $row['idAdditional'],
                                            'Additional',
                                            'Aktif',
                                        ], $tahapan, [$idUserAsli])
                                    );

                                    if (count($dataFeedback) === 0) {
                                        $status = 'info';
                                    } else {
                                        $poin = array_map(function ($tahapan, $lamaWaktu) {
                                            if (in_array($tahapan, ['Kontrol Area', 'Headoffice', 'Payment'])) {
                                                return poinPengajuan($tahapan, timeInMinutes($lamaWaktu));
                                            }
                                        }, array_column($dataFeedback, 'tahapan'), array_column($dataFeedback, 'lamaWaktu'));

                                        $poin = array_filter($poin, function ($nilai) {
                                            return !is_null($nilai);
                                        });

                                        $average = array_sum($poin) / count($poin);
                                        $status = statusAveragePoin($average);
                                    }

                                    ?>
                                    <button type="button" class="btn btn-<?= $status ?>" onclick="showProgressAdditional('<?= $row['idAdditional'] ?>')"><strong>CEK STATUS</strong></button>
                                </td>
                            </tr>
            <?php
                            $n++;
                        }
                    }
                }
            }
            ?>
        </tbody>
    </table>

<?php
}
