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
  'laporan_penjualan_sub'
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

if($idCabang==0){
  $sql=$db->prepare('SELECT SUM(nilaiPembayaran) as jumlahPembayaran, idPenjualanDetail 
    FROM balistars_biaya_sub 
    where (tanggalPembayaran between ? and ?) 
    and statusBiayaSub=? 
    group by idPenjualanDetail');
  $sql->execute([
    $tanggalAwal,$tanggalAkhir,
    'Aktif']);
}
else{
  $sql=$db->prepare('SELECT SUM(nilaiPembayaran) as jumlahPembayaran, idPenjualanDetail 
    FROM balistars_biaya_sub 
    where (tanggalPembayaran between ? and ?) 
    and idCabang=? 
    and statusBiayaSub=? 
    group by idPenjualanDetail');
  $sql->execute([
    $tanggalAwal,$tanggalAkhir,
    $idCabang,
    'Aktif']);
}
$hasil=$sql->fetchAll();

$totalNilaiPenjualan=0; 
$totalNilaiPembayaran=0;
$totalProfit=0;
$n=1;
foreach($hasil as $data){
  $sqlCustomer=$db->prepare('SELECT * FROM balistars_penjualan_detail 
    inner join balistars_penjualan 
    on balistars_penjualan.noNota=balistars_penjualan_detail.noNota 
    where balistars_penjualan_detail.idPenjualanDetail=?');
  $sqlCustomer->execute([$data['idPenjualanDetail']]);
  $dataCustomer=$sqlCustomer->fetch();

  if($dataCustomer['statusCancel']!='ok'){
    $dataCustomer['nilai']=0;
  }

  $subTotal=0;  
  $sqlSub=$db->prepare('SELECT * 
    FROM balistars_biaya_sub 
    where idPenjualanDetail=? 
    and (tanggalPembayaran between ? and ?) 
    and statusBiayaSub=?');
  $sqlSub->execute([
    $data['idPenjualanDetail'],
    $tanggalAwal,$tanggalAkhir,
    'Aktif']);
  $dataSub=$sqlSub->fetchAll();

  if(!$dataSub){
    $rowspan=1;
  }
  else{
    $rowspan=count($dataSub);
  }

  ?>
  <tr>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=$n?></td>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap(ubahTanggalIndo($dataCustomer['tanggalPenjualan']),50,'<br>')?></td>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($dataCustomer['noNota'],50,'<br>')?></td>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($dataCustomer['namaCustomer'],50,'<br>')?></td>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($dataCustomer['namaBahan'],50,'<br>')?></td>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>">Rp <?=wordwrap(ubahToRp($dataCustomer['nilai']),50,'<br>')?></td>
   <?php 
   $cek=1;
   if(!$dataSub){
    ?>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td style="text-align: right; vertical-align: top;">Rp <?=wordwrap(ubahToRp(0),50,'<br>')?></td>
    <td style="text-align: center; vertical-align: top;"><?=wordwrap(0,50,'<br>')?></td>

  </tr>
    <?php
   } 
   else{
    foreach($dataSub as $item){
      if($cek==1){
        ?>
        <td><?=wordwrap(ubahTanggalIndo($item['tanggalPembayaran']),25,'<br>')?></td>
        <td>Rp <?=wordwrap(ubahToRp($item['nilaiPembayaran']),50,'<br>')?></td>
        <td><?=wordwrap($item['namaSupplier'],50,'<br>')?></td>
        <td><?=wordwrap($item['keterangan'],50,'<br>')?></td>

        <td style="text-align: right; vertical-align: top;" rowspan="<?=$rowspan?>">Rp <?=wordwrap(ubahToRp($dataCustomer['nilai']-$data['jumlahPembayaran']),50,'<br>')?></td>
        <td style="text-align: right; vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap(ubahToRp(($dataCustomer['nilai']-$data['jumlahPembayaran'])/$dataCustomer['nilai']*100),50,'<br>')?>%</td>
      </tr>
        <?php
      }
      else{
        ?>
        <tr>
          <td><?=wordwrap(ubahTanggalIndo($item['tanggalPembayaran']),25,'<br>')?></td>
          <td>Rp <?=wordwrap(ubahToRp($item['nilaiPembayaran']),50,'<br>')?></td>
          <td><?=wordwrap($item['namaSupplier'],50,'<br>')?></td>
          <td><?=wordwrap($item['keterangan'],50,'<br>')?></td>
        </tr>
      <?php
      }
      $cek++;
      $subTotal+=$item['nilaiPembayaran'];
    }
   }
  $totalNilaiPenjualan=$totalNilaiPenjualan+$dataCustomer['nilai'];
  $totalNilaiPembayaran=$totalNilaiPembayaran+$subTotal;
  $totalProfit=$totalProfit+$dataCustomer['nilai']-$subTotal;
  $n++;
}
?>
<tr>
  <td colspan="5" style="text-align: center;">Grand Total</td>
  <td>Rp <?=ubahToRp($totalNilaiPenjualan)?></td>
  <td></td>
  <td colspan="3">Rp <?=ubahToRp($totalNilaiPembayaran)?></td>
  <td style="font-weight: bold;">Rp <?=ubahToRp($totalProfit)?></td>
  <?php 
  if($totalNilaiPenjualan==0){
    ?>
    <td>0</td>
    <?php
  } else{
    ?>
  <td><?=round($totalProfit/$totalNilaiPenjualan*100)?>%</td>
  <?php 
  } ?>
</tr>