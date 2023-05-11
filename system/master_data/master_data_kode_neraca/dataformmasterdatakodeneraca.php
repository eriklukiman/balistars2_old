<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
  $idUserAsli,
  'master_data_kode_neraca'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idKodeNeracaLajur = '';
$readonly      = '';
extract($_REQUEST);

$sqlUpdate  = $db->prepare('SELECT * from balistars_kode_neraca_lajur
  where idKodeNeracaLajur = ?');
$sqlUpdate->execute([$idKodeNeracaLajur]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formMasterDataKodeNeraca">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idKodeNeracaLajur"  value="<?=$idKodeNeracaLajur?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">

  <div class="form-group row">
    <div class="col-sm-12">
      <label>Kode Neraca Lajur<?=$idKodeNeracaLajur?></label>
      <input type="text" class="form-control" name="kodeNeracaLajur" placeholder="Input Kode Neraca" id="kodeNeracaLajur" value="<?=$dataUpdate['kodeNeracaLajur']??''?>">
    </div>
    <div class=" form-group col-sm-12">
      <label> Keterangan </label>
      <textarea class="form-control" id="keterangan" name="keterangan" placeholder="Input Keterangan"><?=$dataUpdate['keterangan']??''?></textarea>
    </div>
  </div> 
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataKodeNeraca()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>