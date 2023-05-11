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
  'laporan_piutang_history'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($bankTujuanTransfer==0){
  $jenisPembayaran="Cash";
}
if($bankTujuanTransfer!=0){
  $jenisPembayaran="Transfer";
}
if($bankTujuanTransfer=="-"){
  $jenisPembayaran="PPN";
}
$sql=$db->prepare('UPDATE balistars_piutang set 
  bankTujuanTransfer=?,
  jenisPembayaran=?
  where idPiutang=?'); 
$hasil=$sql->execute([
  $bankTujuanTransfer,
  $jenisPembayaran,
  $idPiutang]);

$data = array('notifikasi' => 2, 'idPiutang' => $idPiutang);

if($hasil){
  $data = array('notifikasi' => 1, 'idPiutang' => $idPiutang);
}
echo json_encode($data);
?>