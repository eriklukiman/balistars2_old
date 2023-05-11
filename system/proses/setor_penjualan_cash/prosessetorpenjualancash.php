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
  'setor_penjualan_cash'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag == 'buka'){
  $sql = $db->prepare('UPDATE balistars_setor_penjualan_cash set 
    statusFinal=?,
    idUserEdit=?
    where idSetor=?');
  $hasil = $sql->execute([
    'Belum Final',
    $idUserAsli, 
    $idSetor]);
  //var_dump($sql->errorInfo());
}
else if($flag=='finalisasi'){
  $sql = $db->prepare('UPDATE balistars_setor_penjualan_cash set 
    statusFinal=?,
    idUserEdit=?
    where idSetor=?');
  $hasil = $sql->execute([
    'Final',
    $idUserAsli, 
    $idSetor]);
}
else{
  $jumlahSetor=ubahToInt($jumlahSetor);
  $tanggalSetor=konversiTanggal($tanggalSetor);

  if($flag == 'update'){
    $sql = $db->prepare('UPDATE balistars_setor_penjualan_cash set 
      idBank=?,
      tanggalSetor=?,
      jumlahSetor=?,
      statusFinal=?,
      idUserEdit=?
      where idSetor=?');
    $hasil = $sql->execute([
      $idBank,
      $tanggalSetor,
      $jumlahSetor,
      'Belum Final',
      $idUserAsli, 
      $idSetor]);
  }
}

$data = array('flag' => $flag, 'notifikasi' => 2);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1);
}
echo json_encode($data);

?>