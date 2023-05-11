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
  'pembayaran_piutang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalPembayaran=konversiTanggal($tanggalPembayaran);
$tanggalPenjualan=konversiTanggal($tanggalPenjualan);
$jumlahPembayaran=ubahToInt($jumlahPembayaran);
$sisaPiutangAwal=ubahToInt($sisaPiutangAwal);
$sisaPiutang=ubahToInt($sisaPiutang);
$grandTotal=ubahToInt($grandTotal);
$biayaAdmin=ubahToInt($biayaAdmin);
$PPH=ubahToInt($PPH);

if($jumlahPembayaran<=0 || $jumlahPembayaran=="" || $jumlahPembayaran=="0"){
  $jumlahPembayaran=0;
}

if($sisaPiutangAwal<=0 || $sisaPiutangAwal=="" || $sisaPiutangAwal=="0"){
  $sisaPiutangAwal=0;
}

if($biayaAdmin<=0 || $biayaAdmin=="" || $biayaAdmin=="0"){
  $biayaAdmin=0;
}
if($PPH<=0 || $PPH=="" || $PPH=="0"){
  $PPH=0;
}

if($jumlahPembayaran>=$sisaPiutangAwal){
  $jumlahPembayaran=$sisaPiutangAwal;
  $sisaPiutang=0;
  $statusPembayaran="Lunas";
}
else{
  $statusPembayaran="Belum Lunas";
}

$sqlCheck=$db->prepare('SELECT *, MIN(sisaPiutang) as sisaPiutangKecil 
  FROM balistars_piutang 
  where noNota=?');
$sqlCheck->execute([$noNota]);
$dataCheck=$sqlCheck->fetch();

if($dataCheck['sisaPiutangKecil']>$sisaPiutang){
  $sqlUpdatePenjualan=$db->prepare('UPDATE balistars_penjualan set statusPembayaran=?,
    idUserEdit=?
    where noNota=?');
  $hasilUpdatePenjualan=$sqlUpdatePenjualan->execute([
    $statusPembayaran,
    $idUserAsli,
    $noNota]);

  $sqlInsertPiutang=$db->prepare('INSERT IGNORE INTO balistars_piutang set noNota=?,
    tanggalPenjualan=?,
    tanggalPembayaran=?,
    grandTotal=?,
    jumlahPembayaran=?,
    sisaPiutang=?,
    jenisPembayaran=?,
    bankTujuanTransfer=?,
    biayaAdmin=?,
    PPH=?,
    idUser=?');
  $hasilInsertPiutang=$sqlInsertPiutang->execute([
    $noNota,
    $tanggalPenjualan,
    $tanggalPembayaran,
    $grandTotal,
    $jumlahPembayaran,
    $sisaPiutang,
    $jenisPembayaran,
    $bankTujuanTransfer,
    $biayaAdmin,
    $PPH,
    $idUserAsli]);
}

$data = array('noNota' => $noNota, 'notifikasi' => 2);
if($hasilUpdatePenjualan && $hasilInsertPiutang){
  $data = array('noNota' => $noNota, 'notifikasi' => 1);
}
echo json_encode($data);

?>