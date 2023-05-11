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
  'laporan_sdm_Branch'
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

$dayOff=0;
for ($i=1; $i <= $tanggalPecah[2] ; $i++) { 
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
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/custom.css">
  <title>Print SDM Branch</title>
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
      <th colspan="40" style="font-size: 17px; text-align: center;">Laporan SDM Branch <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
    <thead>
      <tr>
        <th>No</th>
        <th class="align-middle">Cabang</th>
        <th>Branch</th>
        <th>SDM</th>
        <th>Total Nilai</th>
        <th>Area</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $namaCabang = array();
    $branch = array();
    $SDM  = array();
    $totalNilai = array();
    $area = array();

    $sqlCabang=$db->prepare('SELECT * 
      FROM balistars_cabang 
      where statusCabang=?');
    $sqlCabang->execute(['Aktif']);
    $dataCabang=$sqlCabang->fetchAll();

    foreach ($dataCabang as $row) {
      $totalPoinBranch=0;
      $totalPoinSDM=0;
      $totalAverageSDM=0;

      $sqlLibur=$db->prepare('SELECT hariLibur , jumlahPegawai
        FROM balistars_produktivity 
        where (tanggalProduktivity BETWEEN ? AND ?) 
        and idCabang=? 
        and statusProduktivity=?');
      $sqlLibur->execute([
        $tanggalAwal,$tanggalAkhir,
        $row['idCabang'],
        "Aktif"]);
      $dataLibur=$sqlLibur->fetch();

      $hariLibur=explode(',', $dataLibur['hariLibur']);
      if($dataLibur){
        $banyakHariLibur=cekHariLibur($hariLibur,$tahun.'-'.$bulan.'-'.$tanggalPecah[2]);
      }
      else{
        $banyakHariLibur=0;
      }

      for ($i=1; $i <= $tanggalPecah[2] ; $i++) { 
        if($i<10){
          $d='0'.$i;
        }
        else{
          $d=$i;
        }

        $sqlPoin=$db->prepare('SELECT poin 
          FROM balistars_absensi 
          where idCabang=? 
          and tanggalDatang=? 
          order by jamDatang ASC limit 1');
        $sqlPoin->execute([
          $row['idCabang'],
          $tahun.'-'.$bulan.'-'.$d]);
        $dataPoin=$sqlPoin->fetch();

        $totalPoinBranch=$totalPoinBranch+$dataPoin['poin'];
      }

      $sqlPoin=$db->prepare('SELECT sum(poin) as totalPoinAwal 
        FROM balistars_absensi 
        inner join balistars_pegawai 
        on balistars_absensi.idPegawai=balistars_pegawai.idPegawai
        where balistars_pegawai.idCabang=? 
        and idJabatan!=? and idJabatan!=? and idJabatan!=? and idJabatan!=?
        and statusPegawai=? 
        and tglNonAktif>? 
        and jenisPoin=?
        and (tanggalDatang between ? and ?) 
      ') ;
      $sqlPoin->execute([
        $row['idCabang'],
        1,3,9,11,
        'Aktif',
        $tanggalAkhir,
        'Hari Kerja',
        $tahun.'-'.$bulan.'-01', $tahun.'-'.$bulan.'-'.$tanggalPecah[2]]);
      $dataPoin=$sqlPoin->fetch();

      $sqlPoin1=$db->prepare('SELECT sum(poin) as totalPoinLibur 
        FROM balistars_absensi 
        inner join balistars_pegawai 
        on balistars_absensi.idPegawai=balistars_pegawai.idPegawai
        where balistars_pegawai.idCabang=? 
        and idJabatan!=? and idJabatan!=? and idJabatan!=? and idJabatan!=?
        and statusPegawai=? 
        and tglNonAktif>? 
        and jenisPoin=?
        and (tanggalDatang between ? and ?) 
      ') ;
      $sqlPoin1->execute([
        $row['idCabang'],
        1,3,9,11,
        'Aktif',
        $tanggalAkhir,
        'Hari Libur',
        $tahun.'-'.$bulan.'-01', $tahun.'-'.$bulan.'-'.$tanggalPecah[2]]);
      $dataPoin1=$sqlPoin1->fetch();

      $sqlPoin2=$db->prepare('SELECT balistars_pegawai.idPegawai 
        FROM balistars_absensi 
        inner join balistars_pegawai 
        on balistars_absensi.idPegawai=balistars_pegawai.idPegawai
        where balistars_pegawai.idCabang=? 
        and idJabatan!=? and idJabatan!=? and idJabatan!=? and idJabatan!=?
        and statusPegawai=? 
        and tglNonAktif>? 
        and jenisPoin=?
        and (tanggalDatang between ? and ?)');
      $sqlPoin2->execute([
        $row['idCabang'],
        1,3,9,11,
        'Aktif',
        $tanggalAkhir,
        'Hari Kerja',
        $tahun.'-'.$bulan.'-01', $tahun.'-'.$bulan.'-'.$tanggalPecah[2]]);
      $dataPoin2=$sqlPoin2->fetchAll();

      $sqlKaryawan=$db->prepare('SELECT idPegawai 
        FROM balistars_pegawai 
        where tglNonAktif > ? 
        and idCabang=? 
        and idJabatan!=? and idJabatan!=? and idJabatan!=? and idJabatan!=? 
        and statusPegawai=?');
      $sqlKaryawan->execute([
        $tanggalAkhir, 
        $row['idCabang'],
        1,3,9,11,
        'Aktif']);
      $dataKaryawan=$sqlKaryawan->fetchAll();

      $totalPoinAwal=intval($dataPoin['totalPoinAwal']);
      $totalPoinLibur=intval($dataPoin1['totalPoinLibur']);
      $jumlahPegawai=count($dataKaryawan);
      $banyakHadir=count($dataPoin2);
     
      $totalPoinSDM=$totalPoinAwal
                    +(($banyakHadir-($jumlahPegawai*($tanggalPecah[2]-$banyakHariLibur)))*10)
                    +($dayOff*10*$jumlahPegawai)
                    + $totalPoinLibur;
      $totalAverageSDM=$totalPoinSDM/($tanggalPecah[2]-$banyakHariLibur-$dayOff);

      $averageFixSDM=$totalAverageSDM/$jumlahPegawai;
      $averageBranch=$totalPoinBranch/($tanggalPecah[2]-$banyakHariLibur);
      $nilaiBranch=$averageBranch*20; 
      $nilaiSDM=$averageFixSDM*20;

      $namaCabang[]=$row['namaCabang'];
      $SDM[]=round($nilaiSDM);
      $branch[]=round($nilaiBranch);
      $totalNilai[]=round($nilaiBranch)+round($nilaiSDM);
      $area[]=$row['area'];

    }
    $n=0;
        array_multisort($totalNilai, $SDM, $branch, $namaCabang, $area);
        for ($i=count($totalNilai)-1; $i>=0 ; $i--) {
          $n++; 
          ?>
          <tr>
            <td><?=$n?></td>
            <td><?=$namaCabang[$i]?></td>
            <td><?=$branch[$i]?></td>
            <td><?=$SDM[$i]?></td>
            <td><?=$totalNilai[$i]?></td>
            <td><?=$area[$i]?></td>
          </tr>
          <?php
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