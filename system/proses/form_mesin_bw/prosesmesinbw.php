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
  'form_mesin_bw'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag == 'cancel'){
  $sql=$db->prepare('DELETE FROM balistars_performa_mesin_bw 
    where idPerformaBW=?');
  $hasil=$sql->execute([$idPerformaBW]);
}
else{
  $tanggalPerforma=konversiTanggal($tanggalPerforma);
  $klikBefore=ubahToInt($klikBefore);
  //$klik=ubahToInt($klik);
  //$jumlahKlik=$klikBefore-$klik;
  if($flag == 'stop'){
    $sql=$db->prepare('INSERT INTO balistars_performa_mesin_bw set 
      idCabang=?,
      tanggalPerforma=?,
      klikBefore=?,
      jumlahKlik=?,
      statusKlik=?,
      idUser=?');
    $hasil=$sql->execute([
      $idCabang,
      $tanggalPerforma,
      $klikBefore,
      0,
      'Start',
      $idUserAsli]);
  }
  else{
    $sql=$db->prepare('INSERT INTO balistars_performa_mesin_bw set 
      idCabang=?,
      tanggalPerforma=?,
      klikBefore=?,
      idUser=?');
    $hasil=$sql->execute([
      $idCabang,
      $tanggalPerforma,
      $klikBefore,
      $idUserAsli]);
  }
  //var_dump($hasil);
}
 

$data = array('flag' => $flag, 'notifikasi' => 2, 'parameterOrder' => $parameterOrder);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1, 'parameterOrder' => $parameterOrder);
}
echo json_encode($data);

?>