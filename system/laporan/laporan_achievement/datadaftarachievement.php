<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once 'fungsiachievement.php';

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
  'laporan_achievement'
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
$selisihTanggal=selisihTanggal($tanggalAwal,$tanggalAkhir);


$totalAchievement=0;
$totalPenjualan=0;
$totalKunjungan=0;

$tanggalPecah1=explode('-', $tanggalAwal);
$tanggalPecah2=explode('-', $tanggalAkhir);
$selisihBulan=($tanggalPecah2[1]+(12*($tanggalPecah2[0]-$tanggalPecah1[0])))-$tanggalPecah1[1]+1;
$bulan=$tanggalPecah1[1];
$tahun=$tanggalPecah1[0];
for ($i=0; $i < $selisihBulan ; $i++) { 
  $bulan=$bulan+$i;
  if($bulan>12){
    $bulan=1;
    $tahun++;
  }
}

$waktuSekarang=date('H:i:s');
  
$waktuKuningAwal='07:00:00';
$waktuKuningAkhir='12:00:00'; 
$waktuHijauAkhir='15:00:00';
$waktuBiruAkhir='21:00:00'; 

$sqlBintangMerah=$db->prepare('SELECT idCabang from balistars_penjualan 
  where tanggalPenjualan=? 
  and statusPenjualan=? 
  order by timeStamp ASC limit 1');
$sqlBintangMerah->execute([$tanggalAwal,'Aktif']);
$dataBintangMerah=$sqlBintangMerah->fetch();

$dataBintangKuning=bintang($db,$tanggalAwal,$waktuKuningAwal,$waktuKuningAkhir);
$dataBintangHijau=bintang($db,$tanggalAwal,$waktuKuningAwal,$waktuHijauAkhir);
$dataBintangBiru=bintang($db,$tanggalAwal,$waktuKuningAwal,$waktuBiruAkhir);
 
$n=1;
$sqlPenjualan=$db->prepare('SELECT SUM(grandTotal-nilaiPPN) as acvNonPPN, SUM(grandTotal) as acv, balistars_penjualan.idCabang, namaCabang, balistars_cabang.idCabang 
  FROM balistars_penjualan 
  inner join balistars_cabang 
  on balistars_penjualan.idCabang=balistars_cabang.idCabang 
  where (balistars_penjualan.tanggalPenjualan between ? and ?) 
  and statusPenjualan=?
  group by balistars_penjualan.idCabang 
  order by acvNonPPN DESC');
$sqlPenjualan->execute([
  $tanggalAwal,$tanggalAkhir,
  'Aktif']);
$dataPenjualan=$sqlPenjualan->fetchAll();

$fontWeight='bold';
$arrayAcvNon = array();
$arrayAcv = array();
$arrayCabang = array();

foreach ($dataPenjualan as $row) {
  $arrayAcvNon[]  =$row['acvNonPPN']
                    +fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$row['idCabang'],'Penjualan');
  $arrayAcv[]=$row['acv'];
  $arrayCabang[]=$row['idCabang'];
}
array_multisort($arrayAcvNon, $arrayAcv, $arrayCabang);

for ($i=count($arrayAcvNon)-1; $i >= 0  ; $i--) { 
    if($n>1){
        $fontWeight='normal';
    }
    $sqlKunjungan=$db->prepare('SELECT count(noNota) as totalKunjungan, balistars_cabang.namaCabang 
      FROM balistars_penjualan 
      inner join balistars_cabang 
      on balistars_penjualan.idCabang=balistars_cabang.idCabang 
      where (balistars_penjualan.tanggalPenjualan between ? and ?) 
      and balistars_penjualan.idCabang=? 
      and balistars_penjualan.grandTotal>? 
      and statusPenjualan=?');
    $sqlKunjungan->execute([
      $tanggalAwal,$tanggalAkhir,
      $arrayCabang[$i],
      0,
      'Aktif']);
    $dataKunjungan=$sqlKunjungan->fetch();
 ?>
 <tr>
  <td><?=$n?></td>
  <td style="font-weight:<?=$fontWeight?>"><?=$dataKunjungan['namaCabang']?></td>
  <td style="font-weight:<?=$fontWeight?>"><?=ubahToRp($arrayAcvNon[$i])?></td>
  <td style="font-weight:<?=$fontWeight?>"><?=ubahToRp($arrayAcv[$i])?></td>
  <td style="font-weight:<?=$fontWeight?>"><?=ubahToRp($dataKunjungan['totalKunjungan'])?></td>
  <td>
    <?php

    //echo $waktuSekarang;
    if($arrayCabang[$i]==$dataBintangMerah['idCabang']){
      ?>
      <i class="fa fa-star fa-lg text-danger"></i>
      <?php
    }

    if($arrayCabang[$i]==$dataBintangKuning['idCabang'] and $waktuSekarang>='12:00:00'){
      ?>
      <i class="fa fa-star fa-lg text-warning"></i>
      <?php
    }

    if($arrayCabang[$i]==$dataBintangHijau['idCabang'] and $waktuSekarang>='15:00:00'){
      ?>
      <i class="fa fa-star fa-lg text-success"></i>
      <?php
    }

    if($arrayCabang[$i]==$dataBintangBiru['idCabang'] and $waktuSekarang>='21:00:00'){
      ?>
      <i class="fa fa-star fa-lg text-primary"></i>
      <?php
    }
    ?>
    
  </td>
</tr>
<?php
$n++;
$totalPenjualan=$totalPenjualan+$arrayAcvNon[$i];
$totalAchievement=$totalAchievement+$arrayAcv[$i];
$totalKunjungan=$totalKunjungan+$dataKunjungan['totalKunjungan'];
}
?>
<tr>
  <td></td>
  <td><b>Total</b></td>
  <td><b>
    <?=ubahToRp($totalPenjualan)?></b>
  </td>
  <td><b>
    <?=ubahToRp($totalAchievement)?></b>
  </td>
  <td><b>
    <?=ubahToRp($totalKunjungan)?></b>
  </td>
  <td>
  </td>
</tr>