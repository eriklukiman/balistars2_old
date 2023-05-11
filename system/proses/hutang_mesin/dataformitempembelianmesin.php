<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once 'fungsinomor.php';

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
  'hutang_mesin'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flagDetail       = '';
$idPembelianDetail = '';
$noNota     ='';
extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();
$idCabang=$dataLogin['idCabang'];

$sqlUpdate = $db->prepare('SELECT * FROM balistars_pembelian_mesin_detail WHERE idPembelianDetail=?');
$sqlUpdate->execute([$idPembelianDetail]);
$dataUpdate=$sqlUpdate->fetch();
//var_dump($sqlUpdate->errorInfo());
if($dataUpdate){
  $noNota = $dataUpdate['noNota']??'';
  $dataUpdate['hargaSatuan'] = ubahToRp($dataUpdate['hargaSatuan']??'');
  $dataUpdate['diskon'] = ubahToRp($dataUpdate['diskon']??'');
  $dataUpdate['nilai'] = ubahToRp($dataUpdate['nilai']??'');
  $dataUpdate['qty'] = ubahToRp($dataUpdate['qty']??'');
}

?>

<form id="formItemPembelianMesin">
  <td style="vertical-align: top;">#
    <input type="hidden" name="idPembelianDetail" id="idPembelianDetail" value="<?=$idPembelianDetail?>">
    <input type="hidden" name="flagDetail" id="flagDetail" value="<?=$flagDetail?>">
    <input type="hidden" name="noNota" id="noNota" value="<?=$noNota?>">
  </td>
  <td style="vertical-align: top;">
    <input type="text" class="form-control" name="namaBarang" id="namaBarang" placeholder="Nama Barang" value="<?=$dataUpdate['namaBarang']?>">
  </td>
  <td style="vertical-align: top;">
    <input type="text" name="qty" id="qty" placeholder="0" class="form-control" onkeyup="ubahToRp('#qty'); getNilai();" style="text-align: right;" value="<?=$dataUpdate['qty']?>">
  </td>
  <td style="vertical-align: top;">
    <input type="text" class="form-control" style="text-align: right;" name="hargaSatuan" placeholder="0" onkeyup="ubahToRp('#hargaSatuan'); getNilai();" id="hargaSatuan" value="<?=$dataUpdate['hargaSatuan']?>">
  </td>
  <td style="vertical-align: top;">
    <input type="text" class="form-control" style="text-align: right;" name="diskon" placeholder="0" onkeyup="ubahToRp('#diskon'); getNilai();" id="diskon" value="<?=$dataUpdate['diskon']??'0'?>">
  </td>
  <td style="vertical-align: top;">
    <input type="text" name="nilai" id="nilai" placeholder="0" class="form-control" value="<?=$dataUpdate['nilai']?>" readonly style="text-align: right;">
  </td>
  <td style="vertical-align: top;">
    <button type="button" class="btn btn-success" onclick="prosesPembelianMesinDetail()">
      <i class="fa fa-save"></i>
    </button>
  </td>
</form>
