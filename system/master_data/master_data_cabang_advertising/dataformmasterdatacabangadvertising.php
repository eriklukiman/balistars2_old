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
  'master_data_cabang_advertising'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idCabang = '';
$readonly      = '';
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_cabang_advertising
  where idCabang = ?');
$sqlUpdate->execute([$idCabang]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formMasterDataCabangAdvertising">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idCabang"  value="<?=$idCabang?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nama Cabang Advertising</label>
      <input type="text" class="form-control" name="namaCabang" placeholder="Nama Cabang" id="namaCabang" value="<?=$dataUpdate['namaCabang']??''?>">
    </div>
    <div class="col-sm-6">
      <label>Kota Cabang</label>
      <input type="text" class="form-control" id="kota" name="kota" placeholder="kota" value="<?=$dataUpdate['kota']??''?>">
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nomor Telepon</label>
      <input type="text" class="form-control" id="noTelpCabang" name="noTelpCabang" placeholder="noTelpCabang" value="<?=$dataUpdate['noTelpCabang']??''?>">
    </div>
    <div class="col-sm-6">
      <label>Alamat</label>
      <input type="text" class="form-control" id="alamatCabang" name="alamatCabang" placeholder="Alamat Cabang" value="<?=$dataUpdate['alamatCabang']??''?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataCabangAdvertising()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>