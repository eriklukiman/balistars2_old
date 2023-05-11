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
  'laporan_pphppn'
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
if($idCabang==0){
  $parameter1=' and balistars_penjualan.idCabang !=?';
} else {
  $parameter1=' and balistars_penjualan.idCabang =?';
}

if($jenisBiaya=="PPH"){
  $sqlBiaya=$db->prepare('SELECT *, PPH as biaya 
    FROM balistars_piutang 
    inner join balistars_penjualan 
    on balistars_piutang.noNota=balistars_penjualan.noNota 
    where (tanggalPembayaran between ? and ?) 
    and statusPenjualan=?' 
    .$parameter1);
  $sqlBiaya->execute([
    $tanggalAwal,$tanggalAkhir,
    'Aktif',
    $idCabang]);
  $dataBiaya=$sqlBiaya->fetchAll();
}
else if($jenisBiaya=="PPN"){
  $sqlBiaya=$db->prepare('SELECT *, jumlahPembayaran as biaya 
    FROM balistars_piutang 
    inner join balistars_penjualan 
    on balistars_piutang.noNota=balistars_penjualan.noNota 
    where (tanggalPembayaran between ? and ?) 
    and statusPenjualan=? 
    and balistars_piutang.jenisPembayaran=?'
    .$parameter1);
  $sqlBiaya->execute([
    $tanggalAwal,$tanggalAkhir,
    'Aktif',
    'PPN',
    $idCabang]);
  $dataBiaya=$sqlBiaya->fetchAll();
}
else if ($jenisBiaya=="Biaya Admin"){
  $sqlBiaya=$db->prepare('SELECT *, biayaAdmin as biaya 
    FROM balistars_piutang 
    inner join balistars_penjualan 
    on balistars_piutang.noNota=balistars_penjualan.noNota 
    where (tanggalPembayaran between ? and ?) 
    and statusPenjualan=?'
    .$parameter1);
  $sqlBiaya->execute([
    $tanggalAwal,$tanggalAkhir,
    'Aktif',
    $idCabang]);
  $dataBiaya=$sqlBiaya->fetchAll();
}

$n=0;
$totalBiaya=0;
foreach($dataBiaya as $row){
  if($row['biaya']>0){
  $n++;
 ?>
<tr>
  <td><?=$n?></td>
  <td><?=wordwrap(ubahTanggalIndo($row['tanggalPenjualan']),50,'<br>')?></td>
  <td><?=wordwrap($row['noNota'],50,'<br>')?></td>
  <td><?=wordwrap(ubahTanggalIndo($row['tanggalPembayaran']),50,'<br>')?></td>
  <td>
   <?php 
      if($row['idCustomer']=='0' || $row['idCustomer']==0){
        echo $row['namaCustomer'];
      }
      else{
        $sqlCustomer=$db->prepare('SELECT  namaCustomer FROM balistars_customer where idCustomer=?');
        $sqlCustomer->execute([$row['idCustomer']]);
        $dataCustomer=$sqlCustomer->fetch();
        echo $dataCustomer['namaCustomer'];
      } 
    ?>   
  </td>
  <td>Rp <?=wordwrap(ubahToRp($row['biaya']),50,'<br>')?></td>
</tr>
  <?php
  $totalBiaya+=$row['biaya'];
  }
}
 ?>
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td>Total</td>  
  <td>Rp <?=ubahToRp($totalBiaya)?></td>
</tr> 