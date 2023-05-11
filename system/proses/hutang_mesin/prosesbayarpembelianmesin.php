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
  'hutang_mesin'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag=="finalisasi"){
  $sql=$db->prepare('UPDATE balistars_hutang_mesin set 
    statusCair=?
    where idHutangMesin=?');
  $hasil=$sql->execute([
    'Cair',
    $idHutangMesin]);
}
else{
  $tanggalPembayaran=konversiTanggal($tanggalPembayaran);
  $tanggalPembelian=konversiTanggal($tanggalPembelian);
  $tanggalCair=konversiTanggal($tanggalCair);
  $jumlahPembayaran=ubahToInt($jumlahPembayaran);
  $grandTotal=ubahToInt($grandTotal);
  $sisaHutangAwal=ubahToInt($sisaHutangAwal);
  if($jumlahPembayaran>$sisaHutangAwal){
    $jumlahPembayaran=$sisaHutangAwal;
  }
  if($flag=="update"){
  $sql=$db->prepare('UPDATE balistars_hutang_mesin set 
    tanggalPembayaran=?,
    jumlahPembayaran=?,
    bankAsalTransfer=?,
    tanggalCair=?,
    noGiro=?,
    idUserEdit=?
    where idHutangMesin=?');
  $hasil=$sql->execute([
    $tanggalPembayaran,
    $jumlahPembayaran,
    $bankAsalTransfer,
    $tanggalCair,
    $noGiro,
    $idUserAsli,
    $idHutangMesin]);
  }
  else{
    $sql=$db->prepare('INSERT INTO balistars_hutang_mesin set 
      noNota=?,
      tanggalPembelian=?,
      tanggalPembayaran=?,
      jumlahPembayaran=?,
      jenisPembayaran=?,
      bankAsalTransfer=?,
      tanggalCair=?,
      noGiro=?,
      statusCair=?,
      idUser=?');
    $hasil=$sql->execute([
      $noNota,
      $tanggalPembelian,
      $tanggalPembayaran,
      $jumlahPembayaran,
      $jenisPembayaran,
      $bankAsalTransfer,
      $tanggalCair,
      $noGiro,
      "Belum Cair",
      $idUserAsli]);
    //var_dump($sql->errorInfo());
  }
}

$data = array('noNota' => $noNota, 'notifikasi' => 2);
if($hasil){
  $data = array('noNota' => $noNota, 'notifikasi' => 1);
}
echo json_encode($data);

?>