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
  'master_data_cabang'
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

$sqlUpdate  = $db->prepare('SELECT * from balistars_cabang
  where idCabang = ?');
$sqlUpdate->execute([$idCabang]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formMasterDataCabang">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idCabang"  value="<?=$idCabang?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nama Cabang</label>
      <input type="text" class="form-control" name="namaCabang" placeholder="Nama Cabang" id="namaCabang" value="<?=$dataUpdate['namaCabang']??''?>">
    </div>
    <div class="col-sm-6">
      <label>No Telp Cabang</label>
      <input type="text" name="noTelpCabang" class="form-control" placeholder="Input no telp cabang" value="<?=$dataUpdate['noTelpCabang']??''?>" required>
    </div>
  </div> 
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Kota Cabang</label>
      <input type="text" name="kota" class="form-control" placeholder="Input kota" value="<?=$dataUpdate['kota']??''?>" required>
    </div>
    <div class="col-sm-6">
      <label>Area</label>
      <select name="area" class="form-control select2" style="width: 100%;" required>
          <option value=""> Pilih Area </option>
          <?php
          $arrayArea = array("timur","barat","utara","selatan");
          for($i=0; $i<count($arrayArea); $i++){
            $selected=selected($arrayArea[$i],$dataUpdate['area']??'');
            ?>
            <option value="<?=$arrayArea[$i]?>"<?=$selected?>><?=$arrayArea[$i]?></option>
            <?php
          }
          ?>
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Alamat Cabang</label>
      <input type="text" class="form-control"  name="alamatCabang" placeholder="Input Alamat Cabang" value="<?=$dataUpdate['alamatCabang']??''?>" required>
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataCabang()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>