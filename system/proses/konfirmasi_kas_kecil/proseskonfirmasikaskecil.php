<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';

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
  'konfirmasi_kas_kecil'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$data = array('flag' => $flag, 'notifikasi' => 2);

if($flag == 'cancel'){
  $sql=$db->prepare('UPDATE balistars_kas_kecil_order set 
    statusApproval=?
    where idOrderKasKecil=?');
  $hasil=$sql->execute([
    'reviewed',
    $idOrderKasKecil]);
  $data = array('flag' => $flag, 'notifikasi' => 1);
  //var_dump($sql->errorInfo());
}
else{
  $nilaiApproved = ubahToInt($nilaiApproved);
  $nilai  = ubahToInt($nilai);
  if($nilaiApproved > $nilai){
    $data = array('flag' => $flag, 'notifikasi' => 3);
    //var_dump($data);
  } 
  else{
    $sql=$db->prepare('UPDATE balistars_kas_kecil_order set 
      nilaiApproved=?,                                       
      keteranganApproval=?,
      bankAsalTransfer=?,
      statusApproval=?
      where idOrderKasKecil=?');
    $hasil=$sql->execute([
      $nilaiApproved,
      $keteranganApproval,
      $bankAsalTransfer,
      'approved',
      $idOrderKasKecil]);
    $data = array('flag' => $flag, 'notifikasi' => 1);
  }
}

//$data = array('flag' => $flag, 'notifikasi' => 2);
// if($hasil){
//   $data = array('flag' => $flag, 'notifikasi' => 1);
// }
echo json_encode($data);

?>