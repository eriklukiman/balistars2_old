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
  'laporan_kunjungan'
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

$selisihTanggal=selisihTanggal($tanggalAwal,$tanggalAkhir)+1;
$namaCabang=array();
$idCabang=array();
$minimal=array();
$maximal=array();
$average=array();
?>

<table class="table table-bordered table-custom">
  <thead class="bg-info text-white">
    <th>Tanggal</th>
    <?php
    $dataCabang=$db->prepare('SELECT * 
      FROM balistars_cabang 
      where statusCabang=? 
      and namaCabang not like ? 
      order by idCabang');
    $dataCabang->execute([
      'Aktif',
      '%head office%']);
    $hasil=$dataCabang->fetchAll();
    $i=0;
    foreach($hasil as $row){
      $namaCabang[$i]=$row['namaCabang'];
      $idCabang[$i]=$row['idCabang'];
      $minimal[$i]=999999;
      $maximal[$i]=0;
      $average[$i]=0;
      ?>
      <th><?=$row['namaCabang']?></th>
      <?php
      $i++;
    }
    ?>
  </thead> 
  <tbody>
    <?php
    for($i=0; $i<$selisihTanggal; $i++){
      ?>
      <tr>
        <td><?=ubahTanggalIndo($tanggalAwal)?></td>
        <?php
        for($j=0; $j<count($idCabang); $j++){
          $sqlKunjungan=$db->prepare('SELECT SQL_CACHE COUNT(idPenjualan) as jumlahKunjungan 
            from balistars_penjualan 
            where idCabang=? 
            and tanggalPenjualan=? 
            and grandTotal>? 
            and statusPenjualan=?');
          $sqlKunjungan->execute([
            $idCabang[$j],
            $tanggalAwal,
            0,
            'Aktif']);
          $dataKunjungan=$sqlKunjungan->fetch();
        ?>
        <td style="text-align: center;"><?=$dataKunjungan['jumlahKunjungan']?></td>
          <?php 
          if($dataKunjungan['jumlahKunjungan']>$maximal[$j]){
            $maximal[$j]=$dataKunjungan['jumlahKunjungan'];
          }

          if($dataKunjungan['jumlahKunjungan']<=$minimal[$j] && $dataKunjungan['jumlahKunjungan']>0){
            $minimal[$j]=$dataKunjungan['jumlahKunjungan'];
          }

          $average[$j]+=$dataKunjungan['jumlahKunjungan'];
        }
        ?>
      </tr>
      <?php
      $tanggalAwal=waktuBesok($tanggalAwal);
    }
    ?>
    <tr>
      <td style="font-weight: bold;">Average</td>
      <?php
      for($j=0; $j<count($idCabang); $j++){
        ?>
        <td style="font-weight: bold; text-align: center;">
          <?php
          if($selisihTanggal>0){
            echo number_format($average[$j]/$selisihTanggal,0);
          }
          else{
            echo $average[$j];
          }
          ?>
        </td>
        <?php
      }
      ?>
    </tr>
    <tr>
      <td style="font-weight: bold;">Maksimal</td>
      <?php
      for($j=0; $j<count($idCabang); $j++){
        ?>
        <td style="font-weight: bold; text-align: center;"><?=$maximal[$j]?></td>
        <?php
      }
      ?>
    </tr>
    <tr>
      <td style="font-weight: bold;">Minimal</td>
      <?php
      for($j=0; $j<count($idCabang); $j++){
        if($minimal[$j]>=999999){
          $print="NAN";
        }
        else{
          $print=$minimal[$j];
        }
        ?>
        <td style="font-weight: bold; text-align: center;"><?=$print?></td>
        <?php
      }
      ?>
    </tr>
  </tbody>
</table>
 