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
  'laporan_achievement_area'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalAwal=$tahun.'-'.$bulan.'-01';
$day=cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
$tanggalAkhir=$tahun.'-'.$bulan.'-'.$day;
$tanggalHariIni=date('Y-m-d');
$tanggalPecah=explode('-', $tanggalHariIni);

if($tanggalAkhir<=$tanggalHariIni || $tanggalAkhir>$tanggalPecah[0].'-'.$tanggalPecah[1].'-31' ){
  $tanggalPecah=explode('-', $tanggalAkhir);
}

$sqlAchievement=$db->prepare('SELECT * 
  FROM balistars_achievement_area 
  where area=? 
  and (tanggalAchievement between ? and ?) 
  and statusAchievement=?');
$sqlAchievement->execute([
  $area,
  $tanggalAwal,$tanggalAkhir,
  "Aktif"]);
$dataAchievement=$sqlAchievement->fetch();

function fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$area,$jenisPenyesuaian) 
{
  $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
    FROM balistars_penyesuaian 
    inner join balistars_cabang 
    on balistars_penyesuaian.idCabang=balistars_cabang.idCabang 
    where jenisPenyesuaian=? 
    and (tanggalPenyesuaian between ? and ?) 
    and status=? 
    and balistars_cabang.area=? 
    and statusPenyesuaian=?');
  $sqlPenyesuaian1->execute([
    $jenisPenyesuaian,
    $tanggalAwal,$tanggalAkhir,
    "Naik",
    $area,
    'Aktif']);

  $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
    FROM balistars_penyesuaian 
    inner join balistars_cabang 
    on balistars_penyesuaian.idCabang=balistars_cabang.idCabang  
    where jenisPenyesuaian=? 
    and (tanggalPenyesuaian between ? and ?) 
    and status=? 
    and balistars_cabang.area=? 
    and statusPenyesuaian=?');
  $sqlPenyesuaian2->execute([
    $jenisPenyesuaian,
    $tanggalAwal,$tanggalAkhir,
    "Turun",
    $area,
    "Aktif"]);

  $dataPenyesuaian1=$sqlPenyesuaian1->fetch();
  $dataPenyesuaian2=$sqlPenyesuaian2->fetch();
  return $dataPenyesuaian1['totalUP']-$dataPenyesuaian2['totalDown'];
}

?>
<div style="padding-bottom: 20px">
  <b>Target Achievement Area : <?=ubahToRp($dataAchievement['jumlahAchievement'])?></b><br>
  <b>Hari Efektif : <?=ubahToRp($dataAchievement['achievementHariEfektif'])?></b><br>
  <b>Sabtu & Minggu : <?=ubahToRp($dataAchievement['achievementHariWeekend'])?></b>
</div>
<table class="table table-bordered table-custom">
  <thead class="bg-info text-white">
    <th style="width: 5%">
      No
    </th>
    <th>
      <button class="btn btn-sm btn-info" onclick="dataDaftarAchievementArea()">
        Tanggal
      </button>
    </th>
    <th>
      <button class="btn btn-sm btn-info" onclick="dataDaftarAchievementArea()">
        Target
      </button>
    </th>
    <th style="text-align: center;">
      <button class="btn btn-sm btn-info" onclick="dataDaftarAchievementArea()">
        Achievement
      </button>
    </th>
    <th>
      <button class="btn btn-sm btn-info" onclick="dataDaftarAchievementArea()">
        Rasio
      </button>
    </th>
  </thead>
  <tbody>
    <?php 
      $n=0;
      $tanggalAwalJalan=$tanggalAwal;

      for ($i=1; $i <= $tanggalPecah[2] ; $i++) { 
        $jumlahAchievement=0;
        $nameOfDay = date('l', strtotime($tanggalAwalJalan));

        if($nameOfDay=="Saturday" || $nameOfDay=="Sunday"){
          $jumlahAchievement=$dataAchievement['achievementHariWeekend'];
        }
        else{
          $jumlahAchievement=$dataAchievement['achievementHariEfektif'];
        }
        $n++;

        $sqlPenjualan=$db->prepare('SELECT sum(grandTotal-nilaiPPN) as achievement 
          FROM balistars_penjualan 
          inner join balistars_cabang 
          on balistars_penjualan.idCabang=balistars_cabang.idCabang 
          where balistars_cabang.area=? 
          and balistars_penjualan.tanggalPenjualan=? 
          and statusPenjualan=?');
        $sqlPenjualan->execute([
          $area,
          $tanggalAwalJalan,
          'Aktif']);
        $dataPenjualan=$sqlPenjualan->fetch();

        $acv=$dataPenjualan['achievement']
            +fungsiPenyesuaian($db,$tanggalAwalJalan,$tanggalAwalJalan,$area,'Penjualan');
      ?>
        <tr>
          <td><?=$n?></td>
          <td><?=tanggalTerbilang($tanggalAwalJalan)?></td>
          <td><?=ubahToRp($jumlahAchievement)?></td>
          <td>
            <?=ubahToRp($acv)?>
          </td>
          <td>
            <?php
            if($dataAchievement){
              $ratio=round($acv/$jumlahAchievement*100);
            }
            else{
              $ratio='NAN';
            }
            echo $ratio.'%';
            ?>
          </td>
        </tr>
      <?php
      $tanggalAwalJalan=waktuBesok($tanggalAwalJalan);
      }
      ?>
  </tbody>
</table>