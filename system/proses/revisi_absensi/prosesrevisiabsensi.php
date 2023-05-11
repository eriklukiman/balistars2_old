<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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
  'revisi_absensi'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 

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

//** excecute sql revisi **

$executeLibur = [
  'Hari Libur',
  $idCabang,
  'Hari Kerja'
];

$tandaTanya1 = [];
foreach ($hariLibur as $index => $value) {
  $tandaTanya1[] = '?';
  $executeLibur[] = $value;
}

$joinTandaTanya1 = join(',', $tandaTanya1);

$sql = $db->prepare('UPDATE balistars_absensi set 
  jenisPoin    = ?
  where idAbsensi 
  IN (SELECT idAbsensi  
    FROM balistars_absensi 
    inner join balistars_pegawai 
    on balistars_absensi.idPegawai=balistars_pegawai.idPegawai
    where balistars_absensi.idCabang=? 
    and jenisPoin=?
    AND tanggalDatang IN ('.$joinTandaTanya1.'))');
$hasil1 = $sql->execute($executeLibur);
//var_dump($sql,$executeLibur);



//** excecute sql revisi 2 **

$executeLibur2 = [
  'Hari Kerja',
  $idCabang,
  'Hari Libur',
  $tanggalAwal,
  $tanggalAkhir
];

$tandaTanya2 = [];
foreach ($hariLibur as $index => $value) {
  $tandaTanya2[] = '?';
  $executeLibur2[] = $value;
}

$joinTandaTanya2 = join(',', $tandaTanya2);

$sql = $db->prepare('UPDATE balistars_absensi set 
  jenisPoin    = ?
  where idAbsensi 
  IN (SELECT idAbsensi  
    FROM balistars_absensi 
    inner join balistars_pegawai 
    on balistars_absensi.idPegawai=balistars_pegawai.idPegawai
    where balistars_absensi.idCabang=? 
    and jenisPoin=? 
    AND (tanggalDatang BETWEEN ? and ?)
    AND tanggalDatang NOT IN ('.$joinTandaTanya2.'))
   ');
$hasil2 = $sql->execute($executeLibur2);
//var_dump($sql->errorInfo());

$data = array('notifikasi' => 2);
if($hasil1 && $hasil2){
  $data = array('notifikasi' => 1);
}
echo json_encode($data);

?>