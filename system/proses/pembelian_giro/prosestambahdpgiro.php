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
  'pembelian_giro'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag == 'cancel'){
  $total=ubahToInt($total);
  $sql = $db->prepare('UPDATE balistars_dpgiro SET 
    statusDpGiro =?,
    idUserEdit=?
   where idDpGiro = ?');
  $hasil = $sql->execute([
    'Non Aktif',
    $idUserAsli,
    $idDpGiro]);
  $data = array('status' => false,'notifikasi' => 'Proses Gagal', 'idSupplier' => $idSupplier, 'namaSupplier' => $namaSupplier, 'total' => $total, 'tipePembelianDp' => $tipePembelianDp, 'periode' => $periode,'disabled' => $disabled);
}
else{
  $tanggalCairDp=konversiTanggal($tanggalCairDp);
  $dp=ubahToInt($dp);
  $total=ubahToInt($total);
  if($dp>$total){
    $hasil=false;
    $data = array('notifikasi' => 'DP Melebihi total Pembelian', 'idSupplier' => $idSupplier, 'namaSupplier' => $namaSupplier, 'total' => $total, 'tipePembelianDp' => $tipePembelianDp, 'periode' => $periode,'disabled' => $disabled);
  }
  else{
    if($flag=="update"){
      $sql=$db->prepare('UPDATE balistars_dpgiro set 
        idSupplier=?,
        tanggalCairDp=?,
        dp=?,
        idBank=?,
        noGiro=?,
        tipePembelian=?,
        periode=?, 
        idUserEdit=?
        where idDpGiro=?');
      $hasil=$sql->execute([
        $idSupplier,
        $tanggalCairDp,
        $dp,
        $bankAsalDp,
        $noGiroDp,
        $tipePembelianDp,
        $periode,
        $idUserAsli,
        $idDpGiro]);
    }
    else{
      $sql=$db->prepare('INSERT INTO balistars_dpgiro set 
        idSupplier=?,
        tanggalCairDp=?,
        dp=?,
        idBank=?,
        noGiro=?,
        tipePembelian=?,
        jenisGiro=?,
        periode=?, 
        idUser=? ');
      $hasil=$sql->execute([
        $idSupplier,
        $tanggalCairDp,
        $dp,
        $bankAsalDp,
        $noGiroDp,
        $tipePembelianDp,
        'DP',
        $periode,
        $idUserAsli]);
    }
    $data = array('status' => false,'notifikasi' => 'Proses Gagal', 'idSupplier' => $idSupplier, 'namaSupplier' => $namaSupplier, 'total' => $total, 'tipePembelianDp' => $tipePembelianDp, 'periode' => $periode,'disabled' => $disabled);
  }
  //var_dump($sql->errorInfo());
}

if($hasil){
  $data = array('status' => true,'notifikasi' => 'Proses Berhasil','idSupplier' => $idSupplier, 'namaSupplier' => $namaSupplier, 'total' => $total, 'tipePembelianDp' => $tipePembelianDp, 'periode' => $periode,'disabled' => $disabled);
}
echo json_encode($data);

?>