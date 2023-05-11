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
  'master_data_bank'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idBank = '';
$readonly      = '';
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_bank
  where idBank = ?');
$sqlUpdate->execute([$idBank]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formMasterDataBank">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idBank"  value="<?=$idBank?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nama Bank</label>
      <input type="text" class="form-control" name="namaBank" placeholder="Nama Bank" id="namaBank" value="<?=$dataUpdate['namaBank']??''?>">
    </div>
    <div class="col-sm-6">
      <label>No Rekening</label>
      <input type="text" class="form-control" id="noRekening" name="noRekening" placeholder="No Rekening" value="<?=$dataUpdate['noRekening']??''?>">
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tipe</label>
      <select name="tipe" class="form-control select2" style="width: 100%;" required>
        <option value="">Pilih Tipe</option>
        <?php
        $arrayTipe=array('A1','A2');
        for($i=0; $i<count($arrayTipe); $i++){
          $selected=selected($arrayTipe[$i],$dataUpdate['tipe']??'');
          ?>
          <option value="<?=$arrayTipe[$i]?>" <?=$selected?>> <?=$arrayTipe[$i]?> </option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Atas Nama Rekening</label>
      <input type="text" class="form-control" id="atasNama" name="atasNama" placeholder="Atas Nama Rekening" value="<?=$dataUpdate['atasNama']??''?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataBank()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>