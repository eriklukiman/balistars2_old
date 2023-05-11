<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';

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
  'form_pemutihan_piutang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$data = array('notifikasi' => 2);
if($flag=='cancel'){
  $sql=$db->prepare('UPDATE balistars_pemutihan_piutang set
  statusPemutihan=?,
  idUserEdit=?
  where idPemutihan=?');
  $hasil=$sql->execute([
    'Non Aktif',
    $idUserAsli,
    $idPemutihan]);
  
  if($hasil){
      $data = array('notifikasi' => 1, 'flag' => $flag);
    } 
}
else{
  $status=true;
  $sql=$db->prepare('SELECT idPemutihan from balistars_pemutihan_piutang where noNota=? and statusPemutihan=?');
  $sql->execute([$noNota,'Aktif']);
  $dataSearch=$sql->fetch();
  if($dataSearch){
    $data = array('notifikasi' => 3);
    $status=false;
  }
  $tanggalPemutihan=konversiTanggal($tanggalPemutihan);
  $total=ubahToInt($total);
  $sisaPiutang=ubahToInt($sisaPiutang);

  if($total==0){
    $data = array('notifikasi' => 4);
    $status=false;
  }
  if($status==true){
    if($flag=='update'){
      $sql=$db->prepare('UPDATE balistars_pemutihan_piutang set 
        tanggalPemutihan=?,
        noNota=?,
        namaCabang=?,
        namaCustomer=?,
        grandTotal=?,
        sisaPiutang=?,
        idUserEdit=?
        where idPemutihan=?');
      $hasil=$sql->execute([
        $tanggalPemutihan,
        $noNota,
        $namaCabang,
        $namaCustomer,
        $total,
        $sisaPiutang,
        $idUserAsli,
        $idPemutihan]);
    }
    else{
      $sql=$db->prepare('INSERT INTO balistars_pemutihan_piutang set 
        tanggalPemutihan=?,
        noNota=?,
        namaCabang=?,
        namaCustomer=?,
        grandTotal=?,
        sisaPiutang=?,
        idUser=?');
      $hasil=$sql->execute([
        $tanggalPemutihan,
        $noNota,
        $namaCabang,
        $namaCustomer,
        $total,
        $sisaPiutang,
        $idUserAsli]);
    }
    if($hasil){
      $data = array('notifikasi' => 1, 'flag' => $flag);
    } 
  }
}
echo json_encode($data);

?>