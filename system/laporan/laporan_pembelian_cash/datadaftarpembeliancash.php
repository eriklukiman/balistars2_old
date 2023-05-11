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
  'laporan_pembelian_cash'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 

$sqlPembelian=$db->prepare('
  SELECT SUM(balistars_pembelian.grandTotal) as total, balistars_cabang.namaCabang 
  FROM balistars_pembelian 
  inner join balistars_cabang 
  on balistars_pembelian.idCabang=balistars_cabang.idCabang 
  where idSupplier=?
  and status =? 
  and (tanggalPembelian between ? and ?) 
  group by balistars_pembelian.idCabang');
$sqlPembelian->execute([
  0,
  'Aktif',
  $tanggalAwal,$tanggalAkhir]);
$dataPembelian=$sqlPembelian->fetchAll();
$n=1;
foreach($dataPembelian as $row){
 ?>
 <tr>
   <td><?=$n?></td>
   <td><?=$row['namaCabang']?></td>
   <td>Rp <?=ubahToRp($row['total'])?></td>
 </tr>
 <?php 
 $n++;
  }
  ?>