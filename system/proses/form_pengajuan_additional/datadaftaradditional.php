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
    'form_pengajuan_additional'
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
                <th class="text-center">AKSI</th>
                <th class="text-center">PROJECT</th>
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

            if (isset($tanggal[0]) && isset($tanggal[1])) {

                $tanggalAwal = konversiTanggal($tanggal[0]);
                $tanggalAkhir = konversiTanggal($tanggal[1]);

                $dataAdditional = selectStatement(
                    $db,
                    "SELECT 
                        balistars_pengajuan_additional.*
                    FROM 
                        balistars_pengajuan_additional
                    WHERE 
                        balistars_pengajuan_additional.statusAdditional = ?
                        AND (balistars_pengajuan_additional.tglPengajuan BETWEEN ? AND ?)
                        AND balistars_pengajuan_additional.idCabang = ?
                ",
                    array_merge(
                        [
                            'Aktif',  $tanggalAwal, $tanggalAkhir, $dataLogin['idCabang']
                        ],
                    )
                );

                $n = 1;

                $isDataDisplayed = false;

                foreach ($dataAdditional as $row) {
                    $skip = false;

                    switch ($status) {
                        case 'Belum Diproses':
                            $cekHasil = selectStatement(
                                $db,
                                'SELECT COUNT(*) as cek FROM balistars_penyetujuan WHERE idPengajuan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ?',
                                [$row['idAdditional'], 'Additional', 'Aktif'],
                                'fetch'
                            )['cek'];

                            if (intval($cekHasil) === 0) {
                                $skip = false;
                            } else {
                                $skip = true;
                            }
                            break;
                        case 'Sudah Diproses':
                            $cekHasil = selectStatement(
                                $db,
                                'SELECT COUNT(*) as cek FROM balistars_penyetujuan WHERE idPengajuan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ?',
                                [$row['idAdditional'], 'Additional', 'Aktif'],
                                'fetch'
                            )['cek'];

                            if (intval($cekHasil) > 0) {
                                $skip = false;
                            } else {
                                $skip = true;
                            }

                            break;

                        default:
                            $skip = false;
                            break;
                    }


                    if ($skip) continue;

                    $isDataDisplayed = true || $isDataDisplayed;
            ?>
                    <tr>
                        <td class="text-center"><?= $n ?></td>
                        <td class="text-center" class="align-middle">
                            <?php
                            if ($row['tahapan'] === 'Kontrol Area' || $row['tahapan'] === 'Reject') {
                                if ($row['tahapan'] === 'Reject') {
                                    $editStatus = 'danger';
                                } else {
                                    $editStatus = 'info';
                                }
                            ?>
                                <button type="button" class="btn btn-<?= $editStatus ?>" onclick="getFormAdditional('<?= $row['idAdditional'] ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php
                                if ($row['tahapan'] === 'Kontrol Area') {
                                ?>
                                    <button type="button" class="btn btn-danger" onclick="cancelAdditional($(this),'<?= $row['idAdditional'] ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php
                                }
                            } else {
                                ?>
                                <button type="button" class="btn btn-info" onclick="getFormAdditional('<?= $row['idAdditional'] ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            <?php
                            }
                            ?>
                        </td>
                        <td class="text-center"><?= $row['namaCustomer'] ?></td>
                        <td class="text-center"><?= ubahTanggalIndo($row['tglPengajuan']) ?></td>
                        <td class="text-right">Rp <?= ubahToRp($row['omset']) ?></td>
                        <td class="text-right">Rp <?= ubahToRp($row['biaya']) ?></td>
                        <td class="text-right">Rp <?= ubahToRp($row['profit']) ?></td>
                        <td class="text-center"><?= cekDesimal($row['ratio']) ?>%</td>
                        <td class="text-center">
                            <?php
                            if ($row['tahapan'] === 'Final') {
                                $dataFeedback = selectStatement(
                                    $db,
                                    'SELECT 
                                            * 
                                        FROM 
                                            (
                                                (
                                                    SELECT
                                                        lamaWaktu,
                                                        tahapan
                                                    FROM
                                                        balistars_penyetujuan
                                                    WHERE
                                                        idPengajuan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ?
                                                )
                                                UNION ALL
                                                (
                                                    SELECT
                                                        lamaWaktu,
                                                        \'Payment\' as tahapan
                                                    FROM    
                                                        balistars_payment
                                                    WHERE
                                                        idPengajuan = ? AND jenisPengajuan = ? AND statusPayment = ?
                                                )
                                            ) data_feedback
                                            ',
                                    [
                                        $row['idAdditional'], 'Additional', 'Aktif',
                                        $row['idAdditional'], 'Additional', 'Aktif'
                                    ],
                                );

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

                            ?>
                                <button type="button" class="btn btn-<?= $status ?>" onclick="showProgressAdditional('<?= $row['idAdditional'] ?>')"><strong>CEK STATUS</strong></button>
                            <?php
                            } else {
                            ?>
                                <button type="button" class="btn btn-info" onclick="showProgressAdditional('<?= $row['idAdditional'] ?>')"><strong>CEK STATUS</strong></button>
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
                <?php
                    $n++;
                }
                if ($isDataDisplayed === false) {
                ?>
                    <tr>
                        <td class="text-center table-active" colspan="9"><i class="fas fa-info-circle pr-4"></i><strong>DATA TIDAK DITEMUKAN</strong></td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>

<?php
}
