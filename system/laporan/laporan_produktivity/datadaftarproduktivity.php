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
  'laporan_produktivity'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalAwal=$tahun.'-'.$bulan.'-01';
$tanggalAkhir=$tahun.'-'.$bulan.'-31';

$jenisPenjualan1=$jenisPenjualan;
$jenisPenjualan=strtolower($jenisPenjualan);

$sqlCabang=$db->prepare('SELECT * 
  FROM balistars_cabang 
  where statusCabang=? 
  and namaCabang not like ? 
  order by idCabang');
$sqlCabang->execute([
  'Aktif',
  '%head office%']);
$dataCabang=$sqlCabang->fetchAll();

if($jenisPenjualan1=='laser' || $jenisPenjualan1=='bw'){
  $parameter="klikBefore";
}
else{
  $parameter="luas";
}

$arrayCabang = array();
$arrayTarget = array();
$arrayAcv = array();
$arrayRatio = array();

foreach($dataCabang as $data){
  if($jenisPenjualan=='indoor' || $jenisPenjualan=='outdoor' || $jenisPenjualan=='uv'){
    $sqlDaily=$db->prepare('SELECT SUM(luas) as total 
      FROM balistars_performa_mesin_'.$jenisPenjualan.' 
      where (tanggalPerforma between ? and ?) 
      and idCabang=? 
      and statusPerforma'.$jenisPenjualan1.'=?');
    $sqlDaily->execute([
      $tanggalAwal,$tanggalAkhir,
      $data['idCabang'],
      'Aktif']);
    $dataDaily=$sqlDaily->fetch();
    
    $daily=$dataDaily['total'];

    $sqlTambahan=$db->prepare('SELECT SUM(luas) as totalLuas 
      FROM balistars_performa_mesin_input 
      where idCabang=? 
      and (tanggalPerforma between ? and ?) 
      and jenisOrder=? 
      and statusMesinInput=?');
    $sqlTambahan->execute([
      $data['idCabang'], 
      $tanggalAwal,$tanggalAkhir,
      $jenisPenjualan,
      'Aktif']);
    $dataTambahan=$sqlTambahan->fetch();
    $daily=$daily+$dataTambahan['totalLuas'];
  }
  elseif($jenisPenjualan=='laser'){

    $sqlDaily=$db->prepare('SELECT sum(jumlahKlik) as daily 
      FROM balistars_performa_mesin_laser 
      where (tanggalPerforma between ? and ?) 
      and idCabang=? 
      and statusKlik=? 
      order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
    $sqlDaily->execute([
      $tanggalAwal,$tanggalAkhir, 
      $data['idCabang'],
      'Aktif']);
    $dataDaily=$sqlDaily->fetch();
    $daily=$dataDaily['daily'];

    // $daily=0; 
    // $sqlDailyAfter=$db->prepare('SELECT klikBefore 
    //   FROM balistars_performa_mesin_laser 
    //   where tanggalPerforma<=? 
    //   and idCabang=? 
    //   and statusKlik=?
    //   order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
    // $sqlDailyAfter->execute([
    //   $tanggalAkhir, 
    //   $data['idCabang'],
    //   'Aktif']);
    // $dataDailyAfter=$sqlDailyAfter->fetch();

    // $sqlDailyBefore=$db->prepare('SELECT klikBefore 
    //   FROM balistars_performa_mesin_laser 
    //   where tanggalPerforma<? 
    //   and idCabang=? 
    //   and statusKlik=?
    //   order by tanggalPerforma DESC, idPerformalaser DESC limit 1');
    // $sqlDailyBefore->execute([
    //   $tanggalAwal, 
    //   $data['idCabang'],
    //   'Aktif']);
    // $dataDailyBefore=$sqlDailyBefore->fetch();

    // $daily=$dataDailyAfter['klikBefore']-$dataDailyBefore['klikBefore'];
  }
  else{
    $daily=0; 
    $sqlDailyAfter=$db->prepare('SELECT klikBefore FROM balistars_performa_mesin_'.$jenisPenjualan.' 
      where tanggalPerforma<=? 
      and idCabang=? 
      order by tanggalPerforma DESC, idPerforma'.$jenisPenjualan1.' DESC limit 1');
    $sqlDailyAfter->execute([
      $tanggalAkhir, 
      $data['idCabang']]);
    $dataDailyAfter=$sqlDailyAfter->fetch();

    $sqlDailyBefore=$db->prepare('SELECT klikBefore 
      FROM balistars_performa_mesin_'.$jenisPenjualan.' 
      where tanggalPerforma<? 
      and idCabang=? 
      order by tanggalPerforma DESC, idPerforma'.$jenisPenjualan1.' DESC limit 1');
    $sqlDailyBefore->execute([$tanggalAwal, $data['idCabang']]);
    $dataDailyBefore=$sqlDailyBefore->fetch();

    $daily=$dataDailyAfter['klikBefore']-$dataDailyBefore['klikBefore'];
  }
  $klik=$daily;

  $sqlTargetKlik=$db->prepare('SELECT target 
    FROM balistars_target 
    inner join balistars_jenis_penjualan 
    on balistars_target.idJenisPenjualan=balistars_jenis_penjualan.idJenisPenjualan 
    where idCabang=? 
    and balistars_jenis_penjualan.jenisPenjualan=? 
    and (tanggalAwal between ? and ?) 
    and (tanggalAkhir between ? and ?) 
    and statusTarget=?');
  $sqlTargetKlik->execute([
    $data['idCabang'],
    $jenisPenjualan1,
    $tanggalAwal,$tanggalAkhir,
    $tanggalAwal,$tanggalAkhir,
    'Aktif']);
  $dataTargetKlik=$sqlTargetKlik->fetch();

  $target=$dataTargetKlik['target'];

  if($target>0){
    $ratio=$klik/$target;
  }
  else{
    $target=0;
    $ratio=0; 
  }
  $arrayCabang[]=$data['namaCabang'];
  $arrayTarget[]=$target;
  $arrayRatio[]=$ratio;
  $arrayAcv[]=$klik;
}
array_multisort($arrayRatio, $arrayAcv, $arrayTarget, $arrayCabang);

$n=1;
$totalTarget=0;
$totalKlik=0;
$totalRatio=0;
for($i=count($arrayCabang)-1; $i>=0; $i--){
?>

<tr>
  <td><?=$n?></td>
  <td><?=$arrayCabang[$i]?></td>
  <td><?=$arrayTarget[$i]?></td>
  <td><?=$arrayAcv[$i]?></td>
  <td><?=number_format($arrayRatio[$i]*100,2)?> %</td>
</tr>
<?php
$n++;
$totalTarget+=$arrayTarget[$i];
$totalKlik+=$arrayAcv[$i];
}
if($totalTarget>0){
$totalRatio=$totalKlik/$totalTarget;
}
?>
<tr>
  <td colspan="2" style="text-align: center;"><strong>Bali Stars Group</strong></td>
  <td><strong><?=$totalTarget?></strong></td>
  <td><strong><?=$totalKlik?></strong></td>
  <td><strong><?=number_format($totalRatio*100,2)?> %</strong></td>
</tr>
