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
  'form_mesin_indoor'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag == 'cancel'){
  $sql=$db->prepare('DELETE FROM balistars_performa_mesin_indoor 
    where idPerformaIndoor=?');
  $hasil=$sql->execute([$idPerformaIndoor]);
}
else{
  $tanggalPerforma=konversiTanggal($tanggalPerforma);
  $sisi=explode('x',$ukuran);
  $panjang=trim($sisi[0]);
  $lebar=trim($sisi[1]);
  $panjang=(float)$panjang;
  $lebar=(float)$lebar;
  $ukuran=$panjang."x".$lebar;

  if($flag == 'update'){
    $sql=$db->prepare('UPDATE balistars_performa_mesin_indoor set 
      tanggalPerforma=?,
      namaBahan=?,
      luas=?,
      idUserEdit=?
      where idPerformaIndoor=?');
    $hasil=$sql->execute([
      $tanggalPerforma,
      $namaBahan,
      $luas,
      $idUserAsli,
      $idPerformaIndoor]);
  }
  else{
    $sql=$db->prepare('UPDATE balistars_penjualan_detail set 
      namaBahan=?,
      ukuran=?
      where idPenjualanDetail=?');
    $hasil=$sql->execute([
      $namaBahan,
      $ukuran,
      $idPenjualanDetail]);

    $sql=$db->prepare('INSERT INTO balistars_performa_mesin_indoor set 
      idCabang=?,
      noNota=?,
      idPenjualanDetail=?,
      tanggalPerforma=?,
      namaBahan=?,
      luas=?,
      idUser=?');
    $hasil=$sql->execute([
      $idCabang,
      $noNota,
      $idPenjualanDetail,
      $tanggalPerforma,
      $namaBahan,
      $luas,
      $idUserAsli]);
  }
  //var_dump($sql->errorInfo());
}

$data = array('notifikasi' => 2);
if($hasil){
  $data = array('notifikasi' => 1);
}
echo json_encode($data);

?>