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
  'finalisasi_penjualan'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sql=$db->prepare('
  SELECT tanggalPenjualan, grandTotal, jumlahPembayaranAwal, jenisPembayaran, bankTujuanTransfer 
  from balistars_penjualan 
  where noNota=?');
$sql->execute([$noNota]);
$data=$sql->fetch();

$sqlPiutangDelete=$db->prepare('DELETE FROM balistars_piutang where noNota=?');
$sqlPiutangDelete->execute([$noNota]);
// $sqlPiutangDelete->fetch();

$sisaPiutang=$data['grandTotal']-$data['jumlahPembayaranAwal'];

$sqlInsertPiutang=$db->prepare('INSERT INTO balistars_piutang set 
  noNota=?,
  tanggalPenjualan=?,
  tanggalPembayaran=?,
  grandTotal=?,
  jumlahPembayaran=?,
  sisaPiutang=?,
  jenisPembayaran=?,
  bankTujuanTransfer=?,
  idUser=?');
$hasilInsertPiutang=$sqlInsertPiutang->execute([
  $noNota,
  $data['tanggalPenjualan'],
  $data['tanggalPenjualan'],
  $data['grandTotal'],
  $data['jumlahPembayaranAwal'],
  $sisaPiutang,$data['jenisPembayaran'],
  $data['bankTujuanTransfer'],
  $idUserAsli]);
//var_dump($sqlInsertPiutang->errorInfo());

if($hasilInsertPiutang){
  $sql = $db->prepare('UPDATE balistars_penjualan set 
    statusFinalNota = ?,
    idUserEdit =?
    where noNota = ?');
  $hasil = $sql->execute([
    'final',
    $idUserAsli, 
    $noNota]);
}


$data = array( 'notifikasi' => 2);
if($hasil){
  $data = array('notifikasi' => 1);
}
echo json_encode($data);

?>