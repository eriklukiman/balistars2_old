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
  'laporan_labarugi_kotor'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalAwal=$tahun.'-'.$bulan.'-01';
$tanggalAkhir=$tahun.'-'.$bulan.'-31';



function fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$idCabang,$jenisPenyesuaian){
  if($idCabang==0){
    $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
      FROM balistars_penyesuaian 
      where jenisPenyesuaian=? 
      and (tanggalPenyesuaian between ? and ?) 
      and status=? 
      and statusPenyesuaian=?'); 
    $sqlPenyesuaian1->execute([
      $jenisPenyesuaian,
      $tanggalAwal,
      $tanggalAkhir,
      "Naik",
      "Aktif"]);

    $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
      FROM balistars_penyesuaian 
      where jenisPenyesuaian=? 
      and (tanggalPenyesuaian between ? and ?) 
      and status=? 
      and statusPenyesuaian=?');
    $sqlPenyesuaian2->execute([
      $jenisPenyesuaian,
      $tanggalAwal,
      $tanggalAkhir,
      "Turun",
      "Aktif"]);
  } 
  else{
    $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
      FROM balistars_penyesuaian 
      where jenisPenyesuaian=? 
      and (tanggalPenyesuaian between ? and ?) 
      and status=? 
      and idCabang=? 
      and statusPenyesuaian=?');
    $sqlPenyesuaian1->execute([
      $jenisPenyesuaian,
      $tanggalAwal,
      $tanggalAkhir,
      "Naik",
      $idCabang,
      "Aktif"]);

    $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
      FROM balistars_penyesuaian 
      where jenisPenyesuaian=? 
      and (tanggalPenyesuaian between ? and ?) 
      and status=? 
      and idCabang=? 
      and statusPenyesuaian=?');
    $sqlPenyesuaian2->execute([
      $jenisPenyesuaian,
      $tanggalAwal,
      $tanggalAkhir,
      "Turun",
      $idCabang,
      "Aktif"]);
  }
  $dataPenyesuaian1=$sqlPenyesuaian1->fetch();
  $dataPenyesuaian2=$sqlPenyesuaian2->fetch();
  return $dataPenyesuaian1['totalUP']-$dataPenyesuaian2['totalDown'];
}


