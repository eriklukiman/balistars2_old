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
    'form_pengajuan_petty_cash'
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
                <th class="text-center">NAMA PROYEK</th>
                <th class="text-center">NAMA PERUSAHAAN</th>
                <th class="text-center">TGL</th>
                <th class="text-center">ESTIMASI OMSET</th>
                <th class="text-center">ESTIMASI PENGELUARAN</th>
                <th class="text-center">NILAI PETTY CASH</th>
                <th class="text-center">NO. PO</th>
                <th class="text-center">KET</th>
                <th class="text-center">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $tanggal = explode(' - ', $rentang);

            if (isset($tanggal[0]) && isset($tanggal[1])) {

                $tanggalAwal = konversiTanggal($tanggal[0]);
                $tanggalAkhir = konversiTanggal($tanggal[1]);

                switch ($status) {
                    case 'Belum Diproses':
                        $parameter['status'] = 'AND data_penyetujuan.totalData = 0';
                        break;
                    case 'Sudah Diproses':
                        $parameter['status'] = 'AND data_penyetujuan.totalData > 0';
                        break;
                    case 'Reject':
                        $parameter['status'] = 'AND data_penyetujuan.totalData > 0 AND balistars_pengajuan_petty_cash.tahapan = \'Reject\'';
                        break;

                    default:
                        $parameter['status'] = '';
                        break;
                }

                $dataPettyCash = selectStatement(
                    $db,
                    "SELECT 
                        balistars_pengajuan_petty_cash.*
                    FROM 
                        balistars_pengajuan_petty_cash
                        LEFT JOIN (
                            SELECT
                                COUNT(idPenyetujuan) as totalData,
                                idPengajuan
                            FROM
                                balistars_penyetujuan
                            WHERE
                                jenisPengajuan = ?
                                AND statusPenyetujuan = ?
                                GROUP BY idPengajuan
                        ) data_penyetujuan ON balistars_pengajuan_petty_cash.idPettyCash = data_penyetujuan.idPengajuan
                    WHERE 
                        balistars_pengajuan_petty_cash.statusPettyCash = ?
                        {$parameter['status']}
                        AND balistars_pengajuan_petty_cash.idCabang = ?
                        AND (balistars_pengajuan_petty_cash.tglPengajuan BETWEEN ? AND ?)
                    ",
                    array_merge(['Petty Cash', 'Aktif', 'Aktif', $dataLogin['idCabang'], $tanggalAwal, $tanggalAkhir])
                );

                if (count($dataPettyCash) === 0) {
            ?>
                    <tr>
                        <td class="text-center table-active" colspan="11"><i class="fas fa-info-circle pr-4"></i><strong>DATA TIDAK DITEMUKAN</strong></td>
                    </tr>
                    <?php
                } else {
                    $n = 1;
                    foreach ($dataPettyCash as $row) {
                    ?>
                        <tr>
                            <td class="text-center"><?= $n ?></td>
                            <td class="text-center" class="align-middle">
                                <?php
                                if ($row['tahapan'] === 'Headoffice' || $row['tahapan'] === 'Reject') {
                                ?>
                                    <button type="button" class="btn btn-info" onclick="getFormPettyCash('<?= $row['idPettyCash'] ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php
                                    if ($row['tahapan'] === 'Headoffice') {
                                    ?>
                                        <button type="button" class="btn btn-danger" onclick="cancelPettyCash($(this),'<?= $row['idPettyCash'] ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <button type="button" class="btn btn-info" onclick="getFormPettyCash('<?= $row['idPettyCash'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php
                                }
                                ?>
                            </td>
                            <td class="text-center"><?= $row['namaProyek'] ?></td>
                            <td class="text-center"><?= $row['namaPerusahaan'] ?></td>
                            <td class="text-center"><?= ubahTanggalIndo($row['tglPengajuan']) ?></td>
                            <td class="text-right">Rp <?= ubahToRp($row['estimasiOmset']) ?></td>
                            <td class="text-right">Rp <?= ubahToRp($row['estimasiBiayaPengeluaran']) ?></td>
                            <td class="text-right">Rp <?= ubahToRp($row['nominal']) ?></td>
                            <td class="text-right"><?= $row['noPO'] ?></td>
                            <td class="text-right"><?= wordwrap($row['keterangan'], 25, '<br/>') ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-info" onclick="showProgressPettyCash('<?= $row['idPettyCash'] ?>')"><strong>CEK STATUS</strong></button>
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
