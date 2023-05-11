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
  'pembelian_faktur_pajak'
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

$sql=$db->prepare('SELECT * FROM balistars_pembelian 
  inner join balistars_cabang 
  on balistars_pembelian.idCabang=balistars_cabang.idCabang 
  where (balistars_pembelian.tanggalpembelian between ? and ?) 
  and balistars_pembelian.idSupplier!=? 
  and balistars_pembelian.tipePembelian=?
  and balistars_pembelian.status=? 
  order by balistars_pembelian.tanggalpembelian');
$sql->execute([
  $tanggalAwal,
  $tanggalAkhir,
  0,
  'A1',
  'Aktif']);
$hasil=$sql->fetchAll();


$totalPembelian=0;
$n = 1;
foreach($hasil as $row){
  $noFakturPajak = 'noFakturPajak'.$n;
  ?>
  <tr>
    <td><?=$n?></td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalPembelian']),50,'<br>')?></td>
    <td><?=wordwrap($row['namaSupplier'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['grandTotal']),50,'<br>')?></td>
    <td>
      <input type="text" class="form-control" placeholder="Input Nomor faktur Pajak" name="noFakturPajak" id="<?=$noFakturPajak?>" value="<?=$row['noFakturPajak']??''?>">
    </td>
    <td>
       <?php 
      $display = '';
      if($dataCekMenu['tipeEdit']=='0'){
       $display = 'display : none;';
      }
       ?>
      <button type="button" class="btn btn-primary" onclick="prosesPembelianFaktur('<?=$row['idPembelian']?>','<?=$noFakturPajak?>')">
      <i class="fa fa-save"></i>
    </td>
  </tr>
  <?php
  $totalPembelian+=$row['grandTotal'];
  $n++;
}
?>
<tr>
  <td colspan="4" style="text-align: center;"><b>Total</b></td>
  <td>Rp <?=ubahToRp($totalPembelian)?></td>
  <td colspan="3"></td>
</tr>