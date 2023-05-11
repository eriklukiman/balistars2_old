<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'master_data_penyesuaian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if ($flag == 'cancel') {
  $sql = $db->prepare('UPDATE balistars_penyesuaian set statusPenyesuaian = ?, idUserEdit=? where idPenyesuaian = ?');
  $hasil = $sql->execute(['Non Aktif', $idUserAsli, $idPenyesuaian]);
  //var_dump($sql->errorInfo());
} else if ($flag == 'update') {
  $nominal = ubahToInt($nominal);
  $sql = $db->prepare('UPDATE balistars_penyesuaian set 
    jenisPenyesuaian=?,
    tanggalPenyesuaian=?,
    tipePembayaran=?,
    status=?,
    nominal=?,
    idCabang=?,
    keterangan=?,
    idUserEdit         =?
    where idPenyesuaian = ?');
  $hasil = $sql->execute([
    $jenisPenyesuaian,
    $tanggalPenyesuaian,
    $tipePembayaran,
    $status,
    $nominal,
    $idCabang,
    $keterangan,
    $idUserAsli,
    $idPenyesuaian
  ]);
} else if ($flag === 'tambah') {
  $nominal = ubahToInt($nominal);
  $sql = $db->prepare('INSERT INTO balistars_penyesuaian set 
    jenisPenyesuaian=?,
    tanggalPenyesuaian=?,
    tipePembayaran=?,
    status=?,
    nominal=?,
    idCabang=?,
    keterangan=?,
    idUser =?');
  $hasil = $sql->execute([
    $jenisPenyesuaian,
    $tanggalPenyesuaian,
    $tipePembayaran,
    $status,
    $nominal,
    $idCabang,
    $keterangan,
    $idUserAsli
  ]);
}


if ($hasil) {
  $data = array('flag' => $flag, 'notifikasi' => 1,);
} else {
  $data = array('flag' => $flag, 'notifikasi' => 2,);
}
echo json_encode($data);
