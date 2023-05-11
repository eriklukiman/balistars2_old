<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'form_mesin_laser'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idPerformaLaser = '';
$klikBefore='';
$klikAfter='';
$tanggalPerforma = date('d-m-Y');
extract($_REQUEST);

$sqlLogin  = $db->prepare('
  SELECT * FROM balistars_pegawai 
  inner join balistars_user 
  on balistars_pegawai.idPegawai=balistars_user.idPegawai 
  inner join balistars_cabang 
  on balistars_pegawai.idCabang=balistars_cabang.idCabang 
  where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

$sqlUpdate=$db->prepare('
  SELECT * FROM balistars_performa_mesin_laser 
  where idPerformaLaser=?');
$sqlUpdate->execute([$idPerformaLaser]);
$dataUpdate=$sqlUpdate->fetch();

if($dataUpdate){
  $tanggalPerforma=konversiTanggal($dataUpdate['tanggalPerforma']??'');
  $klikAfter=ubahToRp($dataUpdate['klikBefore']);

  $sqlSebelum=$db->prepare('
    SELECT * FROM balistars_performa_mesin_laser 
    where tanggalPerforma<=? 
    and idCabang=? 
    and idPerformaLaser!=? 
    order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
  $sqlSebelum->execute([
    $dataUpdate['tanggalPerforma'],
    $dataUpdate['idCabang'],
    $dataUpdate['idPerformaLaser']]);
  $dataSebelum=$sqlSebelum->fetch();

  if($dataSebelum['tanggalPerforma']==$dataUpdate['tanggalPerforma'] && $dataSebelum['idPerformaLaser']>$dataUpdate['idPerformaLaser']){

    $sqlSebelum=$db->prepare('
      SELECT * FROM balistars_performa_mesin_laser 
      where tanggalPerforma=? 
      and idCabang=? 
      and idPerformaLaser<? 
      order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
    $sqlSebelum->execute([
      $dataUpdate['tanggalPerforma'],
      $dataUpdate['idCabang'],
      $dataUpdate['idPerformaLaser']]);
    $dataSebelum=$sqlSebelum->fetch();

    if($dataSebelum['idPerformaLaser']>0){
    }
    else{
      $sqlSebelum=$db->prepare('
        SELECT * FROM balistars_performa_mesin_laser 
        where tanggalPerforma<? 
        and idCabang=? 
        and idPerformaLaser!=? 
        order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
      $sqlSebelum->execute([
        $dataUpdate['tanggalPerforma'],
        $dataUpdate['idCabang'],
        $dataUpdate['idPerformaLaser']]);
      $dataSebelum=$sqlSebelum->fetch();
    }
  } 
}
else{
  $tanggalCari=konversiTanggal($tanggalPerforma);
  $sqlSebelum=$db->prepare('
    SELECT * FROM balistars_performa_mesin_laser 
    where tanggalPerforma<=? 
    and idCabang=? 
    order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
  $sqlSebelum->execute([
    $tanggalCari,
    $dataLogin['idCabang']]);
  $dataSebelum=$sqlSebelum->fetch();
}
$klikBefore=ubahToRp($dataSebelum['klikBefore']);

?>
<form id="formMesinLaser">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idPerformaLaser"  value="<?=$idPerformaLaser?>">
  <input type="hidden" name="idCabang" value="<?=$dataLogin['idCabang']?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Klik Before <?=$tanggalPerforma?></label>
      <input type="text" class="form-control" name="klik" id="klik" value="<?=$klikBefore?>" readonly>
    </div>
    <div class="col-sm-6"> 
      <label>Tanggal </label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalPerforma" id="tanggalPerforma" value="<?=$tanggalPerforma?>"  autocomplete="off">
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
      <label>Klik After</label>
      <input type="text" class="form-control" placeholder="Input Nominal" name="klikBefore" id="klikBefore" onkeyup="ubahToRp('#klikBefore')" value="<?=$klikAfter?>">
    </div>
    <div class="col-sm-6">
      <button type="button" class="btn btn-primary" onclick="prosesMesinLaser()">
        <i class="fa fa-save"></i> <br> Save
      </button>
      <button type="button" class="btn btn-danger" onclick="stopMesinLaser()">
        <i class="fa fa-ban"></i> <br> Stop
      </button>
    </div>
  </div>
</form>
