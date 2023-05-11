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
  'laporan_penyusutan_aktiva'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalPenjualan=konversiTanggal($tanggalPenjualan);
$dpp=ubahToInt($dpp);
$ppn=ubahToInt($ppn);

$sql=$db->prepare('INSERT INTO balistars_penjualan_mesin set 
  idPembelianDetail=?,
  tanggalPenjualan=?,
  dpp=?,
  ppn=?,
  keterangan=?,
  idBank=?,
  idUser=?');
$hasil=$sql->execute([
  $idPembelianDetail,
  $tanggalPenjualan,
  $dpp,
  $ppn,
  $keterangan,
  $idBank,
  $idUserAsli]);
//var_dump($sql->errorInfo());

$sql2 = $db->prepare('UPDATE balistars_pembelian_mesin_detail set 
  tanggalJual = ?
  WHERE idPembelianDetail = ?');
$hasil2 = $sql2->execute([
  $tanggalPenjualan,
  $idPembelianDetail]);
//var_dump($sql2->errorInfo());

$sql3 = $db->prepare('UPDATE balistars_mesin_penyusutan set 
  statusPenyusutan = ?
  WHERE idPembelianDetail = ? 
  and tanggalPenyusutan>=?');
$hasil3 = $sql3->execute([
  'Non Aktif',
  $idPembelianDetail, 
  $tanggalPenjualan]);
//var_dump($sql3->errorInfo());
  
$data = array('notifikasi' => 2);
if($hasil){
  $data = array('notifikasi' => 1);
}
echo json_encode($data);

?>