<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP.'/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP.'/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP.'/library/konfigurasidatabase.php';

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
  'master_data_user'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag == 'cancel'){
  $sql = $db->prepare('UPDATE balistars_user set statusUser = ? where idUser = ?');
  $hasil = $sql->execute(['Non aktif', $idUserAccount]);
}
else if($flag == 'update'){
  $password  = password_hash($password, PASSWORD_DEFAULT);
  $sql = $db->prepare('UPDATE balistars_user set 
    userName     = ?,
    tipeUser     = ?,
    password     = ?
    where idUser = ?');
  $hasil = $sql->execute([
    $userName,
    $tipeUser,
    $password,
    $idUserAccount]);
}
else{
  $password           = password_hash($password, PASSWORD_DEFAULT);
  $sql = $db->prepare('INSERT INTO balistars_user set 
    idPegawai =?,
    tipeUser  =?,
    userName  = ?,
    password  = ?,
    jenisUser=?');
  $hasil = $sql->execute([
    $idPegawai,
    $tipeUser,
    $userName,
    $password,
    'Baru']);
}

$data = array('flag' => $flag, 'notifikasi' => 2, 'parameterOrder' => $parameterOrder);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1, 'parameterOrder' => $parameterOrder);
}
echo json_encode($data);
