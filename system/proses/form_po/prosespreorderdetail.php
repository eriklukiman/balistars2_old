<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
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
  'form_po'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flagDetail == 'cancel'){
  $sql = $db->prepare('UPDATE balistars_po_detail set 
    statusPoDetail = ?, 
    idUserEdit=? 
    where idPoDetail = ?');
  $hasil = $sql->execute([
    'Non Aktif',
    $idUserAsli, 
    $idPoDetail]);
  $data = array('flagDetail' => $flagDetail,'status'=>false, 'notifikasi' => 'Proses Gagal');
}
else{
  $hargaSatuan = ubahToInt($hargaSatuan);
  $qty = ubahToInt($qty);  
  $nilai = $hargaSatuan*$qty;
  $cek=explode('x', $ukuran);

  if(count($cek)<2 || $cek[0]=="" || $cek[0]==" " || $cek[1]=="" || $cek[1]==" " || count($cek)>2 || $namaBahan=='' || $finishing==''){
    $hasil=false;
    $data = array('flagDetail' => $flagDetail,'status'=> false, 'notifikasi' => 'Format ukuran salah');
  }
  else
  {

    $cek2=explode(',', $cek[0]); 
    $cek3=explode(',', $cek[1]);
    $lebar = floatval(str_replace(',', '.', str_replace('.', '', $cek[0])));
    $panjang = floatval(str_replace(',', '.', str_replace('.', '', $cek[1])));
    $ukuran=$lebar.'x'.$panjang;

    if($flagDetail=='update'){
      $sql=$db->prepare('UPDATE balistars_po_detail set 
        noPo=?,
        namaBahan=?,
        ukuran=?,
        finishing=?,
        qty=?,
        hargaSatuan=?,
        nilai=?,
        idUserEdit=?
        where idPoDetail=?');
      $hasil=$sql->execute([
        $noPo,
        $namaBahan,
        $ukuran,
        $finishing,
        $qty,
        $hargaSatuan,
        $nilai,
        $idUserAsli,
        $idPoDetail]);
    }
    else{
      $sql=$db->prepare('INSERT INTO balistars_po_detail set 
        noPo=?,
        namaBahan=?,
        ukuran=?,
        finishing=?,
        qty=?,
        hargaSatuan=?,
        nilai=?,
        idUser=?');
      $hasil=$sql->execute([
        $noPo,
        $namaBahan,
        $ukuran,
        $finishing,
        $qty,
        $hargaSatuan,
        $nilai,
        $idUserAsli]);
    }
    $data = array('flagDetail' => $flagDetail,'status'=> false, 'notifikasi' => 'Proses Gagal'); 
  }
}

if($hasil){
  $data = array('flagDetail' => $flagDetail,'status'=>true, 'notifikasi' => 'Proses Berhasil'); 
}
echo json_encode($data);

?>