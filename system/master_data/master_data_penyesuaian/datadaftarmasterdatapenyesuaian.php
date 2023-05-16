<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';

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

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
    $idUserAsli,
    'master_data_penyesuaian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_POST);

$tanggal = explode(' - ', $rentang);

if (isset($tanggal[0]) && isset($tanggal[1])) {

    $tanggalAwal = konversiTanggal($tanggal[0]);
    $tanggalAkhir = konversiTanggal($tanggal[1]);

    $sqlPenyesuaian  = $db->prepare(
        'SELECT 
            * 
        FROM 
            balistars_penyesuaian 
            INNER JOIN balistars_cabang on balistars_penyesuaian.idCabang=balistars_cabang.idCabang
        WHERE 
            statusPenyesuaian = ? 
            AND (tanggalPenyesuaian BETWEEN ? AND ?)
            ORDER BY tanggalPenyesuaian DESC'
    );
    $sqlPenyesuaian->execute(['Aktif', $tanggalAwal, $tanggalAkhir]);
    $dataPenyesuaian = $sqlPenyesuaian->fetchAll();

    $n = 1;
    foreach ($dataPenyesuaian as $row) {
?>
        <tr>
            <?php
            $disabled1  = '';
            $disabled2  = '';

            if ($dataCekMenu['tipeEdit'] == '0') {
                $disabled1 = 'style = "display: none;"';
            }
            if ($dataCekMenu['tipeDelete'] == '0') {
                $disabled2 = 'style = "display: none;"';
            }
            ?>
            <td class="align-middle align-center"><?= $n ?></td>
            <td class="align-middle align-center">
                <button type="button" title="Edit" class="btn btn-warning tombolEditPenyesuaian" style="color: white;" onclick="getFormPenyesuaian('<?= $row['idPenyesuaian'] ?>','<?= $row['jenisPenyesuaian'] ?>')" <?= $disabled1 ?>>
                    <i class="fa fa-edit"></i>
                </button>
                <button type="button" title="Hapus" class="btn btn-danger" onclick="cancelPenyesuaian('<?= $row['idPenyesuaian'] ?>')" <?= $disabled2 ?>>
                    <i class="fa fa-trash"></i>
                </button>
            </td>
            <td class="align-middle align-center"><?= wordwrap($row['jenisPenyesuaian'], 50, '<br>') ?></td>
            <td class="align-middle align-center"><?= wordwrap($row['tipePembayaran'], 50, '<br>') ?></td>
            <td class="align-middle align-center"><?= wordwrap($row['namaCabang'], 50, '<br>') ?></td>
            <td class="align-middle align-center"><?= ubahTanggalIndo($row['tanggalPenyesuaian'], 50, '<br>') ?></td>
            <td class="align-middle align-center"><?= wordwrap($row['status'], 50, '<br>') ?></td>
            <td class="align-middle align-center"><?= wordwrap($row['includeLabaRugiKotor'], 50, '<br>') ?></td>
            <td class="align-middle align-center">Rp <?= ubahToRp($row['nominal'], 50, '<br>') ?></td>
            <td class=""><?= wordwrap($row['keterangan'], 50, '<br>') ?></td>
        </tr>
<?php
        $n++;
    }
}
?>