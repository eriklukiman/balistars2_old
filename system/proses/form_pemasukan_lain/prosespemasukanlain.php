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
  'form_pemasukan_lain'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag == 'final'){
  $sql = $db->prepare('UPDATE balistars_pemasukan_lain set statusFinal = ?, idUserEdit=? where idPemasukanLain = ?');
  $hasil = $sql->execute(['Final',$idUserAsli, $idPemasukanLain]);
}
else if($flag=='buka'){
  $sql = $db->prepare('UPDATE balistars_pemasukan_lain set statusFinal = ?, idUserEdit=? where idPemasukanLain = ?');
  $hasil = $sql->execute(['Belum Final',$idUserAsli, $idPemasukanLain]);
}
else{
  $nilai=ubahToInt($nilai);
  $tanggalPemasukanLain=konversiTanggal($tanggalPemasukanLain);
  if($flag == 'update'){
    $sql = $db->prepare('UPDATE balistars_pemasukan_lain set 
      idKodePemasukan   = ?,
      idBank    = ?,
      tanggalPemasukanLain = ?,
      nilai = ?,
      statusFinal = ?,
      keterangan       =?,
      idUserEdit         =?
      where idPemasukanLain = ?');
    $hasil = $sql->execute([
      $idKodePemasukan,
      $idBank,
      $tanggalPemasukanLain,
      $nilai,
      'Belum Final',
      $keterangan,
      $idUserAsli,
      $idPemasukanLain]);
  }
  else{
    $sql = $db->prepare('INSERT INTO balistars_pemasukan_lain set 
      idKodePemasukan   = ?,
      idBank    = ?,
      tanggalPemasukanLain = ?,
      nilai = ?,
      statusFinal = ?,
      keterangan       =?,
      idUser        =?');
    $hasil = $sql->execute([
      $idKodePemasukan,
      $idBank,
      $tanggalPemasukanLain,
      $nilai,
      'Belum Final',
      $keterangan,
      $idUserAsli]);
  }
}

$data = array('flag' => $flag, 'notifikasi' => 2);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1);
}
echo json_encode($data);

?>