<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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
  'laporan_piutang_history'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($jenisPembayaran=='Cash'){
?>
  <div class="form-group">
    <label class="col-form-label">Bank Transfer</label>
    <select name="bankTujuanTransfer" id="bankTujuanTransferSearch" class="form-control select2">
      <option value="0">Cash</option>
    </select>
  </div>
<?php
} elseif($jenisPembayaran=='PPN'){
?>
  <div class="form-group">
    <label class="col-form-label">Bank Transfer</label>
    <select name="bankTujuanTransfer" id="bankTujuanTransferSearch" class="form-control select2">
      <option value="-">PPN Bayar Dinas</option>
    </select>
  </div>
<?php
} elseif($jenisPembayaran=='Transfer'){
?>
  <div class="form-group">
    <label class="col-form-label">Bank Transfer</label>
    <select name="bankTujuanTransfer" class="form-control select2">
      <?php
      $sqlBank=$db->prepare('SELECT * FROM balistars_bank 
        where statusBank=? 
        order by namaBank');
      $sqlBank->execute(['Aktif']);
      $dataBank=$sqlBank->fetchAll();
      foreach($dataBank as $row){
        ?>
        <option value="<?=$row['idBank']?>"> <?=$row['namaBank']?> </option>
        <?php
      }
      ?>
    </select>
  </div>
<?php 
} ?>