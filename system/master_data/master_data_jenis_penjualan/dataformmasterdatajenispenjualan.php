<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';

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
  'master_data_jenis_penjualan'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idJenisPenjualan = '';
$readonly      = '';
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_jenis_penjualan
  where idJenisPenjualan = ?');
$sqlUpdate->execute([$idJenisPenjualan]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formMasterDataJenisPenjualan">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idJenisPenjualan"  value="<?=$idJenisPenjualan?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-12">
      <label>Jenis Penjualan</label>
      <input type="text" class="form-control" name="jenisPenjualan" placeholder="Jenis Penjualan" id="jenisPenjualan" value="<?=$dataUpdate['jenisPenjualan']??''?>">
    </div>
  </div>  
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataJenisPenjualan()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>