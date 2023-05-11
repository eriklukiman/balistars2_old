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
  'form_setor_pettycash'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

$tanggal      = explode(' - ', $rentang);
$tanggalAwal  = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]);

$sql=$db->prepare('
  SELECT * FROM balistars_kas_kecil_setor 
  inner join balistars_bank 
  on balistars_kas_kecil_setor.idBank=balistars_bank.idBank 
  where balistars_kas_kecil_setor.idCabang=?
  and (tanggalSetor between ? and ?)
  and statusKasKecilSetor=?');
$sql->execute([
  $dataLogin['idCabang'],
  $tanggalAwal,
  $tanggalAkhir,
  'Aktif']);
$hasil=$sql->fetchAll();

$n = 1;
foreach($hasil as $row){
  ?>
  <tr>
    <td style="vertical-align: top;"><?=$n?></td>
    <td style="vertical-align: top;" ><?=wordwrap(ubahTanggalIndo($row['tanggalSetor']),50,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['namaBank'],50,'<br>')?></td>
    <td style="vertical-align: top;" >Rp <?=wordwrap(ubahToRp($row['jumlahSetor']),50,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['keterangan'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>