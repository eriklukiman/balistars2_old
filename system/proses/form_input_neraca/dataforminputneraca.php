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
  'form_input_neraca'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idInputNeraca = '';
$tanggalInputNeraca=date('d-m-Y');
extract($_REQUEST);

$sqlUpdate  = $db->prepare('
  SELECT * FROM balistars_input_neraca 
  where idInputNeraca=?
  and statusInputNeraca=?');
$sqlUpdate->execute([
  $idInputNeraca,
  'Aktif']);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $dataUpdate['nilaiInputNeraca']=ubahToRp($dataUpdate['nilaiInputNeraca']??'');
  $tanggalInputNeraca=konversiTanggal($dataUpdate['tanggalInputNeraca']??'');
}

?>
<form id="formInputNeraca">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="idInputNeraca"  value="<?=$dataUpdate['idInputNeraca']?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">

  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal InputNeraca</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control form-control-lg" name="tanggalInputNeraca" id="tanggalInputNeraca" value="<?=$tanggalInputNeraca?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>Nilai InputNeraca</label>
      <input type="text" class="form-control form-control-lg" placeholder="Nilai Setor" name="nilaiInputNeraca" id="nilaiInputNeraca" onkeyup="ubahToRp('#nilaiInputNeraca')" value="<?=$dataUpdate['nilaiInputNeraca']?>">
    </div>
  </div> 
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Jenis Input</label>
      <select name="jenisInput" class="form-control select2" style="width: 100%;" required>
        <?php
        $arrayJenisInput=array('Saldo Awal','Debet','Kredit');
        for($i=0; $i<count($arrayJenisInput); $i++){
          $selected=selected($arrayJenisInput[$i],$dataUpdate['jenisInput']??'');
          ?>
          <option value="<?=$arrayJenisInput[$i]?>" <?=$selected?>> <?=$arrayJenisInput[$i]?> </option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Tipe Biaya</label>
      <select name="tipeBiaya" class="form-control select2" style="width: 100%;" required>
        <?php
        $arrayTipeBiaya=array('Modal Awal','Laba Ditahan','Cadangan Pajak','Advertising');
        for($i=0; $i<count($arrayTipeBiaya); $i++){
          $selected=selected($arrayTipeBiaya[$i],$dataUpdate['tipeBiaya']??'');
          ?>
          <option value="<?=$arrayTipeBiaya[$i]?>" <?=$selected?>> <?=$arrayTipeBiaya[$i]?> </option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesInputNeraca()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>