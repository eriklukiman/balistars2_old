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
if($tahun2<$tahun1){
  echo"Rentang yang anda pilih salah!!";
}
else if($tahun2==$tahun1 && $bulan2<$bulan1){
  echo"Rentang yang anda pilih salah!!";
}
else{
  $rentang = $bulan2 - $bulan1; 
  if($rentang<=0 and $tahun2!=$tahun1){
    $rentang = $rentang + 12;
  }

  $indexBulan = $bulan1;
  $valueBulan = array("","01","02","03","04","05","06","07","08","09","10","11","12");
  $namaBulan = array("","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
  $tanggalAwal = array();
  $tanggalAkhir = array();
  $tahunPeriode = array();

  for($i=0; $i<=$rentang; $i++){
    if($indexBulan>12){
      $indexBulan=1;
      $tahun1++;
    }
    $tahunPeriode[$i] = $tahun1;
    $bulanPilih[$i] = $valueBulan[$indexBulan];
    $bulanPilihNama[$i] = $namaBulan[$indexBulan];
    $indexBulan=$indexBulan+1;
  }

  for($i=0; $i<count($bulanPilih); $i++){
    $tanggalAwal[$i] = $tahunPeriode[$i]."-".$bulanPilih[$i]."-01";
    $tanggalAkhir[$i] = $tahunPeriode[$i]."-".$bulanPilih[$i]."-31";
  }
  if($tipePenjualan=="A1" || $tipePenjualan=="A2"){
    $sqlPiutang=$db->prepare('SELECT (MIN(sisaPiutang)) as piutang FROM balistars_piutang inner join balistars_penjualan on balistars_piutang.noNota=balistars_penjualan.noNota where balistars_penjualan.idCabang=?  and (balistars_penjualan.tanggalPenjualan between ? and ?) and balistars_penjualan.tipePenjualan=? group by balistars_piutang.noNota');
  }
  else{
    $sqlPiutang=$db->prepare('SELECT MIN(sisaPiutang) as piutang FROM balistars_piutang inner join balistars_penjualan on balistars_piutang.noNota=balistars_penjualan.noNota where balistars_penjualan.idCabang=?  and (balistars_penjualan.tanggalPenjualan between ? and ?) group by balistars_piutang.noNota');
  }
 ?>
<body onload="doPrint()">
<div class="container">
 <table class="table table-bordered table-hover">
    <thead>
      <th colspan="20" style="font-size: 17px; text-align: center;">Laporan Piutang Gabungan <br> <?=namaBulan($bulan1).' '.$tahun1?> - <?=namaBulan($bulan2).' '.$tahun2?></th>
    </thead>
     <thead>
      <tr>
        <th style="width: 5%;">No</th>
        <th style="width: 15%;">Cabang</th>
        <?php
        for($i=0; $i<count($bulanPilihNama); $i++){
          ?>
          <th><?=$bulanPilihNama[$i]?> (Rp)</th>
          <?php
        }
        ?>
        <th>Total Piutang</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $n=1;
      $totalPiutangGlobal=0;
      $dataCabang=$db->query('SELECT * FROM balistars_cabang order by namaCabang');
      foreach($dataCabang as $row){
        ?>
        <tr>
          <td><?=$n?></td>
          <td><?=$row['namaCabang']?></td>
          <?php
          $totalPiutang=0;
          for($i=0; $i<count($bulanPilihNama); $i++){
            if($tipePenjualan=="A1" || $tipePenjualan=="A2"){
              $sqlPiutang->execute([$row['idCabang'], $tanggalAwal[$i], $tanggalAkhir[$i],$tipePenjualan]);
            }
            else{
              $sqlPiutang->execute([$row['idCabang'], $tanggalAwal[$i], $tanggalAkhir[$i]]);
            }
            $dataPiutang=$sqlPiutang->fetchAll();
            $piutang=0;
            foreach ($dataPiutang as $cek) {
              $piutang=$piutang+$cek['piutang'];
            }
            ?>
            <td><?=ubahToRp($piutang)?></td>
            <?php
            $totalPiutang+=$piutang;
          }
          ?>
          <td><?=ubahToRp($totalPiutang)?></td>
        </tr>
        <?php
        $n++;
        $totalPiutangGlobal+=$totalPiutang;
      }
      ?>
      <tr>
        <td></td>
        <td colspan=<?=($rentang+2)?>>Total Piutang Global</td>
        <td><?=ubahToRp($totalPiutangGlobal)?></td>
      </tr>
    </tbody>
  </table>
<?php 
} ?>
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