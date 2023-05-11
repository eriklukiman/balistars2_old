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
  'laporan_pengeluaran_lain'
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

if($kodeAkunting=="Semua"){
  $sqlPengeluaran=$db->prepare('SELECT *, balistars_pengeluaran_lain.keterangan as keterangan, balistars_kode_akunting.keterangan as keteranganKode 
    FROM balistars_pengeluaran_lain 
    LEFT JOIN balistars_kode_akunting 
    on balistars_pengeluaran_lain.kodeAkunting=balistars_kode_akunting.kodeAkunting 
    where (tanggalPengeluaranLain between ? and ?) 
    and statusFinal=? 
    and statusPengeluaranLain=?
    order by tanggalPengeluaranLain');
  $sqlPengeluaran->execute([
    $tanggalAwal,$tanggalAkhir,
    'Final',
    'Aktif']);
}
else{
  $sqlPengeluaran=$db->prepare('SELECT *, balistars_pengeluaran_lain.keterangan as keterangan, balistars_kode_akunting.keterangan as keteranganKode 
    FROM balistars_pengeluaran_lain 
    LEFT JOIN balistars_kode_akunting 
    on balistars_pengeluaran_lain.kodeAkunting=balistars_kode_akunting.kodeAkunting 
    where (tanggalPengeluaranLain between ? and ?) 
    and statusFinal=? 
    and balistars_pengeluaran_lain.kodeAkunting=? 
    and statusPengeluaranLain=? 
    order by tanggalPengeluaranLain');
  $sqlPengeluaran->execute([
    $tanggalAwal,$tanggalAkhir,
    'Final',
    $kodeAkunting,
    'Aktif']);
}
$dataPengeluaran=$sqlPengeluaran->fetchAll();

$n=1;
$grandTotal=0;
foreach($dataPengeluaran as $row){
  if($row['kodeAkunting']=='0'){
    $row['keteranganKode']='Pengeluaran Lain - Lain';
  }
 ?>
<tr>
  <td><?=$n?></td>
  <td><?=wordwrap(ubahTanggalIndo($row['tanggalPengeluaranLain']),50,'<br>')?></td>
  <td><?=wordwrap($row['keterangan'],50,'<br>')?></td>
  <td><?=wordwrap($row['kodeAkunting'].' ('.$row['keteranganKode'].')',50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($row['nilai']),50,'<br>')?></td>
</tr>

<?php
$grandTotal+=$row['nilai'];
$n++;
}
 ?>

<tr>
  <td colspan="1"></td>
  <td>Grand Total</td>
  <td></td>
  <td></td>
  <td>Rp <?=ubahToRp($grandTotal)?></td>
</tr>
