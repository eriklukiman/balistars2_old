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
  <title>laporan Piutang</title>
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
      <th colspan="14" style="font-size: 17px; text-align: center;">Laporan Piutang <?=$namaCabang?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
     <thead>
      <tr>
        <th>No</th>
        <th>No Nota</th>
        <th>Tanggal</th>
        <th>Umur Piutang</th>
        <th>Customer</th>
        <th>no Telepon</th>
        <th>Nilai Penjualan</th>
        <th>Piutang</th>
        <th>Total Piutang Harian</th>
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
            and balistars_penjualan.tanggalPenjualan<?' 
            .$parameter1 
            .$parameter2   
            .'and balistars_penjualan.noNota 
            NOT IN (SELECT noNota FROM balistars_pemutihan_piutang) 
            GROUP BY balistars_penjualan.noNota');
          $sqlAwal->execute([
            "final",
            $tanggalAwal,
            $idCabang,
            $tipe]);
        } else {
          $sqlAwal=$db->prepare('SELECT MIN(sisaPiutang) as sisaPiutang, balistars_penjualan.grandTotal as grandTotal 
            FROM balistars_piutang 
            inner join balistars_penjualan 
            on balistars_piutang.noNota=balistars_penjualan.noNota 
            WHERE balistars_penjualan.statusFinalNota=? 
            and balistars_penjualan.tanggalPenjualan<?
            and balistars_penjualan.idCustomer=?' 
            .$parameter1 
            .$parameter2   
            .'and balistars_penjualan.noNota 
            NOT IN (SELECT noNota FROM balistars_pemutihan_piutang) 
            GROUP BY balistars_penjualan.noNota');
          $sqlAwal->execute([
            "final",
            $tanggalAwal,
            $idCustomer,
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
            and (balistars_penjualan.tanggalPenjualan between ? and ?)'
            .$parameter1
            .$parameter2 
            .'and balistars_penjualan.noNota 
            NOT IN (SELECT noNota FROM balistars_pemutihan_piutang) 
            group by balistars_piutang.noNota 
            ORDER BY balistars_penjualan.tanggalPenjualan');
          $sql->execute([
            'Belum Lunas',
            $tanggalAwal,$tanggalAkhir,
            $idCabang,
            $tipe]);
        } else{
          $sql=$db->prepare('SELECT *, MIN(sisaPiutang) as sisaPiutang2 
          FROM balistars_penjualan 
          inner join balistars_piutang 
          on balistars_penjualan.noNota=balistars_piutang.noNota 
          where balistars_penjualan.statusPembayaran=? 
          and (balistars_penjualan.tanggalPenjualan between ? and ?)
          and balistars_penjualan.idCustomer=?'
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
          $idCabang,
          $tipe]);
        }
        $hasil=$sql->fetchAll();
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