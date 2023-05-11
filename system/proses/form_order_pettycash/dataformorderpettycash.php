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
  'form_order_pettycash'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idOrderKasKecil = '';
$readonly      = '';
$tanggalOrder=date('d-m-Y');
extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

$sqlUpdate  = $db->prepare('
  SELECT * FROM balistars_kas_kecil_order 
  where idOrderKasKecil=? 
  and statusKasKecilOrder=?
  order by tanggalOrder DESC');
$sqlUpdate->execute([
  $idOrderKasKecil,
  'Aktif']);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $dataUpdate['nilai']=ubahToRp($dataUpdate['nilai']??'');
  $tanggalOrder=konversiTanggal($dataUpdate['tanggalOrder']??'');
}

?>
<form id="formOrderPettyCash">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="idOrderKasKecil"  value="<?=$dataUpdate['idOrderKasKecil']?>">
  <input type="hidden" name="idCabang" id="idCabang" value="<?=$dataLogin['idCabang']?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">

  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Order</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalOrder" id="tanggalOrder" value="<?=$tanggalOrder?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>Nilai Order</label>
      <input type="text" class="form-control" placeholder="Nilai Order" name="nilai" id="nilai" onkeyup="ubahToRp('#nilai')" value="<?=$dataUpdate['nilai']?>">
    </div>
  </div>
  <div class="form-group row">
    
  </div>  
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Keterangan</label>
      <textarea class="form-control" name="keterangan" placeholder="Keterangan" id="keterangan"><?=$dataUpdate['keterangan']??''?></textarea>
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesOrderPettyCash()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>