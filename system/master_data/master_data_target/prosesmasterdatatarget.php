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
  'master_data_target'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag == 'cancel'){
  $sql = $db->prepare('UPDATE balistars_target set statusTarget = ?, idUserEdit=? where idTarget = ?');
  $hasil = $sql->execute(['Non Aktif',$idUserAsli, $idTarget]);
}
else{
  $target=ubahToInt($target);
  $tanggalAwal=konversiTanggal($tanggalAwal);
  $tanggalAkhir=konversiTanggal($tanggalAkhir);
  if($flag == 'update'){
    $sql = $db->prepare('UPDATE balistars_target set 
      idJenisPenjualan    = ?,
      idCabang    = ?,
      tanggalAwal = ?,
      tanggalAkhir = ?,
      target       =?,
      idUserEdit         =?
      where idTarget = ?');
    $hasil = $sql->execute([
      $idJenisPenjualan,
      $idCabang,
      $tanggalAwal,
      $tanggalAkhir,
      $target,
      $idUserAsli,
      $idTarget]);
  }
  else{
    $sql = $db->prepare('INSERT INTO balistars_target set 
      idJenisPenjualan    = ?,
      idCabang    = ?,
      tanggalAwal = ?,
      tanggalAkhir = ?,
      target       =?,
      idUser        =?');
    $hasil = $sql->execute([
      $idJenisPenjualan,
      $idCabang,
      $tanggalAwal,
      $tanggalAkhir,
      $target,
      $idUserAsli]);
  }
}

$data = array('flag' => $flag, 'notifikasi' => 2, 'parameterOrder' => $parameterOrder);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1, 'parameterOrder' => $parameterOrder);
}
echo json_encode($data);

?>