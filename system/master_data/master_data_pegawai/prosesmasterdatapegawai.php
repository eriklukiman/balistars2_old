<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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
  'master_data_pegawai'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}
$tanggal=date('Y-m-d');
extract($_REQUEST);

if($flag == 'cancel'){
  $sql=$db->prepare('UPDATE balistars_pegawai set 
    statusPegawai=?, 
    tglNonAktif=?, 
    idUserEdit=? 
    where idPegawai=?');
  $hasil=$sql->execute([
    'Non Aktif',
    $tanggal, 
    $idUserAsli,
    $idPegawai]);

  $sqlUser=$db->prepare('DELETE FROM balistars_user where idPegawai=?');
  $hasilUser=$sqlUser->execute([$idPegawai]);


}
else {
  $tglMulaiKerja = konversiTanggal($tglMulaiKerja);
  if($flag == 'update'){
    $sql = $db->prepare('UPDATE balistars_pegawai set 
      namaPegawai    = ?,
      NIK            = ?,
      alamatPegawai  = ?,
      tglMulaiKerja  = ?,
      noTelpPegawai  = ?,
      idJabatan      = ?,
      idCabang       = ?,
      idCabangAdvertising = ?,
      statusPegawai  =?,
      idUserEdit         =?
      where idPegawai = ?');
    $hasil = $sql->execute([
      $namaPegawai,
      $NIK,
      $alamatPegawai,
      $tglMulaiKerja,
      $noTelpPegawai,
      $idJabatan,
      $idCabang,
      $idCabangAdvertising,
      $statusPegawai,
      $idUserAsli,
      $idPegawai]);
  }
  else{
    $sql = $db->prepare('INSERT INTO balistars_pegawai set 
      namaPegawai    = ?,
      NIK            = ?,
      alamatPegawai  = ?,
      tglMulaiKerja  = ?,
      noTelpPegawai  = ?,
      idJabatan      = ?,
      idCabang       = ?,
      idCabangAdvertising = ?,
      statusPegawai  = ?,
      idUser        =?');
    $hasil = $sql->execute([
      $namaPegawai,
      $NIK,
      $alamatPegawai,
      $tglMulaiKerja,
      $noTelpPegawai,
      $idJabatan,
      $idCabang,
      $idCabangAdvertising,
      $statusPegawai,
      $idUserAsli]);
  }
} 

$data = array('flag' => $flag, 'notifikasi' => 2, 'parameterOrder' => $parameterOrder);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1, 'parameterOrder' => $parameterOrder);
}
echo json_encode($data);

?>