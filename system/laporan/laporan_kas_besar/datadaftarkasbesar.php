<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once 'fungsidebetkredit.php';

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
  'laporan_kas_besar'
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
$tanggalAwalSekali="2019-01-01"; 
$sekarang = $tanggalAwal;
$tanggalKemarin=waktuKemarin($tanggalAwal);
$selisih=selisihTanggal($tanggalAwal, $tanggalAkhir);

if($idCabang=='0'){
  $parameter1 = ' and idCabang !=?';
  $parameter2 = ' and balistars_penjualan.idCabang !=?';
} else {
  $parameter1 = ' and idCabang =?';
  $parameter2 = ' and balistars_penjualan.idCabang =? ';
}

if($tipe=='Semua'){
  $parameter3 = ' and tipe !=?';
  $parameter4 = ' and balistars_penjualan.tipePenjualan !=?';
} else {
  $parameter3 = ' and tipe =?';
  $parameter4 = ' and balistars_penjualan.tipePenjualan=? ';
}

//Menghitung Saldo Awal
$sqlDebet1=$db->prepare('SELECT SUM(jumlahPembayaran-PPH-biayaAdmin) as debetAwal 
  from balistars_piutang 
  inner join balistars_penjualan 
  on balistars_penjualan.noNota=balistars_piutang.noNota 
  where (balistars_piutang.tanggalPembayaran between ? and ?) 
  and balistars_piutang.bankTujuanTransfer=?'
  .$parameter2
  .$parameter4);
$sqlDebet1->execute([
  $tanggalAwalSekali,
  $tanggalKemarin,
  0,
  $idCabang,
  $tipe]);
$dataDebet1=$sqlDebet1->fetch();

$sqlDebet2  =$db->prepare('SELECT SUM(nilai) as debetAwal 
  from balistars_cabang_cash 
  where (tanggalCabangCash between ? and ?) 
  and statusFinal=?'
  .$parameter1);
$sqlDebet2->execute([
  $tanggalAwalSekali,
  $tanggalKemarin,
  'Final',
  $idCabang]);
$dataDebet2=$sqlDebet2->fetch();

$sqlKredit1 =$db->prepare('SELECT SUM(jumlahSetor) as kreditAwal 
  from balistars_setor_penjualan_cash 
  where (tanggalSetor between ? and ?)
  and statusSetor=?'
  .$parameter1
  .$parameter3);
$sqlKredit1->execute([
  $tanggalAwalSekali,
  $tanggalKemarin,
  'Aktif',
  $idCabang,
  $tipe]);
$dataKredit1=$sqlKredit1->fetch();


$saldo[0]=$dataDebet1['debetAwal']
          +$dataDebet2['debetAwal']
          -$dataKredit1['kreditAwal'];
$saldo[1]=0;
$saldo[2]=0;
$saldo[3]=0;
?>
<tr>
  <td></td>
  <td>-</td>
  <td>-</td>
  <td>-</td>
  <td>Saldo Awal</td>
  <td><strong>-</strong></td>
  <td><strong>-</strong></td>
  <td><strong><?=ubahToRp($saldo[0])?></strong></td>
</tr>
<?php
for($i=0; $i<=$selisih; $i++){
  $saldo=debetKasBesarCabang($idCabang,$sekarang,$saldo,$db,$jenis,$tipe);
  $saldo=kreditKasBesarCabang($idCabang,$sekarang,$saldo,$db,$jenis,$tipe);
  $sekarang=waktuBesok($sekarang);
}
 ?>
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td><strong>Total Debet</strong></td>
  <td><strong>Total Kredit</strong></td>
  <td><strong>Saldo Akhir</strong></td>
</tr>
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td><strong><?=ubahToRp($saldo[1])?></strong></td>
  <td><strong><?=ubahToRp($saldo[2])?></strong></td>
  <td><strong><?=ubahToRp($saldo[0])?></strong></td>
</tr>