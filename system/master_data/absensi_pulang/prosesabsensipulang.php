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

date_default_timezone_set("Asia/Kuala_Lumpur");
$siftKerja = '';
extract($_REQUEST);

$today=date('Y-m-d');
$timeToday=date('H:i:s');

$sqlCek=$db->prepare('SELECT jamDatang FROM balistars_absensi where idPegawai=? and tanggalDatang=?');
$sqlCek->execute([$idPegawaiAbsen,$today]);
$dataCek=$sqlCek->fetch();

$data = array('flag' => $flag, 'status' => false,'pesan' => 'Proses gagal, sql eror', 'parameterOrder' => $parameterOrder);

if($dataCek){
  $sql=$db->prepare('UPDATE balistars_absensi set jamPulang=?
                                                  where
                                                  idPegawai=? and
                                                  tanggalDatang=?');
  $hasil=$sql->execute([$timeToday,$idPegawaiAbsen,$today]);
  if($hasil){
  $data = array('flag' => $flag, 'status' => true,'pesan' => 'Proses Absensi Berhasil', 'parameterOrder' => $parameterOrder);
  }
}
else{
  $data = array('flag' => $flag, 'status' => false,'pesan' => 'Anda Belum Melakukan Absensi Datang Hari Ini', 'parameterOrder' => $parameterOrder);
}




echo json_encode($data);

?>