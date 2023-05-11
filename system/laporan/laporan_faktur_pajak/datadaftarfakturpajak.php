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
  'laporan_faktur_pajak'
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

$sql=$db->prepare('SELECT * 
  FROM balistars_penjualan 
  inner join balistars_cabang 
  on balistars_penjualan.idCabang=balistars_cabang.idCabang 
  where (balistars_penjualan.tanggalPenjualan between ? and ?) 
  and balistars_penjualan.statusFakturPajak=? 
  and noFakturPajak!=? 
  and statusPenjualan=?
  order by balistars_penjualan.idCabang');
$sql->execute([
  $tanggalAwal,$tanggalAkhir,
  'Dengan Faktur',
  "",
  'Aktif']);
$hasil=$sql->fetchAll();

$totalNilaiPenjualan=0; $totalNilaiPembayaran=0;
$totalProfit=0;
$n=1;
foreach($hasil as $row){
 ?>

<tr>
  <td><?=$n?></td>
  <td><?=wordwrap($row['noFakturPajak'],50,'<br>')?></td>
  <td>
  <?php 
    $sqlCustomer=$db->prepare('SELECT * FROM balistars_customer where idCustomer=?');
    $sqlCustomer->execute([$row['idCustomer']]);
    $dataCustomer=$sqlCustomer->fetch();
    if($dataCustomer){
      echo $dataCustomer['namaCustomer'];
    }
    else{
      echo $row['namaCustomer'];
    }
  ?>    
  </td>
  <td><?=wordwrap($dataCustomer['NPWP'],50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($row['grandTotal']-$row['nilaiPPN']),50,'<br>')?></td>
  <td><?=wordwrap($row['nilaiPPN'],50,'<br>')?></td>
  <td><?=wordwrap(ubahTanggalIndo($row['tanggalPenjualan']),50,'<br>')?></td>
  <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
  <td><?=wordwrap($row['statusFinalNota'],50,'<br>')?></td>
  <td><?=wordwrap($row['cabangCustomer'],50,'<br>')?></td>
  <td><?=wordwrap($row['noNota'],50,'<br>')?></td>
</tr>
  <?php
  $n++;
 }
 ?>

