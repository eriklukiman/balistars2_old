<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'form_po'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag=='cancel'){
    $sql=$db->prepare('UPDATE balistars_po_detail set 
      statusPoDetail=?, 
      idUserEdit=? 
      WHERE noPo=?');
    $hasil = $sql->execute([
      'Non Aktif',
      $idUserAsli,
      $noPo]);

    if($hasil){
      $sql=$db->prepare('UPDATE balistars_po set 
        statusPo=?, 
        idUserEdit=? 
        WHERE noPo=?');
      $hasil = $sql->execute([
        'Non Aktif',
        $idUserAsli,
        $noPo]);
    }
}
else{
  if($konsumen=='pelanggan'){
  $customer=explode('/',$customer);
  $idCustomer=$customer[0];
  $namaCustomer=$customer[1];
  $noTelpCustomer=$customer[2];
}
$tanggalPo = konversiTanggal($tanggalPo);
$tanggalSelesai = konversiTanggal($tanggalSelesai);
 // $grandTotal=ubahToInt($grandTotal);

  if($flag=='update'){
    $sql=$db->prepare('UPDATE balistars_po set 
      idCabang=?,
      tanggalPo=?,
      idCabangAdvertising=?,
      keterangan=?,
      idCustomer=?,
      namaCustomer=?,
      noTelpCustomer=?,
      tanggalSelesai=?,
      idUserEdit=?
      where noPo=?');
    $hasil=$sql->execute([
      $idCabang,
      $tanggalPo,
      $idCabangAdvertising,
      $keterangan,
      $idCustomer,
      $namaCustomer,
      $noTelpCustomer,
      $tanggalSelesai,
      $idUserAsli,
      $noPo]);

  }
  else{
    $sql=$db->prepare('INSERT INTO balistars_po set 
      idCabang=?,                                         
      noPo=?,
      tanggalPo=?,
      idCabangAdvertising=?,
      keterangan=?,
      idCustomer=?,
      namaCustomer=?,
      noTelpCustomer=?,
      tanggalSelesai=?,
      idUser=?');
    $hasil=$sql->execute([
      $idCabang,
      $noPo,
      $tanggalPo,
      $idCabangAdvertising,
      $keterangan,
      $idCustomer,
      $namaCustomer,
      $noTelpCustomer,
      $tanggalSelesai,
      $idUserAsli]);

    if($hasil){
      updateNoNota($db);
    }
  }
  //var_dump($sql->errorInfo());
}
$data = array('notifikasi' => 'Proses Gagal', 'status'=> false);
if($hasil){
      $data = array('notifikasi' => 'Proses Berhasil', 'status' => true, 'flag' => $flag, 'konsumen' => $konsumen);
    }
 echo json_encode($data);
?>