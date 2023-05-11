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
  'form_input_neraca'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag=='cancel'){
  $sql=$db->prepare('UPDATE balistars_input_neraca set
  statusInputNeraca=?,
  idUserEdit=?
  where idInputNeraca=?');
  $hasil=$sql->execute([
    'Non Aktif',
    $idUserAsli,
    $idInputNeraca]);
}
else{
  $nilaiInputNeraca=ubahToInt($nilaiInputNeraca);
  $tanggalInputNeraca=konversiTanggal($tanggalInputNeraca);

  if($flag=='update'){
    $sql=$db->prepare('UPDATE balistars_input_neraca set 
      tanggalInputNeraca=?,
      tipeBiaya=?,
      nilaiInputNeraca=?,
      jenisInput=?,
      idUserEdit=?
      where idInputNeraca=?');
    $hasil=$sql->execute([
      $tanggalInputNeraca,
      $tipeBiaya,
      $nilaiInputNeraca,
      $jenisInput,
      $idUserAsli,
      $idInputNeraca]);
  }
  else{
    $sql=$db->prepare('INSERT INTO balistars_input_neraca set 
      tanggalInputNeraca=?,
      tipeBiaya=?,
      nilaiInputNeraca=?,
      jenisInput=?,
      idUser=?');
    $hasil=$sql->execute([
      $tanggalInputNeraca,
      $tipeBiaya,
      $nilaiInputNeraca,
      $jenisInput,
      $idUserAsli]);
  }
  //var_dump($sql->errorInfo());
}


$data = array('notifikasi' => 2);
if($hasil){
  $data = array('notifikasi' => 1);
}
echo json_encode($data);

?>