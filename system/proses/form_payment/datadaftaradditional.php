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
    'form_payment'
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
                <th class="text-center">CUSTOMER</th>
                <th class="text-center">TGL</th>
                <th class="text-center">OMSET</th>
                <th class="text-center">BIAYA</th>
                <th class="text-center">PROFIT</th>
                <th class="text-center">RATIO</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $tanggal = explode(' - ', $rentang);

            if (isset($tanggal[0]) && isset($tanggal[1])) {

                switch ($status) {
                    case 'Belum Di Proses':
                        $parameter['tahapan'] = "AND tahapan = 'Payment'";
                        break;
                    case 'Sudah Di Proses':
                        $parameter['tahapan'] = "AND tahapan = 'Final'";
                        break;
                    default:
                        $parameter['tahapan'] = "AND tahapan IN ('Payment','Final')";
                        break;
                }


                [$tanggalAwal, $tanggalAkhir] = $tanggal;

                $dataAdditional = selectStatement(
                    $db,
                    "SELECT 
                        * 
                    FROM 
                        balistars_pengajuan_additional
                    WHERE 
                        statusAdditional = ?
                        {$parameter['tahapan']}
                        AND (tglPengajuan BETWEEN ? AND ?)
                    ",
                    array_merge(['Aktif', $tanggalAwal, $tanggalAkhir])
                );

                if (count($dataAdditional) === 0) {
            ?>
                    <tr>
                        <td class="text-center table-active" colspan="8"><i class="fas fa-info-circle pr-4"></i><strong>DATA TIDAK DITEMUKAN</strong></td>
                    </tr>
                    <?php
                } else {
                    $n = 1;
                    foreach ($dataAdditional as $row) {
                    ?>
                        <tr>
                            <td class="text-center"><?= $n ?></td>
                            <td class="text-center" class="align-middle">
                                <button type="button" class="btn btn-warning" onclick="showFormPengajuan('Additional','<?= $row['idAdditional'] ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php
                                if ($row['tahapan'] === 'Payment') {
                                ?>
                                    <button type="button" class="btn btn-success" onclick="getFormPayment('Additional','<?= $row['idAdditional'] ?>')">
                                        <i class="fas fa-calendar-check"></i>
                                    </button>
                                <?php
                                } else {
                                ?>
                                    <button type="button" class="btn btn-info" onclick="getFormPayment('Additional','<?= $row['idAdditional'] ?>')">
                                        <i class="fas fa-calendar-check"></i>
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
