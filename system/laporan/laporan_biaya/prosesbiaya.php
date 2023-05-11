<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once 'fungsinomor.php';

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
  'laporan_biaya'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag='';
extract($_REQUEST);

if($flag=='cancel'){
    $sql=$db->prepare('UPDATE balistars_biaya_detail set statusCancel=? WHERE noNota=?');
    $hasil = $sql->execute(['cancel',$noNota]);
    if($hasil){
      $sql=$db->prepare('UPDATE balistars_biaya set statusBiaya=?, idUserEdit=? WHERE noNota=?');
      $hasil = $sql->execute(['Non Aktif',$idUserAsli,$noNota]);
    }
}
else{
  if($jenisPPN=='Non PPN'){
    $persenPPN=0;
  }
  $tanggalBiaya=konversiTanggal($tanggalBiaya);
  $ppn=ubahToInt($ppn);
  $grandTotal=ubahToInt($grandTotal);
  if($flag=='update'){
    $sql=$db->prepare('UPDATE balistars_biaya set 
      noNota=?,
      tipeBiaya = ?,
      noNotaBiaya=?,
      tanggalBiaya=?,
      idCabang =?,
      idPegawai=?,
      jenisPPN=?,
      persenPPN=?,
      nilaiPPN=?,
      grandTotal=?,
      kodeAkunting=?,
      idUserEdit=?
      WHERE idBiaya=?');
    $hasil=$sql->execute([
      $noNota,
      $tipeBiaya,
      $noNotaBiaya,
      $tanggalBiaya,
      $idCabang,
      $idPegawai,
      $jenisPPN,
      $persenPPN,
      $ppn,
      $grandTotal,
      $kodeAkunting,
      $idUserAsli,
      $idBiaya]);
  }
  // else{
  //   $sql=$db->prepare('INSERT INTO balistars_biaya set 
  //     noNota=?,
  //     tipeBiaya = ?,
  //     noNotaBiaya=?,
  //     tanggalBiaya=?,
  //     idCabang =?,
  //     idPegawai=?,
  //     jenisPPN=?,
  //     persenPPN=?,
  //     nilaiPPN=?,
  //     grandTotal=?,
  //     kodeAkunting=?,
  //     idUser=?');
  //   $hasil=$sql->execute([
  //     $noNota,
  //     $tipeBiaya,
  //     $noNotaBiaya,
  //     $tanggalBiaya,
  //     $idCabang,
  //     $idPegawai,
  //     $jenisPPN,
  //     $persenPPN,
  //     $ppn,
  //     $grandTotal,
  //     $kodeAkunting,
  //     $idUserAsli]);

  //   if($hasil){
  //     if($tipeBiaya=='A1'){
  //       updateNoNotaA1($db);
  //     }
  //     elseif($tipeBiaya=='A2'){
  //       updateNoNotaA2($db);
  //     }
  //   } 
  // }
}


$data = array('notifikasi' => 2);

if($hasil){
  $data = array('notifikasi' => 1, 'flag' => $flag);
}
echo json_encode($data);

?>