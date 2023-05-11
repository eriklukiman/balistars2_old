<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';

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
  'master_data_menu'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}


$flag = '';
extract($_POST);

if ($flag == 'cancel') {
  $sql = $db->prepare('UPDATE balistars_menu_sub set statusMenuSub =?, idUserEdit=? where idMenuSub=?');
  $hasil = $sql->execute(['Tidak Aktif', $idUserAsli, $idMenuSub]);
} else if ($flag == 'update') {
  $sql = $db->prepare('UPDATE balistars_menu_sub SET 
    namaMenuSub = ?, 
    namaFolder = ?,
    indexMenuSub =?, 
    idUserEdit = ? 
    WHERE idMenuSub = ?');
  $hasil = $sql->execute([
    $namaMenuSub,
    $namaFolder,
    $indexMenuSub,
    $idUserAsli,
    $idMenuSub
  ]);
} else {
  $sql = $db->prepare('INSERT INTO balistars_menu_sub set 
    idMenu=?,
    namaMenuSub=?,
    namaFolder=?,
    indexMenuSub =?,
    idUser=?');
  $hasil = $sql->execute([
    $idMenu,
    $namaMenuSub,
    $namaFolder,
    $indexMenuSub,
    $idUserAsli
  ]);
  
}


$data = array('notifikasi' => 2);
if ($hasil) {
  $data = array('notifikasi' => 1, 'idMenu' => $idMenu);
}
echo json_encode($data);
