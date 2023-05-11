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
  'laporan_point_sdm'
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
$tanggalPecahHariIni=explode('-', $tanggalHariIni);

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

$sqlLibur=$db->prepare('SELECT hariLibur 
  FROM balistars_produktivity 
  where (tanggalProduktivity BETWEEN ? AND ?) 
  and idCabang=? 
  and statusProduktivity=?');
$sqlLibur->execute([
  $tanggalAwal,$tanggalAkhir,
  $idCabang,
  'Aktif']);
$dataLibur=$sqlLibur->fetch();
$hariLibur=explode(',', $dataLibur['hariLibur']);

if($dataLibur){
  $banyakHariLibur=cekHariLibur($hariLibur,$tahun.'-'.$bulan.'-'.$tanggalPecah[2]);
}
else{
  $banyakHariLibur=0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/custom.css">
  <title>Print Point SDM</title>
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
      <th colspan="40" style="font-size: 17px; text-align: center;">Laporan Point SDM <?=$namaCabang?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
    <thead>
      <tr>
        <th>No</th>
        <th>Karyawan</th>
        <?php 
          $dayOff=0;
          for ($i=1; $i <= $tanggalPecah[2] ; $i++) { 
            $cek="";
            if($i<10){ 
              $d='0'.$i;
            }
            else{
              $d=$i;
            }
            $nameOfDay = date('l', strtotime($tahun.'-'.$bulan.'-'.$d));
            if($nameOfDay=="Sunday"){
            $dayOff=$dayOff+1;
            }
            for($j=0; $j<count($hariLibur); $j++){
              if($hariLibur[$j]==$tahun.'-'.$bulan.'-'.$d){
                $cek="bg-warning";
              }
            }
            ?>
            <th class="<?=$cek?>" style="color: white;">
              <?=$d?>
            </th>
            <?php
          }
        ?>
        <th>Jumlah</th>
        <th>Average</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $n=0;
      $sqlKaryawan=$db->prepare('SELECT * 
        FROM balistars_pegawai 
        where tglNonAktif > ? 
        and idCabang=? 
        and idJabatan!=? and idJabatan!=? and idJabatan!=? and idJabatan!=? 
        and statusPegawai=?');
      $sqlKaryawan->execute([
        $tanggalAkhir, 
        $idCabang,
        1,3,9,11,
        'Aktif']);
      $dataKaryawan=$sqlKaryawan->fetchAll();

      $totalAverage=0;
      foreach ($dataKaryawan as $row) {
        $n++;
        ?>
        <tr>
          <td><?=$row['idPegawai']?></td>
          <td><?=$row['namaPegawai']?></td>
          <?php 
          $totalPoin=$dayOff*10;
            for ($i=1; $i <= $tanggalPecah[2] ; $i++) { 
              if($i<10){
              $d='0'.$i;
              }
              else{
                $d=$i;
              }
              $poin=0;
              if($tanggalAkhir>$tanggalPecahHariIni[0].'-'.$tanggalPecahHariIni[1].'-31'){
                $cek="";
              }
              else{
                $sqlPoin=$db->prepare('SELECT * 
                  FROM balistars_absensi 
                  where idPegawai=? 
                  and tanggalDatang=?');
                $sqlPoin->execute([
                  $row['idPegawai'],
                  $tahun.'-'.$bulan.'-'.$d]);
                $dataPoin=$sqlPoin->fetch();
                
                if($dataPoin){
                  $poin=$dataPoin['poin'];
                  $cek="";
                }
                else{
                  $poin=-10;
                  $cek="bg-danger";
                }
                for($j=0; $j<count($hariLibur); $j++){
                  if($hariLibur[$j]==$tahun.'-'.$bulan.'-'.$d){
                    $cek="bg-warning";
                    $poin=0;
                  }
                }
              }
              ?>
              <td class="<?=$cek?>">
              <?php 
                echo $poin;
              ?>
              </td>
              <?php
              $totalPoin=$totalPoin+$poin; 
            }
          $average=$totalPoin/($tanggalPecah[2]-$banyakHariLibur-$dayOff);
          $totalAverage=$totalAverage+$average;
          ?>
          <td><?=$totalPoin?></td>
          <td><?=round($average,1)?></td>
        </tr>
        <?php
      }
      ?>
    </tbody>
  </table>
  <?php
    $fixAverage=$totalAverage/$n;
    $nilaiSDM=round($fixAverage*20);
  ?>
  <table style="padding-top: 200px;" class="table">
    <tr>
      <td>Jumlah Karyawan</td>
      <td><?=$n?></td>
    </tr>
    <tr>
      <td>Jumlah Hari</td>
      <td><?=($tanggalPecah[2]-$banyakHariLibur)?> Hari</td>
    </tr>
    <tr>
      <td>Off Resmi</td>
      <td><?=$dayOff?> Hari</td>
    </tr>
    <tr>
      <td>Total Average</td>
      <td><?=round($totalAverage)?></td>
    </tr>
    <tr>
      <td>Fix Average</td>
      <td><?=round($fixAverage)?></td>
    </tr>
    <tr>
      <td>Nilai SDM</td>
      <td><?=$nilaiSDM?></td>
    </tr>
    <tr>
      <td>Kelas Cabang</td>
      <td>
        <?php 
        if($totalAverage/$n>8.9){
          echo "A";
        }
        else if($totalAverage/$n>7){
          echo "B";
        }
        else if($totalAverage/$n>5){
          echo "C";
        }
        else if($totalAverage/$n==0){
          echo "Belum Dapat Ditentukan";
        }
        else{
          echo "BURUK";
        }
        ?>
      </td>
    </tr>
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