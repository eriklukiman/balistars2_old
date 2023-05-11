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
  'penjualan_faktur_pajak'
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


if($idCabang=="0" || $idCabang==0){
  $sql=$db->prepare('
    SELECT * FROM balistars_penjualan 
    where (balistars_penjualan.tanggalPenjualan between ? and ?) 
    and balistars_penjualan.statusFakturPajak=? 
    and statusPenjualan=?
    order by balistars_penjualan.tanggalPenjualan');
  $sql->execute([
    $tanggalAwal,
    $tanggalAkhir,
    'Dengan Faktur',
    'Aktif']);
}
else{
  $sql=$db->prepare('
    SELECT * FROM balistars_penjualan 
    where (balistars_penjualan.tanggalPenjualan between ? and ?) 
    and balistars_penjualan.statusFakturPajak=? 
    and balistars_penjualan.idCabang=? 
    and statusPenjualan =?
    order by balistars_penjualan.tanggalPenjualan');
  $sql->execute([
    $tanggalAwal,
    $tanggalAkhir,
    'Dengan Faktur',
    $idCabang,
    'Aktif']);
}
$hasil=$sql->fetchAll();

$totalPPN=0;
$totalDPP=0;
$totalPenjualan=0;
$n = 1;
foreach($hasil as $row){
  $faktur='faktur'.$n;
  ?>
  <tr>
    <td><?=$n?></td>
    <td><?=ubahTanggalIndo($row['tanggalPenjualan'])?></td>
    <td><?=$row['noNota']?></td>
    <td><?=$row['namaCustomer']?></td>
    <td>Rp <?=ubahToRp($row['nilaiPPN'])?></td>
    <td>Rp <?=ubahToRp($row['grandTotal']-$row['nilaiPPN'])?></td>
    <td>Rp <?=ubahToRp($row['grandTotal'])?></td>
    <td>
      <input type="text" class="form-control" placeholder="Input Cabang Customer" name="cabangCustomer" data-id="<?=$faktur?>" value="<?=$row['cabangCustomer']??''?>">
    </td>
    <td>
      <input type="text" class="form-control" placeholder="Input Nomor faktur Pajak" name="noFakturPajak" data-id="<?=$faktur?>" value="<?=$row['noFakturPajak']??''?>">
    </td>
    <td>
       <?php 
      $display = '';
      if($dataCekMenu['tipeEdit']=='0'){
       $display = 'display : none;';
      }
       ?>
      <button type    = "button"
              title   = "Final" 
              class   = "btn btn-primary" 
              onclick = "prosesPenjualanFakturPajak('<?=$row['idPenjualan']?>','<?=$faktur?>')">
        <i class="fa fa-save"></i>
      </button>
      <button type="button" 
              title="Print" 
              class="btn btn-success"
              data-fileType="pdf" 
              onclick="window.open('./print_nota/?noNota=<?= $row['noNota'] ?>', '_blank')" >
        <i class="fa fa-print"></i>
      </button>
    </td>   
  </tr>
  <?php
  $totalPenjualan+=$row['grandTotal'];
  $totalDPP+=$row['grandTotal']-$row['nilaiPPN'];
  $totalPPN+=$row['nilaiPPN'];
  $n++;
}
?>
<tr>
  <td colspan="5" style="text-align: center;"><b>Total</b></td>
  <td>Rp <?=ubahToRp($totalPPN)?></td>
  <td>Rp <?=ubahToRp($totalDPP)?></td>
  <td>Rp <?=ubahToRp($totalPenjualan)?></td>
  <td colspan="3"></td>
</tr>