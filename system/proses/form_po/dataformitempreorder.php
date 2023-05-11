<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'form_po'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}
  
$readonly      = '';
$noPo     ='';
$idPoDetail='';
$flag ='';
$flagDetail='';
extract($_REQUEST);

$sqlUpdate = $db->prepare('SELECT * FROM balistars_po_detail WHERE idPoDetail=?');
$sqlUpdate->execute([$idPoDetail]);
$dataUpdate=$sqlUpdate->fetch();
if($dataUpdate){
  $dataUpdate['hargaSatuan']=ubahToRp($dataUpdate['hargaSatuan']);
  $dataUpdate['qty'] = ubahToRp($dataUpdate['qty']);
  $dataUpdate['nilai'] = ubahToRp($dataUpdate['nilai']);
}

?>

  <form id="formItemPreorder">
    <input type="hidden" name="flagDetail" id="flagDetail" value="<?=$flagDetail?>">
    <input type="hidden" name="noPo" id="noPo" value="<?=$dataUpdate['noPo']?>">
    <input type="hidden" name="idPoDetail" id="idPoDetail" value="<?=$idPoDetail?>">
    <td style="vertical-align: top;">#<?=$idPoDetail?></td>
    <td style="vertical-align: top;">
      <input type="text" name="namaBahan" value="<?=$dataUpdate['namaBahan']?>" id="namaBahan" class="form-control" placeholder="Input Nama Bahan" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="ukuran" id="ukuran" value="<?=$dataUpdate['ukuran']?>" class="form-control" placeholder="Input Ukuran" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="finishing" id="finishing" class="form-control" placeholder="Input Finishing" value="<?=$dataUpdate['finishing']?>" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="qty" id="qty" class="form-control" placeholder="qty" onkeyup="ubahToRp('#qty'); getNilai();" value="<?=$dataUpdate['qty']?>" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="hargaSatuan" id="hargaSatuan" class="form-control" placeholder="harga" onkeyup="ubahToRp('#hargaSatuan'); getNilai();" value="<?=$dataUpdate['hargaSatuan']?>" >
    </td>
    <td style="vertical-align: top;">
      <input type="text" name="nilai" id="nilai" class="form-control" placeholder="0" value="<?=$dataUpdate['nilai']?>" readonly>
    </td>
    <td style="vertical-align: top;">
      <button type="button" class="btn btn-success" onclick="prosesPreorderDetail()">
        <i class="fa fa-save" style="text-align: right;"></i>
      </button>
    </td>
  </form>
