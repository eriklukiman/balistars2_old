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
  'laporan_uang_masuk'
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
$selisihTanggal=selisihTanggal($tanggalAwal,$tanggalAkhir);

if($idCabang==0){
  $parameter1=' and balistars_penjualan.idCabang !=?';
  $parameter2=' and idCabang !=?';
} else {
  $parameter1=' and balistars_penjualan.idCabang =?';
  $parameter2=' and idCabang =?';
}

$totalCash=0;
$totalTransfer=0;
$totalJumlah=0;
$totalSetor=0; 

for ($i=0; $i <= $selisihTanggal ; $i++) { 
  $sqlCash=$db->prepare('SELECT SUM(jumlahPembayaran) as totalCash 
    FROM balistars_piutang 
    inner join balistars_penjualan 
    on balistars_piutang.noNota=balistars_penjualan.noNota 
    where balistars_piutang.bankTujuanTransfer=? 
    and balistars_piutang.jenisPembayaran=? 
    and (balistars_piutang.tanggalPembayaran=?) 
    and statusPenjualan=?'
    .$parameter1);
  $sqlCash->execute([
    0,
    "Cash",
    $tanggalAwal,
    'Aktif',
    $idCabang]);

  $sqlTransfer=$db->prepare('SELECT SUM(jumlahPembayaran) as totalTransfer, SUM(biayaAdmin) as totalAdmin, SUM(PPH) as totalPPH  
    FROM balistars_piutang 
    inner join balistars_penjualan 
    on balistars_piutang.noNota=balistars_penjualan.noNota 
    where balistars_piutang.bankTujuanTransfer!=? 
    and balistars_piutang.jenisPembayaran=? 
    and balistars_penjualan.statusPenjualan=? 
    and (balistars_piutang.tanggalPembayaran=?)'
    .$parameter1);
  $sqlTransfer->execute([
    0,
    "Transfer",
    'Aktif',
    $tanggalAwal,
    $idCabang]);  

  $sqlPPN=$db->prepare('SELECT SUM(jumlahPembayaran) as totalPPN 
    FROM balistars_piutang 
    inner join balistars_penjualan 
    on balistars_piutang.noNota=balistars_penjualan.noNota 
    where balistars_piutang.bankTujuanTransfer=? 
    and balistars_piutang.jenisPembayaran=? 
    and balistars_penjualan.statusPenjualan=? 
    and (balistars_piutang.tanggalPembayaran=?)'
    .$parameter1);
  $sqlPPN->execute([
    "-",
    "PPN",
    'Aktif',
    $tanggalAwal,
    $idCabang]);

  $sqlSetor=$db->prepare('SELECT SUM(jumlahSetor) as totalSetor 
    FROM balistars_setor_penjualan_cash 
    where tanggalSetor=? 
    and statusSetor=?'
    .$parameter2);
  $sqlSetor->execute([
    $tanggalAwal,
    'Aktif',
    $idCabang]);
 
  $dataCash=$sqlCash->fetch();
  $dataTransfer=$sqlTransfer->fetch();
  $dataPPN=$sqlPPN->fetch();       
  $dataSetor=$sqlSetor->fetch();

  $jumlah=$dataCash['totalCash']+$dataTransfer['totalTransfer']+$dataPPN['totalPPN'];
 ?>
<tr>
  <td><?=wordwrap(ubahTanggalIndo($tanggalAwal),50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($dataCash['totalCash']),50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($dataTransfer['totalTransfer']),50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($jumlah),50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($dataSetor['totalSetor']+$dataTransfer['totalTransfer']-$dataTransfer['totalAdmin']-$dataTransfer['totalPPH']),50,'<br>')?></td>
</tr>
  <?php
  $totalJumlah+=$jumlah;
  $totalTransfer+=($dataTransfer['totalTransfer']+$dataPPN['totalPPN']);
  $totalCash+=$dataCash['totalCash'];
  $totalSetor+=$dataSetor['totalSetor']+$dataTransfer['totalTransfer']-$dataTransfer['totalAdmin']-$dataTransfer['totalPPH'];
  $tanggalAwal=waktuBesok($tanggalAwal);

}
 ?>
<tr>
  <td>TOTAL</td>
  <td>Rp <?=ubahToRp($totalCash)?></td>
  <td>Rp <?=ubahToRp($totalTransfer)?></td>
  <td>Rp <?=ubahToRp($totalJumlah)?></td>
  <td>Rp <?=ubahToRp($totalSetor)?></td>
</tr>