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
  'absensi_pulang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);
$tanggal=explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 

if($idCabang=='0'){
  $parameter='and balistars_cabang.idCabang!=?';
}
else{
  $parameter='and balistars_cabang.idCabang=?';
}


$sqlAbsensi=$db->prepare('SELECT * FROM balistars_absensi inner join balistars_pegawai on balistars_absensi.idPegawai=balistars_pegawai.idPegawai inner join balistars_cabang on balistars_absensi.idCabang=balistars_cabang.idCabang where (tanggalDatang between ? and ?) and jamPulang!=?'.$parameter.' order by jamPulang ASC');
$sqlAbsensi->execute([$tanggalAwal,$tanggalAkhir,"",$idCabang]);
$dataAbsensi=$sqlAbsensi->fetchAll();

$n = 1;
foreach($dataAbsensi as $row){
  ?>
  <tr>
    <td><?=$n?></td>
    <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
    <td><?=wordwrap($row['NIK'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaPegawai'],50,'<br>')?></td>
    <td><?=wordwrap($row['jamPulang'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
