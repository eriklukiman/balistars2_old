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
  'laporan_penjualan_ratarata'
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

$arrayOmset = array();
$arrayAverage = array();
$arrayJenis = array('Outdoor','Laser','UV/Indoor','Advertising','Lain - Lain');
$n=0; 
$daily=0;
for ($i=0; $i < count($arrayJenis) ; $i++) {
  if($i==2){
    $sqlPenjualan=$db->prepare('SELECT SUM(nilai) as omset, count(idPenjualanDetail) as totalBanyak 
      FROM balistars_penjualan_detail 
      inner join balistars_penjualan 
      on balistars_penjualan_detail.noNota=balistars_penjualan.noNota 
      where (balistars_penjualan_detail.jenisOrder=? 
        or balistars_penjualan_detail.jenisOrder=?) 
      and balistars_penjualan.statusFinalNota=? 
      and balistars_penjualan_detail.statusPenjualan=?
      and (balistars_penjualan.tanggalPenjualan between ? and ?) 
      and balistars_penjualan.idCabang=? 
      and balistars_penjualan_detail.statusCancel=?');
    $sqlPenjualan->execute([
      'UV',
      'Indoor',
      'final',
      'Aktif',
      $tanggalAwal,$tanggalAkhir,
      $idCabang,
      'ok']);
    $dataPenjualan=$sqlPenjualan->fetch();
    $daily=$dataPenjualan['omset'];
  }
  else{
    $sqlPenjualan=$db->prepare('SELECT SUM(nilai) as omset, count(idPenjualanDetail) as totalBanyak 
      FROM balistars_penjualan_detail 
      inner join balistars_penjualan 
      on balistars_penjualan_detail.noNota=balistars_penjualan.noNota 
      where balistars_penjualan_detail.jenisOrder=? 
      and balistars_penjualan.statusFinalNota=? 
      and balistars_penjualan.statusPenjualan=?
      and (balistars_penjualan.tanggalPenjualan between ? and ?) 
      and balistars_penjualan.idCabang=? 
      and balistars_penjualan_detail.statusCancel=?');
    $sqlPenjualan->execute([
      $arrayJenis[$i],
      'final',
      'Aktif',
      $tanggalAwal,$tanggalAkhir,
      $idCabang,
      'ok']);
    $dataPenjualan=$sqlPenjualan->fetch();
    $daily=$dataPenjualan['omset'];
  }
  $arrayOmset[]=$daily;
  $arrayAverage[]=$dataPenjualan['totalBanyak'];
  $n++;
  var_dump($arrayOmset);
}

$n=0; 
for ($i=0; $i < count($arrayJenis) ; $i++) { 
  if($arrayAverage[$i]==0){
    $pembilang=1;
  }
  else{
    $pembilang=$arrayAverage[$i];
  }
?>
  <tr>
    <td><?=$arrayJenis[$i]?></td>
    <td>Rp <?=ubahToRp($arrayOmset[$i])?></td>
    <?php 
    if(array_sum($arrayOmset)==0){
      ?>
    <td>0</td>
    <?php
    }else{ ?>
    <th><?=round($arrayOmset[$i]*100/array_sum($arrayOmset),2)?>%</th>
    <?php 
    } ?>
    <td>Rp <?=ubahToRp($arrayOmset[$i]/$pembilang)?></td>
  </tr>
  <?php
  $n++;
  $totalOmset+=$arrayOmset[$i];
  $totalRatio+=round($arrayOmset[$i]*100/array_sum($arrayOmset),2);
  $totalAS+=$arrayOmset[$i]/$pembilang;
 }
 ?>
 <tr>
   <td><b>Total</b></td>
   <td><b><?=ubahToRp($totalOmset)?></b></td>
   <td><b><?=ubahToRp($totalRatio)?>%</b></td>
   <td><b><?=ubahToRp($totalAS)?></b></td>
 </tr>