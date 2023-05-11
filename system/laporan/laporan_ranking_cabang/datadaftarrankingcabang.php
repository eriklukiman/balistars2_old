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
  'laporan_ranking_cabang'
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

$sql=$db->prepare('SELECT idCabang, sum(grandTotal-nilaiPPN) as achievement 
  FROM balistars_penjualan 
  where (tanggalPenjualan between ? and ?) 
  and statusPenjualan=? 
  group by idCabang');
$sql->execute([
  $tanggalAwal,
  $tanggalAkhir,
  'Aktif']);
$hasil=$sql->fetchAll();

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

function fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$idCabang,$jenisPenyesuaian)
  {
    if($idCabang==0){
      $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
        FROM balistars_penyesuaian 
        where jenisPenyesuaian=? 
        and (tanggalPenyesuaian between ? and ?) 
        and status=? 
        and statusPenyesuaian=?');
      $sqlPenyesuaian1->execute([
        $jenisPenyesuaian,
        $tanggalAwal,$tanggalAkhir,
        "Naik",
        "Aktif"]);

      $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
        FROM balistars_penyesuaian 
        where jenisPenyesuaian=? 
        and (tanggalPenyesuaian between ? and ?) 
        and status=? 
        and statusPenyesuaian=?');
      $sqlPenyesuaian2->execute([
        $jenisPenyesuaian,
        $tanggalAwal,$tanggalAkhir,
        "Turun",
        "Aktif"]);

    } 
    else{
      $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
        FROM balistars_penyesuaian 
        where jenisPenyesuaian=? 
        and (tanggalPenyesuaian between ? and ?) 
        and status=? 
        and idCabang=? 
        and statusPenyesuaian=?');
      $sqlPenyesuaian1->execute([
        $jenisPenyesuaian,
        $tanggalAwal,$tanggalAkhir,
        "Naik",
        $idCabang,
        "Aktif"]);

      $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
        FROM balistars_penyesuaian 
        where jenisPenyesuaian=? 
        and (tanggalPenyesuaian between ? and ?) 
        and status=? 
        and idCabang=? 
        and statusPenyesuaian=?');
      $sqlPenyesuaian2->execute([
        $jenisPenyesuaian,
        $tanggalAwal,$tanggalAkhir,
        "Turun",
        $idCabang,
        "Aktif"]);

    }

    $dataPenyesuaian1=$sqlPenyesuaian1->fetch();
    $dataPenyesuaian2=$sqlPenyesuaian2->fetch();
    return $dataPenyesuaian1['totalUP']-$dataPenyesuaian2['totalDown'];
  }

$totalPegawai=0;
$arrayCabang = array();
$arrayProduktivity = array();
$arrayachievement = array();

foreach ($hasil as $row) {
  $sqlProduktivity=$db->prepare('SELECT * FROM balistars_produktivity where idCabang=? and (tanggalProduktivity between ? and ?)');
  $sqlProduktivity->execute([$row['idCabang'],$tanggalAwal,$tanggalAkhir]);
  $dataProduktivity=$sqlProduktivity->fetch();

  $arrayCabang[]=$row['idCabang'];
  $arrayachievement[]=$row['achievement']+fungsiPenyesuaian($db,($tanggalAwal),$tanggalAkhir,$row['idCabang'],"Penjualan");
  if(!$dataProduktivity){
    $arrayProduktivity[]=0;
  }
  else{
    $arrayProduktivity[]=($row['achievement']+fungsiPenyesuaian($db,($tanggalAwal),$tanggalAkhir,$row['idCabang'],"Penjualan"))/$dataProduktivity['jumlahPegawai'];
  }
  $totalPegawai+=$dataProduktivity['jumlahPegawai'];
}

array_multisort($arrayProduktivity, $arrayachievement, $arrayCabang);

$n=1;
$totalAchievement=0;
$productivity=0;
$totalProductivity=0;
$idealProduktivity=0;
$banyakHariLibur=0;
$kelas='Utama';
for ($i=count($arrayProduktivity)-1; $i >= 0  ; $i--) { 
  if($n<=3){
    $kelas='Utama';
  }
  else if($n<=6){
    $kelas='Middle';
  }
  else {
    $kelas='Degradasi';
  }

  if(($tanggalAwal<$tanggalHariIni) && ($tanggalAkhir<=$tanggalHariIni)){
    $tanggalPecah=explode('-', $tanggalAkhir);
  }
  else{
    $tanggalPecah=explode('-', $tanggalHariIni);
  }

  $sqlProduktivity=$db->prepare('SELECT * FROM balistars_produktivity 
    where idCabang=? 
    and (tanggalProduktivity between ? and ?) 
    and statusProduktivity=?');
  $sqlProduktivity->execute(
    [$arrayCabang[$i],
    $tanggalAwal,$tanggalAkhir,
    'Aktif']);
  $dataProduktivity=$sqlProduktivity->fetch();

  if($dataProduktivity['hariLibur']){
    $hariLibur=explode(',', $dataProduktivity['hariLibur']);
    $banyakHariLibur=cekHariLibur($hariLibur,$tahun.'-'.$bulan.'-'.$tanggalPecah[2]);
  }
  else{
    $banyakHariLibur=0;
  }

  $idealProduktivity  = $tanggalPecah[2]-$banyakHariLibur;
  $hariEfektif        = $day-count($hariLibur);
  if($idealProduktivity<0){
    $idealProduktivity=0;
  }

  $idealProduktivity  =($idealProduktivity
                        *$dataProduktivity['nominalProduktif'])
                        /$hariEfektif;
  if($arrayProduktivity[$i]<$idealProduktivity){
    $style="font-weight: 700; color: red";
  }
  else{
    $style="color: green";
  }
?>
<tr>
  <td><?=$n?></td>
  <td>
    <?php 
    $dataCabang=executeQueryUpdateForm('SELECT namaCabang, area FROM balistars_cabang where idCabang=?',$db,$arrayCabang[$i]);
    echo $dataCabang['namaCabang'];
    ?>
  </td>
  <td><?=ubahToRp($arrayachievement[$i])?></td>
  <td style="<?=$style?>"><?=ubahToRp($arrayProduktivity[$i])?></td>
  <td><?=$dataCabang['area'];?></td>
  <td><?=$kelas?></td>
</tr>

<?php
  $n++;
  $totalAchievement+=($arrayachievement[$i]);
  $totalProductivity=$totalProductivity+$arrayProduktivity[$i];
  }
  $totalAchievement=$totalAchievement;
?>

<tr>
  <td colspan="2" style="text-align: center;"><strong>Bali Stars Group</strong></td>
  <td><strong><?=ubahToRp($totalAchievement)?></strong></td>
  <td><strong><?=ubahToRp($totalAchievement/$totalPegawai)?></strong></td>
  <td colspan="2"></td>
</tr>

<tr>
  <td colspan="2" style="text-align: center;"><b>Ideal Produktivity Cabang</b></td>
  <td colspan="4"><b><?=ubahToRp($idealProduktivity)?></b></td>
</tr>