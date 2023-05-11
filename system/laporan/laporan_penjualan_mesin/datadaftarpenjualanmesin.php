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
  'laporan_penjualan_mesin'
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

$sqlPenjualanMesin=$db->prepare('SELECT * 
  FROM balistars_penjualan_mesin 
  inner join balistars_pembelian_mesin_detail 
  on balistars_penjualan_mesin.idPembelianDetail=balistars_pembelian_mesin_detail.idPembelianDetail 
  inner join balistars_pembelian_mesin 
  on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
  where (tanggalPenjualan between ? and ?) 
  and statusPenjualanMesin =?
  order by tanggalPenjualan DESC');
$sqlPenjualanMesin->execute([
  $tanggalAwal,$tanggalAkhir,
  'Aktif']);
$dataPenjualanMesin=$sqlPenjualanMesin->fetchAll();

$n=1;
$grandTotalDPP = 0;
$grandTotalPPN = 0;
$grandTotal = 0;
foreach($dataPenjualanMesin as $row){
 ?>
 <tr>
   <td><?=$n?></td>
   <td><?=wordwrap(ubahTanggalIndo($row['tanggalPenjualan']),50,'<br>')?></td>
   <td><?=wordwrap($row['namaBarang'],50,'<br>')?></td>
   <td><?=wordwrap($row['keterangan'],50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['dpp']),50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['ppn']),50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['dpp']+$row['ppn']),50,'<br>')?></td>
   <td><?=wordwrap($row['tipePembelian'],50,'<br>')?></td>
 </tr>
 <?php 
  $n++;
  $grandTotalDPP = $grandTotalDPP + $row['dpp']; 
  $grandTotalPPN = $grandTotalPPN + $row['ppn'];
  $grandTotal = $grandTotal + $row['dpp']+$row['ppn'];
  }
  ?>
<tr>
  <td colspan="1"></td>
  <td><b>Grand Total</b></td>
  <td></td>
  <td></td>
  <td><?=ubahToRp($grandTotalDPP)?></td>
  <td><?=ubahToRp($grandTotalPPN)?></td>
  <td><?=ubahToRp($grandTotal)?></td>
  <td></td>
</tr>