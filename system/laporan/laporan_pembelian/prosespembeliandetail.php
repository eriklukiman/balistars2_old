<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';

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
  'laporan_pembelian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flagDetail == 'cancel'){
  $sql = $db->prepare('UPDATE balistars_pembelian_detail set statusCancel = ? where idPembelianDetail = ?');
  $hasil = $sql->execute(['cancel', $idPembelianDetail]);
}
else{
  //$tanggalPembelian=konversiTanggal($tanggalPembelian);
  $qty = ubahToInt($qty); 
  $hargaSatuan = ubahToInt($hargaSatuan);
  $diskon = ubahToInt($diskon);  
  $nilai = $hargaSatuan*$qty;
  if($flagDetail=='update'){
    $sql=$db->prepare('UPDATE balistars_pembelian_detail set 
      noNota=?,
      jenisOrder=?,
      namaBarang=?,
      qty=?,
      hargaSatuan=?,
      diskon=?,
      nilai=?,
      statusCancel=?,
      idUser=?
      where idPembelianDetail=?');
    $hasil=$sql->execute([
      $noNota,
      $jenisOrder,
      $namaBarang,
      $qty,
      $hargaSatuan,
      $diskon,
      $nilai,
      'oke',
      $idUserAsli,
      $idPembelianDetail]); 
  }
  else{
    $sql=$db->prepare('INSERT INTO balistars_pembelian_detail set 
      noNota=?,
      jenisOrder=?,
      namaBarang=?,
      qty=?,
      hargaSatuan=?,
      diskon=?,
      nilai=?,
      statusCancel=?,
      idUser=?');
    $hasil=$sql->execute([
      $noNota,
      $jenisOrder,
      $namaBarang,
      $qty,
      $hargaSatuan,
      $diskon,
      $nilai,
      'oke',
      $idUserAsli]); 
  }
  
  //var_dump($sql->errorInfo());
}

$data = array('flagDetail' => $flagDetail, 'notifikasi' => 2);
if($hasil){
  $data = array('flagDetail' => $flagDetail, 'notifikasi' => 1);
}
echo json_encode($data);

?>