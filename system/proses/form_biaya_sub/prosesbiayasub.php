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
  'form_biaya_sub'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);



if($flag=="cancel"){
  $sql=$db->prepare('UPDATE balistars_biaya_sub set 
    statusBiayaSub=?
    where idBiaya=?');
  $hasil=$sql->execute([
    'Cair',
    $idBiaya]);
}
else{ 
  $tanggalPembayaran=konversiTanggal($tanggalPembayaran);
  $nilaiPembayaran=ubahToInt($nilaiPembayaran);
  $nilaiPenjualan=ubahToInt($nilaiPenjualan);

  $sqlSearch=$db->prepare('SELECT idBiaya from balistars_biaya_sub where idBiaya=? and statusBiayaSub=?');
  $sqlSearch->execute([$idBiaya,'Aktif']);
  $data=$sqlSearch->fetch();

  if($data){
  $sql=$db->prepare('UPDATE balistars_biaya_sub set 
    tanggalPembayaran=?,
    namaSupplier=?,
    project=?,
    nilaiPenjualan=?,
    nilaiPembayaran=?,
    keterangan=?,
    idCabang=?,
    idUserEdit=?
    where idBiaya=?');
  $hasil=$sql->execute([
    $tanggalPembayaran,
    $namaSupplier,
    $project,
    $nilaiPenjualan,
    $nilaiPembayaran,
    $keterangan,
    $idCabang,
    $idUserAsli,
    $idBiaya]);
  }
  else{
    $sql=$db->prepare('INSERT INTO balistars_biaya_sub set 
      idPenjualanDetail=?,
      tanggalPembayaran=?,
      namaSupplier=?,
      project=?,
      nilaiPenjualan=?,
      nilaiPembayaran=?,
      keterangan=?,
      idCabang=?,
      idUser=?');
  $hasil=$sql->execute([
    $idPenjualanDetail,
    $tanggalPembayaran,
    $namaSupplier,
    $project,
    $nilaiPenjualan,
    $nilaiPembayaran,
    $keterangan,
    $idCabang,
    $idUserAsli]);
    //var_dump($sql->errorInfo());
  }
}

$data = array('idPenjualanDetail' => $idPenjualanDetail, 'notifikasi' => 2);
if($hasil){
  $data = array('idPenjualanDetail' => $idPenjualanDetail, 'notifikasi' => 1);
}
echo json_encode($data);

?>