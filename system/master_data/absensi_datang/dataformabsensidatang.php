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
  'absensi_datang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idAbsensi = '';
$readonly      = '';
extract($_REQUEST);

$sqlUpdate  = $db->prepare('SELECT * from balistars_Absensi
  where idAbsensi = ?');
$sqlUpdate->execute([$idAbsensi]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formAbsensiDatang">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idAbsensi"  value="<?=$idAbsensi?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="row">
    <div class="col-sm-6">
      <div class="form-group">
        <label>Nomor Induk Pegawai</label>
        <input type="text" name="NIK" onkeyup="showDataPegawai();" id="NIK" class="form-control" placeholder="Input Nomor Induk Pegawai" required>
      </div>
      <div class="form-group">
        <label>Sift Kerja</label><br>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="siftKerja" id="inlineRadio1" value="normal">
          <label class="form-check-label" for="inlineRadio1" required>Sift Normal</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="siftKerja" id="inlineRadio1" value="pagi">
          <label class="form-check-label" for="inlineRadio1" required>Sift Pagi</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="siftKerja" id="inlineRadio2" value="middle">
          <label class="form-check-label" for="inlineRadio2" required>Sift Middle</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="siftKerja" id="inlineRadio2" value="middle2">
          <label class="form-check-label" for="inlineRadio2" required>Sift Middle 2</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="siftKerja" id="inlineRadio2" value="middle3">
          <label class="form-check-label" for="inlineRadio2" required>Sift Middle 3</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="siftKerja" id="inlineRadio2" value="siang">
          <label class="form-check-label" for="inlineRadio2" required>Sift Siang</label>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <div id="dataPegawai">
        <div class="form-group">
          <label>Nama Pegawai</label>
          <input type="text" name="namaPegawai" class="form-control" placeholder="Nama Pegawai" disabled>
        </div>
        <div class="form-group">
          <label>Cabang</label>
          <input type="text" name="namaCabang" class="form-control" placeholder="Cabang Pegawai"disabled>
        </div>
      </div>
      <div class="form-group">
        <button type="button" class="btn btn-primary" onclick="prosesAbsensiDatang()">
          <i class="fa fa-save"></i> Save
        </button>
      </div>
    </div>
  </div>
  
</form>