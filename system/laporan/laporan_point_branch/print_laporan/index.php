<?php
include_once '../../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';

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
  'laporan_point_Branch'
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

$tanggalAwal=$tahun.'-'.$bulan.'-01';
$day=cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
$tanggalAkhir=$tahun.'-'.$bulan.'-'.$day;
$tanggalHariIni=date('Y-m-d');
$tanggalPecah=explode('-', $tanggalHariIni);

if($tanggalAkhir<=$tanggalHariIni || $tanggalAkhir>$tanggalPecah[0].'-'.$tanggalPecah[1].'-31' ){
  $tanggalPecah=explode('-', $tanggalAkhir);
}

function cekHariLibur($hariLibur,$tanggalAkhir)
{
  $cek=0;
  for ($i=0; $i<count($hariLibur) ; $i++) { 
    if($hariLibur[$i]<=$tanggalAkhir && $hariLibur[$i]!=''){
      $cek++;
    }
  }
  return $cek;
}

$sqlBanyakCabang=$db->prepare('SELECT count(idCabang) as banyak 
  FROM balistars_cabang 
  where statusCabang=?');
$sqlBanyakCabang->execute(['Aktif']);
$dataBanyakCabang=$sqlBanyakCabang->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/custom.css">
  <title>Print Point Branch</title>
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/toastr/toastr.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/select2/select2.css"> 
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/datepicker/datepicker.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/main2.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/color_skins.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/loader.css">
  
  <?php
  icon($logo);
  ?>
</head>
<body onload="doPrint()">
<div class="container">
 <table class="table table-bordered table-hover">
    <thead>
      <th colspan="40" style="font-size: 17px; text-align: center;">Laporan Point Branch <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
    <thead>
      <tr>
        <th rowspan="2">Tanggal</th>
        <?php 
          $sqlCabang=$db->prepare('SELECT * 
            FROM balistars_cabang 
            where statusCabang=?');
          $sqlCabang->execute(['Aktif']);
          $dataCabang=$sqlCabang->fetchAll();
          foreach ($dataCabang as $row) {
            ?>
            <th colspan="2"><?=$row['namaCabang']?></th>
            <?php
          }
        ?>
      </tr>
      <tr>
        <?php 
      for($i=0; $i<$dataBanyakCabang['banyak']; $i++){
        ?>
        <th>Jam</th>
        <th>Poin</th>
        <?php
      }
      ?>
      </tr>
    </thead>
    <tbody>
      <?php 
      $no=0;
      $totalAverage=0;
      $totalPoin =  new SplFixedArray($dataBanyakCabang['banyak']);
      for ($i=1; $i <= $tanggalPecah[2] ; $i++) { 
        if($i<10){
            $d='0'.$i;
          }
          else{
            $d=$i;
          }
        $no++;
        ?>
        <tr>
          <td><?=$d?></td>
          <?php 
            $sqlCabang=$db->prepare('SELECT * 
              FROM balistars_cabang 
              where statusCabang=?');
            $sqlCabang->execute(['Aktif']);
            $dataCabang=$sqlCabang->fetchAll();
            $k=0;
            foreach ($dataCabang as $row) {
              $sqlPoin=$db->prepare('SELECT * 
                FROM balistars_absensi 
                where idCabang=? 
                and tanggalDatang=? 
                order by jamDatang ASC limit 1');
              $sqlPoin->execute([
                $row['idCabang'],
                $tahun.'-'.$bulan.'-'.$d]);
              $dataPoin=$sqlPoin->fetch();
              ?>
              <td><?=$dataPoin['jamDatang']?></td>
              <td><?=$dataPoin['poin']?></td>
              <?php
              $totalPoin[$k]=$totalPoin[$k]+$dataPoin['poin'];
              $k++;
            }
          ?>
        </tr>
        <?php
      }
      ?>
    </tbody>
  </table>
  <table class="table table-bordered">
    <?php 
    $sqlCabang=$db->prepare('SELECT * 
      FROM balistars_cabang 
      where statusCabang=?');
    $sqlCabang->execute(['Aktif']);
    $dataCabang=$sqlCabang->fetchAll();
    $n=0;
    ?>
    <thead class="bg-info text-white">
      <tr>
        <th>No</th>
        <th>Cabang</th>
        <th>Total Poin</th>
        <th>Hari Efektif</th>
        <th>Average</th>
        <th>Branch</th>
        <th>Nilai</th>
        <th>Kelas Cabang</th>
      </tr>
    </thead>
    <?php
    foreach ($dataCabang as $row) {
      $n++;
      ?>
      <tr>
        <td><?=$n?></td>
        <td><?=$row['namaCabang']?></td>
        <td><?=$totalPoin[$n-1]?></td>
        <td>
          <?php 
          $sqlLibur=$db->prepare('SELECT hariLibur 
            FROM balistars_produktivity 
            where (tanggalProduktivity BETWEEN ? AND ?) 
            and idCabang=? 
            and statusProduktivity=?');
          $sqlLibur->execute([
            $tanggalAwal,$tanggalAkhir,
            $row['idCabang'],
            "Aktif"]);
          $dataLibur=$sqlLibur->fetch();
          if($dataLibur){
            $hariLibur=explode(',', $dataLibur['hariLibur']);
            $banyakHariLibur=cekHariLibur($hariLibur,$tahun.'-'.$bulan.'-'.$tanggalPecah[2]);
          }
          else{
            $banyakHariLibur=0;
          }
          echo $tanggalPecah[2]-$banyakHariLibur;
          ?>
        </td>
        <td>
          <?php 
          $average=$totalPoin[$n-1]/($tanggalPecah[2]-$banyakHariLibur);
          echo round($average);
          ?>
        </td>
        <td><?=round($average*20)?></td>
        <td>
          <?php
          $nilai=round($average*10); 
          echo $nilai;
          ?>
        </td>
        <td>
          <?php 
          if($nilai>70){
            echo "Kelas A";
          }
          else if($nilai>50){
            echo "Kelas B";
          }
          else if($nilai>30){
            echo "Kelas C";
          }
          else{
            echo "BURUK";
          }
          ?>
        </td>
      </tr>
      <?php
    }
    ?>
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