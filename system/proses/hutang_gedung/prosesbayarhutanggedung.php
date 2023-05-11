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
  'hutang_gedung'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag=="finalisasi"){
  $sqlCek=$db->prepare('SELECT noGiro from balistars_hutang_gedung_pembayaran 
    where idPembayaran=?');
  $sqlCek->execute([
    $idPembayaran]);
  $hasilCek = $sqlCek-> fetch();
  if($hasilCek['noGiro']===''){
    $hasil=false;
    $data = array('idHutangGedung' => $idPembayaran,'status' => false, 'notifikasi' => 'Proses Gagal, Nomor Giro Belum Terisi');
  } else{
    $sql=$db->prepare('UPDATE balistars_hutang_gedung_pembayaran set 
      statusCair=?
      where idPembayaran=?');
    $hasil=$sql->execute([
      'Cair',
      $idPembayaran]);
  } 
}
else if($flag=='cancel'){
  $sql=$db->prepare('DELETE from balistars_hutang_gedung_pembayaran 
      where idPembayaran=?');
  $hasil=$sql->execute([
      $idPembayaran]);
}
else{
  $tanggalPembayaran=konversiTanggal($tanggalPembayaran);
  $tanggalCair=konversiTanggal($tanggalCair);
  $jumlahPembayaran=ubahToInt($jumlahPembayaran);
  $nilaiSewa=ubahToInt($nilaiSewa);
  $sisaHutangAwal=ubahToInt($sisaHutangAwal);
  if($jumlahPembayaran>$sisaHutangAwal){
    $hasil=false;
    $data = array('idHutangGedung' => $idHutangGedung,'status' => false, 'notifikasi' => 'Proses Gagal, Jumlah Pembayaran Melebihi Hutang');
  }
  else{
    if($flag=="update"){
    $sql=$db->prepare('UPDATE balistars_hutang_gedung_pembayaran set 
      tanggalPembayaran=?,
      jumlahPembayaran=?,
      bankAsalTransfer=?,
      tanggalCair=?,
      noGiro=?,
      idUserEdit=?
      where idPembayaran=?');
    $hasil=$sql->execute([
      $tanggalPembayaran,
      $jumlahPembayaran,
      $bankAsalTransfer,
      $tanggalCair,
      $noGiro,
      $idUserAsli,
      $idPembayaran]);
      $data = array('idHutangGedung' => $idHutangGedung,'status' => false, 'notifikasi' => 'Proses Data Gagal Update');
    }
    else{
      $sql=$db->prepare('INSERT INTO balistars_hutang_gedung_pembayaran set 
        idHutangGedung=?,
        tanggalPembayaran=?,
        jumlahPembayaran=?,
        jenisPembayaran=?,
        bankAsalTransfer=?,
        tanggalCair=?,
        noGiro=?,
        statusCair=?,
        idUser=?');
      $hasil=$sql->execute([
        $idHutangGedung,
        $tanggalPembayaran,
        $jumlahPembayaran,
        $jenisPembayaran,
        $bankAsalTransfer,
        $tanggalCair,
        $noGiro,
        "Belum Cair",
        $idUserAsli]);
      $data = array('idHutangGedung' => $idHutangGedung,'status' => false, 'notifikasi' => 'Proses Data Gagal insert');

      //var_dump($sql->errorInfo());
    } 
  }
}

if($hasil){
  $data = array('idHutangGedung' => $idHutangGedung, 'status' => true, 'notifikasi' => 'Proses Data Berhasil');
}
echo json_encode($data);

?>