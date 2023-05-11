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
  'master_data_bank'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag == 'cancel'){
  $sql = $db->prepare('UPDATE balistars_bank set statusBank = ?, idUserEdit=? where idBank = ?');
  $hasil = $sql->execute(['Non Aktif',$idUserAsli, $idBank]);
}
else if($flag == 'update'){
  $sql = $db->prepare('UPDATE balistars_bank set 
    namaBank    = ?,
    noRekening    = ?,
    atasNama = ?,
    tipe = ?,
    idUserEdit         =?
    where idBank = ?');
  $hasil = $sql->execute([
    $namaBank,
    $noRekening,
    $atasNama,
    $tipe,
    $idUserAsli,
    $idBank]);
}
else{
  $sql = $db->prepare('INSERT INTO balistars_bank set 
    namaBank    = ?,
    noRekening    = ?,
    atasNama  = ?,
    tipe = ?,
    idUser       =?');
  $hasil = $sql->execute([
    $namaBank,
    $noRekening,
    $atasNama,
    $tipe,
    $idUserAsli]);
}

$data = array('flag' => $flag, 'notifikasi' => 2, 'parameterOrder' => $parameterOrder);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1, 'parameterOrder' => $parameterOrder);
}
echo json_encode($data);

?>