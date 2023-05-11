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
  'form_pemutihan_piutang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idPemutihan = '';
$tanggalPemutihan=date('d-m-Y');
extract($_REQUEST);

$sqlUpdate  = $db->prepare('
  SELECT * FROM balistars_pemutihan_piutang 
  where idPemutihan=?
  and statusPemutihan=?');
$sqlUpdate->execute([
  $idPemutihan,
  'Aktif']);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $tanggalPemutihan=konversiTanggal($dataUpdate['tanggalPemutihan']??'');
}

?>
<form id="formPemutihan">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="idPemutihan" id=idPemutihan  value="<?=$dataUpdate['idPemutihan']?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Pemutihan<?=$idPemutihan?></label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control form-control-lg" name="tanggalPemutihan" id="tanggalPemutihan" value="<?=$tanggalPemutihan?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="form-group col-sm-4">
      <label>Input No Nota</label>
     <input type="text" name="noNota" id="noNota" placeholder="Input Nomor Nota" value="<?=$dataUpdate['noNota']?>" onkeyup="showDataNota();" class="form-control"/>
    </div>
    <div class="form-group col-sm-2">
      <label>Aksi</label> <br>
      <button type="button" class="btn btn-primary" onclick="prosesPemutihanPiutang()">
        <i class="fa fa-save"></i> Save
      </button>
    </div>
  </div> 

  <div id="dataNotaTersimpan">
  </div>

</form>