<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
include_once $BASE_URL_PHP.'/library/fungsitanggal.php';

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
  'master_data_sift'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$siftPagiNormal="00:00";
$siftSiangNormal="00:00";
$siftPagiWeekend="00:00";
$siftSiangWeekend="00:00";
$siftMiddleNormal="00:00";
$siftNormalNormal="00:00";
$siftMiddleWeekend="00:00";
$siftNormalWeekend="00:00";
extract($_REQUEST);


if($flag == 'cancel'){
  $sql = $db->prepare('UPDATE balistars_sift set statusSift = ?, idUserEdit=? where idSift = ?');
  $hasil = $sql->execute(['Non Aktif',$idUserAsli, $idSift]);
}
else if($flag == 'update'){
    $tanggalBerlaku=konversiTanggal($tanggalBerlaku);
  $sql = $db->prepare('UPDATE balistars_sift set 
    idCabang=?,
    tanggalBerlaku=?,
    siftPagiNormal=?,
    siftSiangNormal=?,
    siftPagiWeekend=?,
    siftSiangWeekend=?,
    siftMiddleNormal=?,
    siftNormalNormal=?,
    siftMiddleWeekend=?,
    siftMiddle2Normal=?,
    siftMiddle2Weekend=?,
    siftMiddle3Normal=?,
    siftMiddle3Weekend=?,
    siftNormalWeekend=?,
    idUserEdit       =?
    where idSift = ?');
  $hasil = $sql->execute([
    $idCabang,
    $tanggalBerlaku,
    $siftPagiNormal,
    $siftSiangNormal,
    $siftPagiWeekend,
    $siftSiangWeekend,
    $siftMiddleNormal,
    $siftNormalNormal,
    $siftMiddleWeekend,
    $siftMiddle2Normal,
    $siftMiddle2Weekend,
    $siftMiddle3Normal,
    $siftMiddle3Weekend,
    $siftNormalWeekend,
    $idUserAsli,
    $idSift]);
}
else{
    $tanggalBerlaku=konversiTanggal($tanggalBerlaku);
  $sql = $db->prepare('INSERT INTO balistars_sift set 
    idCabang=?,
    tanggalBerlaku=?,
    siftPagiNormal=?,
    siftSiangNormal=?,
    siftPagiWeekend=?,
    siftSiangWeekend=?,
    siftMiddleNormal=?,
    siftNormalNormal=?,
    siftMiddleWeekend=?,
    siftMiddle2Normal=?,
    siftMiddle2Weekend=?,
    siftMiddle3Normal=?,
    siftMiddle3Weekend=?,
    siftNormalWeekend=?,
    idUser        =?');
  $hasil = $sql->execute([
    $idCabang,
    $tanggalBerlaku,
    $siftPagiNormal,
    $siftSiangNormal,
    $siftPagiWeekend,
    $siftSiangWeekend,
    $siftMiddleNormal,
    $siftNormalNormal,
    $siftMiddleWeekend,
    $siftMiddle2Normal,
    $siftMiddle2Weekend,
    $siftMiddle3Normal,
    $siftMiddle3Weekend,
    $siftNormalWeekend,
    $idUserAsli]);
}


$data = array('flag' => $flag, 'notifikasi' => 2, 'parameterOrder' => $parameterOrder);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1, 'parameterOrder' => $parameterOrder);
}
echo json_encode($data);

?>