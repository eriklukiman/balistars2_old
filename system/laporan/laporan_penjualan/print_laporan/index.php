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

extract($_REQUEST);

//$rentang1=$rentang;
$rentang=explode(' - ',$rentang);
$tanggalAwal=konversiTanggal($rentang[0]);
$tanggalAkhir=konversiTanggal($rentang[1]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/custom.css">
  <title>laporan Biaya</title>
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/toastr/toastr.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/select2/select2.css"> 
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/datepicker/datepicker.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/main2.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/color_skins.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/loader.css">
</head>
<?php  
$dataCabang=executeQueryUpdateForm('SELECT * FROM balistars_cabang where idCabang=?',$db,$idCabang);
if($dataCabang){
  $namaCabang=$dataCabang['namaCabang'];
}
else{
  $namaCabang="Semua Cabang";
}
?>
<body onload="doPrint()">
<div class="container">
 <table class="table table-bordered table-hover">
    <thead>
      <th colspan="14" style="font-size: 17px; text-align: center;">Laporan Penjualan <?=$namaCabang?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
     <thead>
      <tr>
        <th>No</th>
        <th>Tanggal Penjualan</th>
        <th>Waktu</th>
        <th>No Nota</th>
        <th>Faktur Pajak </th>
        <th>Nama Cabang </th>
        <th>Customer</th>
        <th>Nilai Penjualan</th>
        <th>Total Penjualan</th>
        <th>Nilai PPN</th>
        <th>Total PPN</th>
        <th>Penjualan + PPN</th>
        <th>Total Penjualan + PPN</th>
        <?php 
        if($dataCekMenu['tipeA2']==1){
          ?>
        <th>A1/A2 </th>
        <?php
        } 
          ?>
      </tr>
    </thead>
    <tbody>
      <?php 
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

        $sql=$db->prepare('SELECT *, balistars_penjualan.timeStamp as waktuPenjualan 
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
</div>
  <script>
  function doPrint() {
    window.print();
    window.onafterprint=function(event){
      window.close();
    };            
  }
  </script>
</body>
</html>