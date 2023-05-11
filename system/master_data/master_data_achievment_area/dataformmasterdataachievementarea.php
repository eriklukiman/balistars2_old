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
  'master_data_achievment_area'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

//$bulan = date('m');
//$tahun = date('Y');
$idAchievement = '';
$readonly      = '';
$tanggalAchievement = date('d-m-Y');
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}
//$tanggalAchievement = $tahun.'-'.$bulan.'-01';

$sqlUpdate  = $db->prepare('SELECT * from balistars_achievement_area
  where idAchievement = ?');
$sqlUpdate->execute([$idAchievement]);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $tanggalAchievement = konversiTanggal($dataUpdate['tanggalAchievement']??'');
  $dataUpdate['jumlahAchievement'] = ubahToRp($dataUpdate['jumlahAchievement']??'');
  $dataUpdate['achievementHariEfektif'] = ubahToRp($dataUpdate['achievementHariEfektif']??'');
  $dataUpdate['achievementHariWeekend'] = ubahToRp($dataUpdate['achievementHariWeekend']??'');
}

?>
<form id="formMasterDataAchievement">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idAchievement"  value="<?=$idAchievement?>">
  <!--<input type="hidden" name="tanggalAchievement"  value="<?=$tanggalAchievement?>">-->
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-12">
      <label>Tanggal Achievement (** isikan Tanggal 1 **)</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalAchievement" id="tanggalAchievement" value="<?=$tanggalAchievement?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <label> Area </label>
      <select name="area" class="form-control select2" style="width: 100%;" required>
        <option value="">Pilih Area</option>
        <?php
        $arrayArea=array('timur','barat','utara','selatan');
        for($i=0; $i<count($arrayArea); $i++){
          $selected=selected($arrayArea[$i],$dataUpdate['area']??'');
          ?>
          <option value="<?=$arrayArea[$i]?>" <?=$selected?>> <?=$arrayArea[$i]?> </option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Achievment Bulanan</label>
      <input type="text" class="form-control" placeholder="Achievment Bulanan" name="jumlahAchievement" id="jumlahAchievement" onkeyup="ubahToRp('#jumlahAchievement')" value="<?=$dataUpdate['jumlahAchievement']??''?>">
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Acievment Hari Efektif</label>
      <input type="text" class="form-control" placeholder="Acievment Hari Efektif" name="achievementHariEfektif" id="achievementHariEfektif" onkeyup="ubahToRp('#achievementHariEfektif')" value="<?=$dataUpdate['achievementHariEfektif']??''?>">
    </div>
    <div class="col-sm-6">
      <label>Acievment Sabtu Minggu</label>
      <input type="text" class="form-control" placeholder="Acievment Sabtu Minggu" name="achievementHariWeekend" id="achievementHariWeekend" onkeyup="ubahToRp('#achievementHariWeekend') " value="<?=$dataUpdate['achievementHariEfektif']??''?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataAchievement()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>