<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
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
  'form_order_pettycash'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag == 'cancel'){
  $sql = $db->prepare('UPDATE balistars_kas_kecil_order set 
    statusKasKecilOrder = ?, 
    idUserEdit=? 
    where idOrderKasKecil = ?');
  $hasil = $sql->execute([
    'Non Aktif',
    $idUserAsli, 
    $idOrderKasKecil]);
}
else{
  $nilai=ubahToInt($nilai);
  $tanggalOrder=konversiTanggal($tanggalOrder);

  if($flag == 'update'){
    $sql = $db->prepare('UPDATE balistars_kas_kecil_order set 
      idCabang=?,
      tanggalOrder=?,
      nilai=?,
      keterangan=?,
      statusApproval=?,
      idUserEdit=?
      where idOrderKasKecil=?');
    $hasil = $sql->execute([
      $idCabang,
      $tanggalOrder,
      $nilai,
      $keterangan,
      'reviewed',
      $idUserAsli,
      $idOrderKasKecil]);
  }
  else{
    $sql = $db->prepare('INSERT INTO balistars_kas_kecil_order set 
      idCabang=?,
      tanggalOrder=?,
      nilai=?,
      keterangan=?,
      statusApproval=?,
      idUser =?');
    $hasil = $sql->execute([
      $idCabang,
      $tanggalOrder,
      $nilai,
      $keterangan,
      'reviewed',
      $idUserAsli]);
  }
}

$data = array('flag' => $flag, 'notifikasi' => 2, 'parameterOrder' => $parameterOrder);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1, 'parameterOrder' => $parameterOrder);
}
echo json_encode($data);

?>