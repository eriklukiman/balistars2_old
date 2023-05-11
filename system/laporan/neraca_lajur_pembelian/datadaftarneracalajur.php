<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';

#fungsi - fungsi neraca
  // include_once('fungsineracalajur/fungsikasbesarcabang.php');
  include_once('fungsineracalajur/fungsikaskecilcabang.php');
  include_once('fungsineracalajur/fungsikasbank.php');
  // include_once('fungsineracalajur/fungsikasadvertising.php');
  // include_once('fungsineracalajur/fungsikaspiutang.php');
  // include_once('fungsineracalajur/fungsikaspemutihanpiutang.php');
  include_once('fungsineracalajur/fungsikaspersglobal.php');
  // include_once('fungsineracalajur/fungsikasbayardimuka.php');
  include_once('fungsineracalajur/fungsikashutang.php');
  include_once('fungsineracalajur/fungsikashutangA2.php');
  include_once('fungsineracalajur/fungsikashutanglancar.php');
  // include_once('fungsineracalajur/fungsikaspph.php');
  include_once('fungsineracalajur/fungsikasppn.php');
  // include_once('fungsineracalajur/fungsikasmesin.php');
  // include_once('fungsineracalajur/fungsikaskendaraaninventaris.php');
  // include_once('fungsineracalajur/fungsikasakumulasipenyusutanmesinkendaraan.php');
  // include_once('fungsineracalajur/fungsikasmodalawal.php');
  // include_once('fungsineracalajur/fungsikascadanganpajak.php');
  // include_once('fungsineracalajur/fungsikasprive.php');
  // include_once('fungsineracalajur/fungsikaspenjualan.php');
  // include_once('fungsineracalajur/fungsikashpp.php');
  // include_once('fungsineracalajur/fungsikasbiaya.php');
  // include_once('fungsineracalajur/fungsikasbiayaadmin.php');
  // include_once('fungsineracalajur/fungsikasbiayasewa.php');
  // include_once('fungsineracalajur/fungsikasbiayapenyusutan.php');
  // include_once('fungsineracalajur/fungsikaspendapatan.php');
  // include_once('fungsineracalajur/fungsikaspendapatanlain.php');

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
  'neraca_lajur'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$tipe='';
extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 
$tanggalKemarin   =waktuKemarin($tanggalAwal); 
$tanggalAwalSekali="2019-01-01";

$total = array(
  'debet' => 0, 
  'kredit' => 0,
  'saldoAwal' => 0,
  'saldoAkhir' => 0,
  'memorial' => 0,
  'laba' => 0,
  'neraca' => 0);

function tampilTable($kodeACC,$keterangan,$saldoAwal,$debet,$kredit,$saldoAkhir,$memorial,$laba,$neraca,$total)
  {
    $stat='';
    if($kodeACC=='6398'){
      $stat = 'hidden';
    }
    ?>
    <tr <?=$stat?>>
      <td>
        <?=$kodeACC?>    
        <input type="number" hidden value="<?=(int)$debet?>"  id="<?=$kodeACC?>">
        <input type="number" hidden value="<?=$saldoAkhir?>"  id="saldo-<?=$kodeACC?>">
        <input type="number" hidden value="<?=$neraca?>"  id="neraca-<?=$kodeACC?>">
      </td>
      <td><?=$keterangan?></td>
      <td><?=ubahToRp($saldoAwal)?></td>
      <td id="debet-<?=$kodeACC?>"><?=ubahToRp($debet)?></td>
      <td><?=ubahToRp($kredit)?></td>
      <td id="saldo-val<?=$kodeACC?>"><?=ubahToRp($saldoAkhir)?></td>
      <td><?=ubahToRp($memorial)?></td>
      <td><?=ubahToRp($laba)?></td>
      <td id="neraca-val<?=$kodeACC?>"><?=ubahToRp($neraca)?></td>
    </tr>
    <?php

    $total['saldoAwal'] += $saldoAwal;
    $total['debet']     += $debet;
    $total['kredit']    += $kredit;
    $total['saldoAkhir']+= $saldoAkhir;
    $total['memorial']  += $memorial;
    $total['laba']      += $laba;
    $total['neraca']    += $neraca;
    return $total;
  }
  ?>

<?php
  // $total = kasBesarCabang($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  $total = kasKecilCabang($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  $total = kasBank($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasAdvertising($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasPiutang($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasPemutihanPiutang($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  $total = kasPersGlobal($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasBayarDiMuka($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  $total = kasHutang($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  $total = kasHutangA2($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  $total = kasHutangLancar($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasPPH($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  $total = kasPPN($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasMesin($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasKendaraanInventaris($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasPenyusutanMesinKendaraan($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasModalAwal($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);   
  // $total = kasCadanganPajak($total,$db,$tanggalAwal,$tanggalAkhir,$tipe); 
  // $total = kasPrive($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasPenjualan($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasHpp($total,$db,$tanggalAwal,$tanggalAkhir,$tipe); 
  // $total = kasBiaya($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasBiayaAdmin($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasBiayaSewa($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasBiayaPenyusutan($total,$db,$tanggalAwal,$tanggalAkhir,$tipe); 
  // $total = kasPendapatan($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  // $total = kasPendapatanLain($total,$db,$tanggalAwal,$tanggalAkhir,$tipe);
  tampilTable(ubahToRp(intval($total['debet'])-intval($total['kredit'])),'<b>Grand Total<b>',$total['saldoAwal'],$total['debet'],$total['kredit'],$total['saldoAkhir'],$total['memorial'],$total['laba'],$total['neraca'],$total)

?>