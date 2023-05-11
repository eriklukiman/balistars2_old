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

extract($_REQUEST);
if($NIK=="" || $NIK==" "){
?>
<div id="dataPegawai">
  <div class="form-group">
    <label>Nama Pegawai</label>
    <input type="text" name="namaPegawai" class="form-control" placeholder="Nama Pegawai"  disabled>
  </div>
  <div class="form-group">
    <label>Cabang</label>
    <input type="text" name="namaCabang" class="form-control" placeholder="Cabang Pegawai" disabled>
  </div>
  <?php
  }
  else{
    $sqlPegawai=$db->prepare('SELECT * FROM balistars_pegawai inner join balistars_cabang on balistars_cabang.idCabang=balistars_pegawai.idCabang where balistars_pegawai.NIK=?');
    $sqlPegawai->execute([$NIK]);
    $dataPegawai=$sqlPegawai->fetch();
    ?>
    <input type="hidden" name="idCabang" value="<?=$dataPegawai['idCabang']?>">
     <input type="hidden" name="idPegawaiAbsen" value="<?=$dataPegawai['idPegawai']?>">
     <div class="form-group">
        <label>Nama Pegawai</label>
        <input type="text" name="namaPegawai" class="form-control" placeholder="Nama Pegawai" value="<?=$dataPegawai['namaPegawai']?>" disabled>
      </div>
      <div class="form-group">
        <label>Cabang</label>
        <input type="text" name="namaCabang" class="form-control" placeholder="Cabang Pegawai" value="<?=$dataPegawai['namaCabang']?>" disabled>
      </div>
    <?php
  }

  ?>
</div>
