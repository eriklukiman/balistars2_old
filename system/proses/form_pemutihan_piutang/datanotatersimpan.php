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

extract($_REQUEST);

$sql=$db->prepare('SELECT balistars_penjualan.namaCustomer, balistars_cabang.namaCabang, balistars_piutang.sisaPiutang, balistars_penjualan.grandTotal as total  FROM balistars_penjualan inner join balistars_cabang on balistars_penjualan.idCabang=balistars_cabang.idCabang inner join balistars_piutang on balistars_penjualan.noNota=balistars_piutang.noNota where balistars_piutang.noNota=? order by balistars_piutang.idPiutang DESC limit 1');
  $sql->execute([$noNota]);
  $data=$sql->fetch();
?>
<div class="row">
  <div class="form-group col-sm-6">
    <label>Cabang <?=$noNota?></label>
     <input type="text" name="namaCabang" id="namaCabang" class="form-control" value="<?=$data['namaCabang']?>" style="margin-right: 3px;" readonly>
  </div>
  <div class="form-group col-sm-6">
    <label>Customer</label>
     <input type="text" name="namaCustomer" id="namaCustomer" class="form-control" value="<?=$data['namaCustomer']?>" style="margin-right: 3px;" readonly>
  </div>
  <div class="form-group col-sm-6">
    <label>Total Penjualan</label>
     <input type="text" name="total" id="total" class="form-control" value="<?=ubahToRp($data['total'])?>" style="margin-right: 3px;" readonly>
  </div>
  <div class="form-group col-sm-6">
    <label>Sisa Piutang</label>
     <input type="text" name="sisaPiutang" id="sisaPiutang" class="form-control" value="<?=ubahToRp($data['sisaPiutang'])?>" style="margin-right: 3px;" readonly>
  </div>
</div>