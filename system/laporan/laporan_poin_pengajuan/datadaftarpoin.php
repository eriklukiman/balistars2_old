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
$sqlCekMenu = $db->prepare(
    'SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?'
);
$sqlCekMenu->execute([
    $idUserAsli,
    'laporan_poin_pengajuan'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    extract($_POST);

?>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center">NO</th>
                <th class="text-center">NAMA PEGAWAI</th>
                <th class="text-center">POIN PENGAJUAN</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $tanggal = explode(' - ', $rentang);



            if (isset($tanggal[0]) && isset($tanggal[1])) {

                [$tanggalAwal, $tanggalAkhir] = $tanggal;

                $dataPengembalian = selectStatement(
                    $db,
                    "SELECT
                            balistars_pegawai.namaPegawai,
                            SEC_TO_TIME(COALESCE(data_kontrol_area.avg,0)) as avgKontrolArea,
                            SEC_TO_TIME(COALESCE(data_headoffice.avg,0)) as avgHeadoffice,
                            SEC_TO_TIME(COALESCE(data_payment.avg,0)) as avgPayment
                        FROM
                            balistars_pegawai
                            LEFT JOIN (
                                SELECT
                                    balistars_user.idPegawai,
                                    ROUND(AVG(TIME_TO_SEC(balistars_penyetujuan.lamaWaktu))) as avg
                                FROM
                                    balistars_penyetujuan
                                    INNER JOIN balistars_user ON balistars_penyetujuan.idUserPenyetuju = balistars_user.idUser
                                WHERE
                                    balistars_penyetujuan.statusPenyetujuan = ?
                                    AND balistars_penyetujuan.tahapan = ?
                                    AND (DATE(DATE_FORMAT(balistars_penyetujuan.timeStamp, '%Y-%m-%d')) BETWEEN ? AND ?)
                                    GROUP BY balistars_penyetujuan.idUserPenyetuju
                            ) data_kontrol_area ON balistars_pegawai.idPegawai = data_kontrol_area.idPegawai
                            LEFT JOIN (
                                SELECT
                                    balistars_user.idPegawai,
                                    ROUND(AVG(TIME_TO_SEC(balistars_penyetujuan.lamaWaktu))) as avg
                                FROM
                                    balistars_penyetujuan
                                    INNER JOIN balistars_user ON balistars_penyetujuan.idUserPenyetuju = balistars_user.idUser
                                WHERE
                                    balistars_penyetujuan.statusPenyetujuan = ?
                                    AND balistars_penyetujuan.tahapan = ?
                                    AND (DATE(DATE_FORMAT(balistars_penyetujuan.timeStamp, '%Y-%m-%d')) BETWEEN ? AND ?)
                                    GROUP BY balistars_penyetujuan.idUserPenyetuju
                            ) data_headoffice ON balistars_pegawai.idPegawai = data_headoffice.idPegawai
                            LEFT JOIN (
                                SELECT
                                    balistars_user.idPegawai,
                                    ROUND(AVG(TIME_TO_SEC(balistars_payment.lamaWaktu))) as avg
                                FROM
                                    balistars_payment
                                    INNER JOIN balistars_user ON balistars_payment.idUser = balistars_user.idUser
                                WHERE
                                    balistars_payment.statusPayment = ?
                                    AND (DATE(balistars_payment.tanggal) BETWEEN ? AND ?)
                                    GROUP BY balistars_payment.idUser
                            ) data_payment ON balistars_pegawai.idPegawai = data_payment.idPegawai
                        ",
                    array_merge(['Aktif', 'Kontrol Area', $tanggalAwal, $tanggalAkhir], ['Aktif', 'Headoffice', $tanggalAwal, $tanggalAkhir], ['Aktif', $tanggalAwal, $tanggalAkhir])
                );

                if (count($dataPengembalian) === 0) {
            ?>
                    <tr>
                        <td class="text-center table-active" colspan="7"><i class="fas fa-info-circle pr-4"></i><strong>DATA TIDAK DITEMUKAN</strong></td>
                    </tr>
                    <?php
                } else {
                    $n = 1;
                    foreach ($dataPengembalian as $row) {
                    ?>
                        <tr>
                            <td class="text-center"><?= $n ?></td>
                            <td class="text-center"><?= $row['namaPegawai'] ?></td>
                            <td class="text-center">
                                <?php
                                if ($row['avgKontrolArea'] !== '00:00:00') {
                                    $poinKontrolArea = poinPengajuan('Kontrol Area', timeInMinutes($row['avgKontrolArea']));
                                    $statusKontrolArea = statusAveragePoin($poinKontrolArea);
                                ?>
                                    <button class="btn btn-<?= $statusKontrolArea ?>" type="button"><strong>KONTROL AREA : <?= $poinKontrolArea ?> POIN</strong> <i>( ~ <?= $row['avgKontrolArea']; ?> )</i></button>
                                <?php
                                }

                                if ($row['avgHeadoffice'] !== '00:00:00') {
                                    $poinHeadoffice = poinPengajuan('Headoffice', timeInMinutes($row['avgHeadoffice']));
                                    $statusHeadoffice = statusAveragePoin($poinHeadoffice);
                                ?>
                                    <button class="btn btn-<?= $statusHeadoffice ?>" type="button"><strong>HEADOFFICE : <?= $poinHeadoffice ?> POIN</strong> <i>( ~ <?= $row['avgHeadoffice']; ?> )</i></button>
                                <?php
                                }

                                if ($row['avgPayment'] !== '00:00:00') {
                                    $poinPayment = poinPengajuan('Payment', timeInMinutes($row['avgPayment']));
                                    $statusPayment = statusAveragePoin($poinPayment);
                                ?>
                                    <button class="btn btn-<?= $statusPayment ?>" type="button"><strong>PAYMENT : <?= $poinPayment ?> POIN</strong> <i>( ~ <?= $row['avgPayment']; ?> )</i></button>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
            <?php
                        $n++;
                    }
                }
            }

            ?>
        </tbody>
    </table>

<?php
}
