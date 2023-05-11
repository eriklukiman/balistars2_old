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
  'ranking_absensi'
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

$tanggalAkhir=$tanggal;
$tanggalAkhir=konversiTanggal($tanggalAkhir);
$tanggalPecah=explode('-', $tanggalAkhir);
$tanggalAwal=$tanggalPecah[0].'-'.$tanggalPecah[1].'-01';
$hariAkhir=(int)$tanggalPecah[2];

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/custom.css">
  <title>laporan Ranking Absensi</title>
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/toastr/toastr.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/select2/select2.css"> 
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/datepicker/datepicker.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/main2.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/color_skins.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/loader.css">
</head>
<body onload="doPrint()">
<div class="container">
 <table class="table table-bordered table-hover">
    <thead>
      <th colspan="6" style="font-size: 17px; text-align: center;">Laporan Ranking Absensi <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
     <thead>
        <th>Rank</th>
        <th>Branch</th>
        <th>Nama</th>
        <th>Poin</th>
        <th>Predikat</th>
        <th>Notifikasi</th>
    </thead>
    <tbody>
      <?php
      $banyakHariLibur = array(0,0,0,0,0,0,0,0,0,0,0,0);
      $n=0;
      $sqlLibur=$db->prepare('
        SELECT hariLibur, idCabang 
        FROM balistars_produktivity 
        where (tanggalProduktivity BETWEEN ? AND ?)
        and statusProduktivity=?');
      $sqlLibur->execute([
        $tanggalAwal,
        $tanggalAkhir,
        'Aktif']);
      $dataLibur=$sqlLibur->fetchAll();
      foreach ($dataLibur as $cek) {
        $hariLibur=explode(',', $cek['hariLibur']); 
        if($dataLibur){
          $banyakHariLibur[$cek['idCabang']]=cekHariLibur($hariLibur,$tanggalAkhir);
        }
        else{
          $banyakHariLibur[$cek['idCabang']]=0;
        }
      }
      $dayOff=0;
      $tanggalJalan=$tanggalAwal;
      for ($i=0; $i < $hariAkhir ; $i++) { 
        $nameOfDay = date('l', strtotime($tanggalJalan));
        if($nameOfDay=="Sunday"){
          $dayOff++;
        }
        $tanggalJalan=waktuBesok($tanggalJalan);
      }
      $set = $hariAkhir-$dayOff;
      $sql=$db->prepare('SELECT (data.totalPoin-((?-data.totalAbsen)*10)+(?*10)) as poinKotor, data.idPegawai, data.namaPegawai, data.idCabang, data.namaCabang FROM 
        (SELECT SUM(poin) as totalPoin, balistars_absensi.idPegawai, count(idAbsensi) as totalAbsen, namaPegawai, balistars_pegawai.idCabang, balistars_cabang.namaCabang FROM balistars_pegawai 
        inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang 
        inner join balistars_absensi on balistars_absensi.idPegawai=balistars_pegawai.idPegawai 
        where idJabatan!=? and idJabatan!=? and idJabatan!=? and idJabatan!=? and (balistars_absensi.tanggalDatang BETWEEN ? and ?) group by idPegawai) as data 
        order by poinKotor DESC, namaPegawai ASC');
      $sql->execute([$hariAkhir,$dayOff,1,3,9,11,$tanggalAwal,$tanggalAkhir]);
      $data=$sql->fetchAll();
      foreach ($data as $row) {
        $pembagi = $set-$banyakHariLibur[$row['idCabang']];
        $poinBersih = round($row['poinKotor']/$pembagi);
        //echo ' ini '.$row['poinKotor'].'/'.$pembagi.' '.$poinBersih.' '.$row['namaPegawai'].' '.$banyakHariLibur[$row['idCabang']].'<br>';
        $n++;
        if($poinBersih>=9){
          $predikat='A';
          $color='success'; 
        }
        else if($poinBersih>=8){
          $predikat='B';
          $color='info';
        }
        else if($poinBersih>=6){
          $predikat='C';
          $color='warning';
        }
        else {
          $predikat='D';
          $color='danger';
        }
        ?>
      <tr>
        <td><?=$n?></td>
        <td><?=$row['namaCabang']?> </td>
        <td><?=$row['namaPegawai']?></td>
        <td><?=$poinBersih?></td>
        <td>Kelas <?=$predikat?></td>
        <td style="color: red;">
          <?php  
          if($predikat=='D'){
            echo "Warning";
          }
          ?>
        </td>
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
 <!--  <script>
    function doPrint(url) {
      window.print();
      window.onafterprint=function(event){
         window.location.href=url;
      };            
    }
  </script> -->
</body>
</html>