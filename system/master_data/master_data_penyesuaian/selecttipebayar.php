<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
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

$sqlUpdate  = $db->prepare('SELECT * FROM balistars_penyesuaian
  WHERE idPenyesuaian = ?');
$sqlUpdate->execute([$idPenyesuaian]);
$dataUpdate = $sqlUpdate->fetch();

if ($dataUpdate) {
    $tipePembayaran = $dataUpdate['tipePembayaran'];
} else {
    $tipePembayaran = '-';
}

if ($jenisPenyesuaian === 'Pembelian') {
?>
    <label>Tipe Pembayaran</label>
    <select name="tipePembayaran" id="tipePembayaran" class="form-control select2" style="width: 100%;" onchange="" required>
        <?php
        $opsi = ['Cash', 'Kredit'];

        foreach ($opsi as $data) {
            $selected = selected($data, $tipePembayaran);
        ?>
            <option value="<?= $data ?>" <?= $selected ?>> <?= $data ?> </option>
        <?php
        }
        ?>
    </select>
<?php
} else {
?>
    <input type="hidden" name="tipePembayaran" id="tipePembayaran" value="-">
<?php
}
?>