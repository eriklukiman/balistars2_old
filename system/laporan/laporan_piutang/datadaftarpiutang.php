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
  'laporan_piutang'
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
  $parameter1 =' and balistars_penjualan.idCabang !=?';
} else{
  $parameter1 =' and balistars_penjualan.idCabang =?';
}
if($tipe=='Semua'){
  $parameter2 =' and balistars_penjualan.tipePenjualan !=?';
} else{
  $parameter2 =' and balistars_penjualan.tipePenjualan =?';
}

if($idCustomer==0){
  $sqlAwal=$db->prepare('SELECT MIN(sisaPiutang) as sisaPiutang, balistars_penjualan.grandTotal as grandTotal 
    FROM balistars_piutang 
    inner join balistars_penjualan 
    on balistars_piutang.noNota=balistars_penjualan.noNota 
    WHERE balistars_penjualan.statusFinalNota=? 
    and balistars_penjualan.tanggalPenjualan<?
    and balistars_penjualan.statusPenjualan=?' 
    .$parameter1 
    .$parameter2   
    .'and balistars_penjualan.noNota 
    NOT IN (SELECT noNota FROM balistars_pemutihan_piutang) 
    GROUP BY balistars_penjualan.noNota');
  $sqlAwal->execute([
    "final",
    $tanggalAwal,
    'Aktif',
    $idCabang,
    $tipe]);
} else {
  $sqlAwal=$db->prepare('SELECT MIN(sisaPiutang) as sisaPiutang, balistars_penjualan.grandTotal as grandTotal 
    FROM balistars_piutang 
    inner join balistars_penjualan 
    on balistars_piutang.noNota=balistars_penjualan.noNota 
    WHERE balistars_penjualan.statusFinalNota=? 
    and balistars_penjualan.tanggalPenjualan<?
    and balistars_penjualan.idCustomer=?
    and balistars_penjualan.statusPenjualan=?' 
    .$parameter1 
    .$parameter2   
    .'and balistars_penjualan.noNota 
    NOT IN (SELECT noNota FROM balistars_pemutihan_piutang) 
    GROUP BY balistars_penjualan.noNota');
  $sqlAwal->execute([
    "final",
    $tanggalAwal,
    $idCustomer,
    'Aktif',
    $idCabang,
    $tipe]);
}

$dataAwal=$sqlAwal->fetchAll();

$totalPiutang=0;
$totalPenjualan=0;

foreach ($dataAwal as $row) {
  $totalPiutang+=$row['sisaPiutang'];
  $totalPenjualan+=$row['grandTotal'];
}
$totalPembayaran=$totalPenjualan-$totalPiutang;
?>
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td colspan="2"><b>Piutang Awal</b></td>
  <td><?=ubahToRp($totalPenjualan)?></td>
  <td><?=ubahToRp($totalPiutang)?></td>
  <td></td>
  <td></td>
</tr>

<?php 
if($idCustomer==0){
  $sql=$db->prepare('SELECT *, MIN(sisaPiutang) as sisaPiutang2 
    FROM balistars_penjualan 
    inner join balistars_piutang 
    on balistars_penjualan.noNota=balistars_piutang.noNota 
    where balistars_penjualan.statusPembayaran=? 
    and (balistars_penjualan.tanggalPenjualan between ? and ?)
    and balistars_penjualan.statusPenjualan=?'
    .$parameter1
    .$parameter2 
    .'and balistars_penjualan.noNota 
    NOT IN (SELECT noNota FROM balistars_pemutihan_piutang) 
    group by balistars_piutang.noNota 
    ORDER BY balistars_penjualan.tanggalPenjualan');
  $sql->execute([
    'Belum Lunas',
    $tanggalAwal,$tanggalAkhir,
    'Aktif',
    $idCabang,
    $tipe]);
} else{
  $sql=$db->prepare('SELECT *, MIN(sisaPiutang) as sisaPiutang2 
  FROM balistars_penjualan 
  inner join balistars_piutang 
  on balistars_penjualan.noNota=balistars_piutang.noNota 
  where balistars_penjualan.statusPembayaran=? 
  and (balistars_penjualan.tanggalPenjualan between ? and ?)
  and balistars_penjualan.idCustomer=?
  and balistars_penjualan.statusPenjualan=?'
  .$parameter1
  .$parameter2 
  .'and balistars_penjualan.noNota 
  NOT IN (SELECT noNota FROM balistars_pemutihan_piutang) 
  group by balistars_piutang.noNota 
  ORDER BY balistars_penjualan.tanggalPenjualan');
$sql->execute([
  'Belum Lunas',
  $tanggalAwal,$tanggalAkhir,
  $idCustomer,
  'Aktif',
  $idCabang,
  $tipe]);
}
$hasil=$sql->fetchAll();
//var_dump($sql->errorInfo());
$totalPiutangHarian=0;
$n=1;
foreach($hasil as $data){
  $umurPiutang=selisihTanggal(date('Y-m-d'),$data['tanggalPenjualan']);
  $totalPiutang+=$data['sisaPiutang2'];
  $totalPiutangHarian+=$data['sisaPiutang2'];
      $totalPenjualan+=$data['grandTotal'];
 ?>

<tr>
  <td><?=$n?></td>
  <td><?=wordwrap($data['noNota'],50,'<br>')?></td>
  <td><?=wordwrap(ubahTanggalIndo($data['tanggalPenjualan']),50,'<br>')?></td>
  <td><?=wordwrap($umurPiutang,50,'<br>')?> Hari</td>
  <td><?=wordwrap($data['namaCustomer'],50,'<br>')?></td>
  <td>
    <?php  
      if($data['idCustomer']==0){
        echo $data['noTelpCustomer'];
      }
      else{
        $sqlCustomer=$db->prepare('SELECT * FROM balistars_customer where idCustomer=?');
        $sqlCustomer->execute([$data['idCustomer']]);
        $dataCustomer=$sqlCustomer->fetch();
        echo $dataCustomer['noTelpCustomer'];
      }
      ?>
  </td>
  <td><?=wordwrap(ubahToRp($data['grandTotal']),50,'<br>')?></td>
  <td><?=wordwrap(ubahToRp($data['sisaPiutang2']),50,'<br>')?></td>
  <td><?=wordwrap(ubahToRp($totalPiutangHarian),50,'<br>')?></td>
  <?php 
  if($dataCekMenu['tipeA2']==1){
    ?>
    <td><?=wordwrap($data['tipePenjualan'],50,'<br>')?></td>
  <?php
  } ?>
</tr>
  <?php
  $totalPembayaran=$totalPenjualan-$totalPiutang;
  $n++;
 }
 ?>
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td style="font-weight: bold;" colspan="2">Total Penjualan / Total Pembayaran / Piutang</td>
  <td style="font-weight: bold;"><?=ubahToRp($totalPenjualan)?></td>
  <td style="font-weight: bold;"><?=ubahToRp($totalPiutang)?></td>
  <td></td>
  <td></td>
</tr>