$sqlPenjualan=$db->prepare('SELECT sum(grandTotal-nilaiPPN) as achievement 
  from balistars_penjualan 
  where idCabang=? 
  and (tanggalPenjualan between ? and ?) 
  and statusPenjualan=?');  
$sqlPenjualan->execute([
  $idCabang,
  $tanggalAwal,
  $tanggalAkhir,
  "Aktif"]);
$dataPenjualan=$sqlPenjualan->fetch();

$sqlPembelian=$db->prepare('SELECT SUM(grandTotal) as pembelian 
  from balistars_pembelian 
  where idCabang=? 
  and (tanggalPembelian between ? and ?) 
  and idSupplier!=? 
  and idSupplier!=? 
  and status=?');
$sqlPembelian->execute([
  $idCabang,
  $tanggalAwal,
  $tanggalAkhir,
  18,
  17,
  'Aktif']);
$dataPembelian=$sqlPembelian->fetch();

$sqlBiaya=$db->prepare('SELECT SUM(grandTotal) as biaya 
  from balistars_biaya 
  where idCabang=? 
  and (tanggalBiaya between ? and ?) 
  and statusBiaya=?');
$sqlBiaya->execute([
  $idCabang,
  $tanggalAwal,
  $tanggalAkhir,
  'Aktif']);
$dataBiaya=$sqlBiaya->fetch();

$sqlBiayaAdvertising=$db->prepare('SELECT SUM(grandTotal) as biaya 
  from balistars_biaya 
  where idCabang=? 
  and (tanggalBiaya between ? and ?) 
  and kodeAkunting=? 
  and statusBiaya=?');
$sqlBiayaAdvertising->execute([
  $idCabang,
  $tanggalAwal,
  $tanggalAkhir,
  "6141",
  "Aktif"]);
$dataBiayaAdvertising=$sqlBiayaAdvertising->fetch();

$klik=0;

$sqlDaily=$db->prepare('SELECT sum(jumlahKlik) as daily 
  FROM balistars_performa_mesin_laser 
  where (tanggalPerforma between ? and ?) 
  and idCabang=? 
  and statusKlik=? 
  order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
$sqlDaily->execute([
  $tanggalAwal,
  $tanggalAkhir, 
  $idCabang,
  'Aktif']);
$dataDaily=$sqlDaily->fetch();
$klik=$dataDaily['daily'];

$sqlKlik=$db->prepare('SELECT * FROM balistars_biaya_klik 
  where tanggalBiaya<=? 
  and idCabang=? 
  and statusBiayaKlik=? 
  order by tanggalBiaya DESC limit 1');
$sqlKlik->execute([
  $tanggalAkhir,
  $idCabang,
  'Aktif']);
$dataKlik=$sqlKlik->fetch();

$banyakKlik=$klik;
$totalBiayaKlik=($banyakKlik*$dataKlik['jumlahBiaya']);

$sqlPenyusutan=$db->prepare('SELECT * from balistars_penyusutan_cabang 
    where idCabang=? 
    and tanggalPenyusutan<=? 
    and statusPenyusutan=?
    order by tanggalPenyusutan DESC limit 1');    
$sqlPenyusutan->execute([
    $idCabang,
    $tanggalAkhir,
    'Aktif']);
$dataPenyusutan=$sqlPenyusutan->fetch();

$hpp=0;
$materialCost=
    $dataPembelian['pembelian']+
    $dataBiayaAdvertising['biaya']+
    fungsiPenyesuaian($db,($tanggalAwal),$tanggalAkhir,$idCabang,"Pembelian");
    
$biaya=
    $dataBiaya['biaya']-
    $dataBiayaAdvertising['biaya'];
    
$omset=
    $dataPenjualan['achievement']+
    fungsiPenyesuaian($db,($tanggalAwal),$tanggalAkhir,$idCabang,"Penjualan");

$profitBruto=
    $omset-
    $materialCost-
    $biaya-
    $totalBiayaKlik-
    $dataPenyusutan['nilaiSetorHO']-
    $dataPenyusutan['nilaiPenyusutan']-
    ($omset*0.15);


if($omset!=0){
  $hpp=(($materialCost+$biaya+($totalBiayaKlik))-12000000)*100/$omset;
}

$bonusKacab=$profitBruto*0.02;

$bonusProgresif=0;
if($profitBruto>40000000){
  $bonusProgresif=$bonusKacab/2;
}

$bonusTotal=$bonusKacab+$bonusProgresif;

$style='btn btn-success';
if($profitBruto<0){
  $style='btn btn-danger';
}
?>

<table class="table table-bordered" style="font-size: 14px;">
    <tbody>
      <tr>
        <td style="width: 15%;">Achievement</td>
        <td style="width: 25%;">: Rp <?=ubahToRp($omset)?></td>
        <td>
          <span class="btn btn-primary" style="font-weight: bold; font-size: 20px;">
            HPP <br>
            <?=number_format($hpp,2)?> %
          </span>
        </td>
      </tr>
      <tr>
        <td>Material Cost</td>
        <td>: Rp <?=ubahToRp($materialCost)?></td>
        <td></td>
      </tr>
      <tr>
        <td>Biaya</td>
        <td>: Rp <?=ubahToRp($biaya)?></td>
        <td></td>
      </tr>
      <tr>
        <td>Total Klik / Nilai</td>
        <td>: <?=ubahToRp($banyakKlik)?> / Rp <?=ubahToRp($totalBiayaKlik)?></td>
        <td></td>
      </tr>
      <tr>
        <td>Head Office</td>
        <td>: Rp <?=ubahToRp($dataPenyusutan['nilaiSetorHO'])?></td>
        <td></td>
      </tr>
      <tr>
        <td>Penyusutan</td>
        <td>: Rp <?=ubahToRp($dataPenyusutan['nilaiPenyusutan'])?></td>
        <td></td>
      </tr>
      <tr>
        <td>Gaji Team</td>
        <td>: Rp <?=ubahToRp($omset*0.15)?></td>
        <td></td>
      </tr>
      <tr>
        <td style="font-weight: bold; font-size: 20px;">Profit Bruto</td>
        <td>: <span class="<?=$style?>" style="font-weight: bold; font-size: 20px;">Rp <?=ubahToRp($profitBruto)?></span></td>
        <td></td>
      </tr>
      <tr>
        <td style="font-weight: bold; font-size: 20px;">Bonus Kacab</td>
        <td>: <span class="<?=$style?>" style="font-weight: bold; font-size: 20px;">Rp <?=ubahToRp($bonusKacab)?></span></td>
        <td></td>
      </tr>
      <tr>
        <td style="font-weight: bold; font-size: 20px;">Bonus Progresif</td>
        <td>: <span class="<?=$style?>" style="font-weight: bold; font-size: 20px;">Rp <?=ubahToRp($bonusProgresif)?></span></td>
        <td></td>
      </tr>
      <tr>
        <td style="font-weight: bold; font-size: 20px;">Bonus Total</td>
        <td>: <span class="<?=$style?>" style="font-weight: bold; font-size: 20px;">Rp <?=ubahToRp($bonusTotal)?></span></td>
        <td></td>
      </tr>
    </tbody>
  </table>