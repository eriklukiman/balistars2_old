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
  'laporan_pembelian_mesin'
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

if($idCabang=='0'){
  $parameter1 = ' AND idCabang != ?';
} else{
  $parameter1 = ' AND idCabang = ?';
}

if($tipe=='Semua'){
  $parameter2 = ' AND tipePembelian != ?';
} else{
  $parameter2 = ' AND tipePembelian = ?';
}

$sql = $db->prepare('SELECT * from balistars_pembelian_mesin 
  where (tanggalPembelian between ? and ?)
  and statusPembelianMesin=?' 
  .$parameter1 
  .$parameter2
  .'order by tanggalPembelian');
  $sql->execute([
    $tanggalAwal,
    $tanggalAkhir,
    'Aktif',
    $idCabang,
    $tipe]);
$data=$sql->fetchAll();

$n=1;
$totalDPP        = 0;
$totalPPN        = 0;
$totalGrandTotal = 0;
foreach($data as $row){
 ?>
 <tr>
   <td><?=$n?></td>
   <td><?=wordwrap(ubahTanggalIndo($row['tanggalPembelian']),50,'<br>')?></td>
   <td><?=wordwrap($row['noNota'],50,'<br>')?></td>
   <td><?=wordwrap($row['namaSupplier'],50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['grandTotal']-$row['nilaiPPN']),50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['nilaiPPN']),50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['grandTotal']),50,'<br>')?></td>
 </tr>
 <?php 
  $n++;
  $totalDPP+=$row['grandTotal']-$row['nilaiPPN'];
  $totalPPN+=$row['nilaiPPN'];
  $totalGrandTotal+=$row['grandTotal'];
  }
  ?>
<tr style="font-weight: bold;">
  <td colspan="4">Grand Total :</td>
  <td><?=UbahToRp($totalDPP)?></td>
  <td><?=UbahToRp($totalPPN)?></td>
  <td><?=UbahToRp($totalGrandTotal)?></td>
</tr>