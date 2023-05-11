<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';

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
  'absensi_datang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}
date_default_timezone_set("Asia/Kuala_Lumpur");
$siftKerja = '';
extract($_REQUEST);

$tahun=date('Y');
$bulan=date('m');
$today=date('Y-m-d');
$timeToday=date('H:i:s');

$tanggalAwal=$tahun.'-'.$bulan.'-01';
$day=cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
$tanggalAkhir=$tahun.'-'.$bulan.'-'.$day;


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

if (in_array($today, $hariLibur)) {
  $jenisPoin = 'Hari Libur';
}else{
  $jenisPoin = 'Hari Kerja';
}


$nameOfDay = date('l', strtotime($today));
$sqlCek=$db->prepare('SELECT jamDatang FROM balistars_absensi where idPegawai=? and tanggalDatang=?');
$sqlCek->execute([$idPegawaiAbsen,$today]);
$dataCek=$sqlCek->fetch();

$sqlPoin=$db->prepare('SELECT * FROM balistars_sift where idCabang=? and tanggalBerlaku<=? order by tanggalBerlaku DESC limit 1');
$sqlPoin->execute([$idCabang,$today]);
$dataPoin=$sqlPoin->fetch();
if($dataCek){
  $data = array('flag' => $flag, 'notifikasi' => 2, 'parameterOrder' => $parameterOrder);
}
else if($siftKerja==''){
  $data = array('flag' => $flag, 'notifikasi' => 3, 'parameterOrder' => $parameterOrder);
}
else{
  if($siftKerja=="normal"){
    if($nameOfDay=="Saturday" || $nameOfDay=="Sunday"){
      if($timeToday<=$dataPoin['siftNormalWeekend']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
    else{
      if($timeToday<=$dataPoin['siftNormalNormal']){
        $poin=10;
      }
      else{ 
        $poin=0;
      }
    }
  }
  else if($siftKerja=="pagi"){
    if($nameOfDay=="Saturday" || $nameOfDay=="Sunday"){
      if($timeToday<=$dataPoin['siftPagiWeekend']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
    else{
      if($timeToday<=$dataPoin['siftPagiNormal']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
  }
  else if($siftKerja=="middle"){
    if($nameOfDay=="Saturday" || $nameOfDay=="Sunday"){
      if($timeToday<=$dataPoin['siftMiddleWeekend']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
    else{
      if($timeToday<=$dataPoin['siftMiddleNormal']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
  }
  else if($siftKerja=="middle2"){
    if($nameOfDay=="Saturday" || $nameOfDay=="Sunday"){
      if($timeToday<=$dataPoin['siftMiddle2Weekend']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
    else{
      if($timeToday<=$dataPoin['siftMiddle2Normal']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
  }
  else if($siftKerja=="middle3"){
    if($nameOfDay=="Saturday" || $nameOfDay=="Sunday"){
      if($timeToday<=$dataPoin['siftMiddle3Weekend']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
    else{
      if($timeToday<=$dataPoin['siftMiddle3Normal']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
  }
  else if($siftKerja=="siang"){
    if($nameOfDay=="Saturday" || $nameOfDay=="Sunday"){
      if($timeToday<=$dataPoin['siftSiangWeekend']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
    else{
      if($timeToday<=$dataPoin['siftSiangNormal']){
        $poin=10;
      }
      else{
        $poin=0;
      }
    }
  }
  $sql=$db->prepare('INSERT INTO balistars_absensi set 
    tanggalDatang=?,
    idPegawai=?,
    siftMasuk=?,
    jamDatang=?,
    poin=?,
    jenisPoin=?,
    idCabang=?,
    idUser=?');
  $hasil=$sql->execute([
    $today,
    $idPegawaiAbsen,
    $siftKerja,
    $timeToday,
    $poin,
    $jenisPoin,
    $idCabang,
    $idUserAsli]);

$data = array('flag' => $flag, 'notifikasi' => 4, 'parameterOrder' => $parameterOrder);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1, 'parameterOrder' => $parameterOrder);
}
}

echo json_encode($data);

?>