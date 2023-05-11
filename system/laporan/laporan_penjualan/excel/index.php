<?php
include_once '../../../../library/konfigurasiurl.php';
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
  'laporan_penjualan'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}
$sqlInformasi    = $db->query('SELECT * FROM balistars_information');
$dataInformasi = $sqlInformasi->fetch();
$logo            = $BASE_URL_HTML.'/assets/images/'.$dataInformasi['logo'];

//informasi user login
$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

?>
<!DOCTYPE html>
<html>
<head>
	<title>Export Laporan Penjualan</title>
<?php

extract($_REQUEST);

$rentang=explode(' - ',$rentang);
$tanggalAwal=konversiTanggal($rentang[0]);
$tanggalAkhir=konversiTanggal($rentang[1]);

$dataCabang=executeQueryUpdateForm('SELECT * FROM balistars_cabang where idCabang=?',$db,$idCabang);
if($dataCabang){
  $namaCabang=$dataCabang['namaCabang'];
}
else{
  $namaCabang="Semua Cabang";
}

if($tipe=='Semua'){
          $parameter1 = ' AND tipePenjualan != ?';
        } else{
          $parameter1 = ' AND tipePenjualan = ?';
        }

        if($idCabang=='0'){
          $parameter2 = ' AND balistars_penjualan.idCabang != ?';
        } else{
          $parameter2 = ' AND balistars_penjualan.idCabang = ?';
        }

        $sql=$db->prepare('SELECT *, balistars_penjualan.timeStampInput as waktuPenjualan 
          FROM balistars_penjualan 
          inner join balistars_cabang 
          on balistars_penjualan.idCabang=balistars_cabang.idCabang 
          where (balistars_penjualan.tanggalPenjualan between ? and ?)'
          .$parameter1 
          .$parameter2 
          .'order by balistars_penjualan.tanggalPenjualan');
        $sql->execute([
          $tanggalAwal,$tanggalAkhir,
          $tipe,
          $idCabang]);
        $hasil = $sql->fetchAll();

  ?>
  <!-- Bootstrap 3.3.6 -->
</head>
<?php   

$filename = 'Laporan Penjualan '.$namaCabang.' '.ubahTanggalIndo($tanggalAwal).'-'.ubahTanggalIndo($tanggalAkhir);
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=".$filename.".xls");
?>
<style type="text/css">
	body{
		font-family: sans-serif;
	}
	table{
		margin: 20px auto;
		border-collapse: collapse;
	}
	table td{
		border: 1px solid #3c3c3c;
		padding: 3px 8px;
 
	}
	a{
		background: blue;
		color: #fff;
		padding: 8px 10px;
		text-decoration: none;
		border-radius: 2px;
	}
</style>
<body>
 <table border="1">
  <tr>
    <td colspan="13" style="font-size: 17px; text-align: center;">Laporan Penjualan <?=$namaCabang?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></td>
  </tr>
  <tr>
      <td>No</td>
      <td style="width: 10%;">Tanggal</td>
      <td>Waktu</td>
      <td style="width: 8%;">No Nota</td>
      <td>Faktur Pajak</td>
      <td style="width: 20%;">Nama Cabang</td>
      <td style="width: 20%;">Customer</td>
      <td>Nilai Penjualan (Rp)</td>
      <td>Total Penjualan (Rp)</td>
      <td>Nilai PPN (Rp)</td>
      <td>Total Nilai PPN (Rp)</td>
      <td>Penjualan + PPN</td>
      <td>Total Penjualan + PPN</td>
      <?php 
        if($dataCekMenu['tipeA2']==1){
          ?>
        <th>A1/A2 </th>
        <?php
        } 
          ?>
  </tr>
  <tbody>
    <?php
      $n = 1;
        $totalPenjualan=0;
         $totalPPN=0;

        foreach($hasil as $row){
          $totalPenjualan+=$row['grandTotal']-$row['nilaiPPN'];
          $totalPPN+=$row['nilaiPPN'];
          if($row['idCustomer']>0){
            $konsumen ='pelanggan';
          } else{
            $konsumen ='umum';
          }
        ?>
        <tr>
          <td><?=$n?></td>
          <td><?=wordwrap(ubahTanggalIndo($row['tanggalPenjualan']),50,'<br>')?></td>
          <td><?=wordwrap($row['waktuPenjualan'],50,'<br>')?></td>
          <td><?=wordwrap($row['noNota'],50,'<br>')?></td>
          <td><?=wordwrap($row['noFakturPajak'],50,'<br>')?></td>
          <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
          <td><?=wordwrap($row['namaCustomer'],50,'<br>')?></td>
          <td><?=wordwrap(ubahToRp($row['grandTotal']-$row['nilaiPPN']),50,'<br>')?></td>
          <td><?=wordwrap(ubahToRp($totalPenjualan),50,'<br>')?></td>
          <td><?=wordwrap(ubahToRp($row['nilaiPPN']),50,'<br>')?></td>
          <td><?=wordwrap(ubahToRp($totalPPN),50,'<br>')?></td>
          <td><?=wordwrap(ubahToRp($row['grandTotal']),50,'<br>')?></td>
          <td><?=wordwrap(ubahToRp($totalPenjualan+$totalPPN),50,'<br>')?></td>
         <?php 
          if($dataCekMenu['tipeA2']==1){
            ?>
            <td><?=wordwrap($row['tipePenjualan'],50,'<br>')?></td>
          <?php
          } 
          ?>
        </tr>
        <?php
        $n++;
      }
      ?>
  </tbody>
</table>
</body>
</html>