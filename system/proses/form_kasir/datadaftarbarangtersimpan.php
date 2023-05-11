<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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
  'form_kasir'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$pembayaran=0;
extract($_REQUEST);
$cek=0;
$sqlItemPenjualan = $db->prepare('SELECT * FROM balistars_penjualan_detail WHERE statusCancel=? and noNota=?');
$sqlItemPenjualan->execute(['ok',$noNota]);
$dataItemPenjualan = $sqlItemPenjualan->fetchAll();
if($dataItemPenjualan){
  $cek=1;
}

$sqlTotal = $db->prepare('SELECT SUM(nilai) as total FROM balistars_penjualan_detail  WHERE noNota=? and statusCancel=?');
$sqlTotal->execute([$noNota,'ok']);
$dataTotal=$sqlTotal->fetch();

$n=1;
foreach ($dataItemPenjualan as $row) {
  ?>
  <tr>
    <td style="vertical-align: top;"><?=$n?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['jenisOrder'],30,'<br>')?> / <?=wordwrap($row['jenisPenjualan'],30,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['namaBahan'],30,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['ukuran'],30,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['finishing'],30,'<br>')?></td>
    <td style="vertical-align: top; text-align: right; padding-right: 23px;"><?=$row['qty']?></td>
    <td style="vertical-align: top; text-align: right;"><?=ubahToRp($row['hargaSatuan'])?></td>
    <td style="vertical-align: top; text-align: right; padding-right: 23px;"><?=ubahToRp($row['nilai'])?></td>
    <td style="vertical-align: top;">
      <button type="button" class="btn btn-danger" onclick="cancelBarang('<?=$row['idPenjualanDetail']?>')">
        <i class="fa fa-trash"></i>
      </button>
      <button type="button" class="btn btn-warning" onclick="editBarang('<?=$row['idPenjualanDetail']?>')">
        <i class="fa fa-edit"></i>
      </button>
    </td>
  </tr>
  <?php
   $n++;
}
?>
<?php 
$sqlUpdate=$db->prepare('SELECT * FROM balistars_penjualan where noNota = ?');
$sqlUpdate->execute([$noNota]);
$dataUpdate = $sqlUpdate->fetch();
if($dataUpdate){
  $pembayaran=ubahToRp($dataUpdate['jumlahPembayaranAwal']);
}

if($jenisPPN=='Include'){
  $subTotal = (100/(100+$persenPPN)) * $dataTotal['total'];
  $ppn = ($persenPPN/100) * $subTotal;
  $grandTotal= $dataTotal['total'];
}
else if($jenisPPN=='Exclude'){
  $subTotal = $dataTotal['total'];
  $ppn = ($persenPPN/100) * $dataTotal['total'];
  $grandTotal= ((100+$persenPPN)/100) *$dataTotal['total'];
}
else{
  $subTotal = $dataTotal['total'];
  $ppn = 0;
  $grandTotal= $dataTotal['total'];
  $style='style="display : none;"';
}
 ?>
 
  <tr>
    <td colspan="7" style="text-align: right;"><b>Sub Total</b></td>
    <td style="text-align: right; padding-right: 23px;">
      <input type="text" class="form-control" name="subTotal" id="subTotal" value="<?=ubahToRp($subTotal)?>" readonly style="text-align: right;">
    </td>
    <td></td>
  </tr>
  <tr <?=$style?>>
    <td colspan="7" style="text-align: right;"><b>PPN</b></td>
    <td style="text-align: right; padding-right: 23px;">
      <input type="text" class="form-control" name="nilaiPPN" id="nilaiPPN" value="<?=ubahToRp($ppn)?>" readonly style="text-align: right;">
    </td>
    <td></td>
  </tr>
  <tr>
    <td colspan="7" style="text-align: right;"><b>Grand Total</b></td>
    <td style="text-align: right; padding-right: 23px;">
      <input type="text" class="form-control" name="grandTotal" id="grandTotal" value="<?=ubahToRp($grandTotal)?>" readonly style="text-align: right;">
    </td>
    <td></td>
  </tr>
  <tr>
    <td colspan="7" style="text-align: right;"><b>Pembayaran</b></td>
    <td style="text-align: right; padding-right: 23px;">
      <input type="text" class="form-control" name="pembayaran" placeholder="0" id="pembayaran" onkeyup="ubahToRp('#pembayaran'); getKembalian();statusKembalian();" value="0" style="text-align: right;">
    </td>
    <td></td>
  </tr>
  <tr>
    <td colspan="7" style="text-align: right;"><b>Kembalian</b></td>
    <td style="text-align: right; padding-right: 23px;">
      <input type="text" class="form-control" placeholder="0" name="kembalian" id="kembalian" onkeyup="ubahToRp('#kembalian');"  readonly style="text-align: right;">
    </td>
    <td></td>
  </tr>
  <tr>
    <td colspan="7" style="text-align: right;"><b>Status Pembayaran</b></td>
    <td style="text-align: right; padding-right: 23px;">
      <input type="text" class="form-control" placeholder="Belum Lunas" name="statusPembayaran" id="statusPembayaran"  readonly>
    </td>
    <td></td>
  </tr>
  <?php  
  if($cek==0){
    $disabled='disabled';
  }
  else{
    $disabled='';
  }
?>
  <tr>
    <td colspan="7" style="text-align: right;"></td>
     <td style="text-align: right;">
      <button class="btn btn-primary" onclick="prosesPenjualan();" <?=$disabled?> >
        <i class="fa fa-print"></i> Print
      </button>
    </td>
    <td></td>
  </tr> 
