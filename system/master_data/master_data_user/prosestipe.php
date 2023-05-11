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

$flag = '';
extract($_REQUEST);

if($flag == 'edit'){
  $sql = $db->prepare('SELECT * FROM balistars_user_detail where idUserDetail=?');
  $sql->execute([$idUserDetail]);
  $row=$sql->fetch();
  if($row['tipeEdit']=='1'){
     $sql = $db->prepare('UPDATE balistars_user_detail set 
    tipeEdit=?
    where idUserDetail=?');
    $hasil = $sql->execute(['0',$idUserDetail]);
  }
  else{
     $sql = $db->prepare('UPDATE balistars_user_detail set 
    tipeEdit=?
    where idUserDetail=?');
    $hasil = $sql->execute(['1',$idUserDetail]);
  }
}

if($flag == 'delete'){
  $sql = $db->prepare('SELECT * FROM balistars_user_detail where idUserDetail=?');
  $sql->execute([$idUserDetail]);
  $row=$sql->fetch();
  if($row['tipeDelete']=='1'){
     $sql = $db->prepare('UPDATE balistars_user_detail set 
    tipeDelete=?
    where idUserDetail=?');
    $hasil = $sql->execute(['0',$idUserDetail]);
  }
  else{
     $sql = $db->prepare('UPDATE balistars_user_detail set 
    tipeDelete=?
    where idUserDetail=?');
    $hasil = $sql->execute(['1',$idUserDetail]);
  }
}

if($flag == 'a2'){
  $sql = $db->prepare('SELECT * FROM balistars_user_detail where idUserDetail=?');
  $sql->execute([$idUserDetail]);
  $row=$sql->fetch();
  if($row['tipeA2']=='1'){
     $sql = $db->prepare('UPDATE balistars_user_detail set 
    tipeA2=?
    where idUserDetail=?');
    $hasil = $sql->execute(['0',$idUserDetail]);
  }
  else{
     $sql = $db->prepare('UPDATE balistars_user_detail set 
    tipeA2=?
    where idUserDetail=?');
    $hasil = $sql->execute(['1',$idUserDetail]);
  }
}

$data = array('notifikasi'=>2);
if($hasil){
  $data = array('notifikasi'=>1);
}
echo json_encode($data);
?>