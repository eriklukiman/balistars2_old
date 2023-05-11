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
  'form_penyusutan'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idPenyusutan = '';
$readonly      = '';
$tanggalPenyusutan = date('d-m-Y');
extract($_REQUEST);


$sqlUpdate  = $db->prepare('
  SELECT * from balistars_penyusutan
  where idPenyusutan = ?');
$sqlUpdate->execute([$idPenyusutan]);
$dataUpdate = $sqlUpdate->fetch();
if($dataUpdate){
  $tanggalPenyusutan = konversiTanggal($dataUpdate['tanggalPenyusutan']??'');
  $dataUpdate['nilaiPenyusutan'] = ubahToRp($dataUpdate['nilaiPenyusutan']??'');
}

?>
<form id="formPenyusutan">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idPenyusutan"  value="<?=$idPenyusutan?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-12">
      <label>Tanggal Penyusutan</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control form-control-lg" name="tanggalPenyusutan" id="tanggalPenyusutan" value="<?=$tanggalPenyusutan?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Nilai Penyusutan</label>
      <input type="text" class="form-control" placeholder="Nilai Penyusutan" name="nilaiPenyusutan" id="nilaiPenyusutan" onkeyup="ubahToRp('#nilaiPenyusutan')" value="<?=$dataUpdate['nilaiPenyusutan']??''?>">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Tipe</label>
      <select name="tipe" id="tipe" class="form-control select2" >
        <?php
        $arrayTipe=array('A1','A2');
        for($i=0; $i<count($arrayTipe); $i++){
          $selected=selected($arrayTipe[$i],$dataUpdate['tipe']);
          ?>
          <option value="<?=$arrayTipe[$i]?>" <?=$selected?>> <?=$arrayTipe[$i]?> </option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesPenyusutan()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>