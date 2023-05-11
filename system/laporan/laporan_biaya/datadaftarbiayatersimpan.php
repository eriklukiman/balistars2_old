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
  'laporan_biaya'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);
$cek=0;
$grandTotal=0;
$sqlItemBiaya = $db->prepare('SELECT * 
  FROM balistars_biaya_detail 
  INNER JOIN balistars_biaya 
  ON balistars_biaya.noNota=balistars_biaya_detail.noNota 
  WHERE balistars_biaya_detail.statusCancel=? 
  and balistars_biaya_detail.noNota=?');
$sqlItemBiaya->execute(['oke',$noNota]);
$dataItemBiaya = $sqlItemBiaya->fetchAll();
if($dataItemBiaya){
  $cek=1;
}

$sqlPembayaran = $db->prepare('SELECT SUM(nilai) as totalPembayaran FROM balistars_biaya_detail  WHERE noNota=? and statusCancel=?');
$sqlPembayaran->execute([$noNota,'oke']);
$dataPembayaran=$sqlPembayaran->fetch();

$n=1;
foreach ($dataItemBiaya as $row) {
  ?>
  <tr>
    <td style="vertical-align: top;"><?=$n?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['keterangan'],30,'<br>')?></td>
    <td style="vertical-align: top; text-align: right; padding-right: 23px;"><?=$row['qty']?></td>
    <td style="vertical-align: top; text-align: right;"><?=ubahToRp($row['hargaSatuan'])?></td>
    <td style="vertical-align: top; text-align: right; padding-right: 23px;"><?=ubahToRp($row['nilai'])?></td>
    <td style="vertical-align: top;">
      <button type="button" class="btn btn-danger" onclick="cancelBarang('<?=$row['idBiayaDetail']?>')">
        <i class="fa fa-trash"></i>
      </button>
      <button type="button" class="btn btn-warning" onclick="editBarang('<?=$row['idBiayaDetail']?>')">
        <i class="fa fa-edit"></i>
      </button>
    </td>
  </tr>
  <?php
   $n++;
}
?>
<?php 
if($tipe=='A1'){
  if($jenisPPN=='Include'){
    $subTotal = (100/(100+$persenPPN)) * $dataPembayaran['totalPembayaran'];
    $ppn = ($persenPPN/100) * $subTotal;
    $grandTotal= $dataPembayaran['totalPembayaran'];
  }
  else if($jenisPPN=='Exclude'){
    $subTotal = $dataPembayaran['totalPembayaran'];
    $ppn = ($persenPPN/100) * $dataPembayaran['totalPembayaran'];
    $grandTotal= ((100+$persenPPN)/100) *$dataPembayaran['totalPembayaran'];
  }
  else{
    $subTotal = $dataPembayaran['totalPembayaran'];
    $grandTotal= $dataPembayaran['totalPembayaran'];
    $ppn = 0;
  }
} else{
  $subTotal = $dataPembayaran['totalPembayaran'];
  $grandTotal= $dataPembayaran['totalPembayaran'];
  $ppn = 0;
}

 ?>


<tr>
  <td colspan="4" style="text-align: right;"><b>Sub Total</b></td>
  <td style="text-align: right; padding-right: 23px;">
    <!-- <b id="subTotal"><?=ubahToRp($subTotal)?></b> -->
    <input type="text" class="form-control" name="subTotal" id="subTotal" value="<?=ubahToRp($subTotal)?>" readonly>
  </td>
</tr>
<?php 
if($tipe=='A1' && ($jenisPPN=='Include' || $jenisPPN=='Exclude')){
  ?>
<tr>
  <td colspan="4" style="text-align: right;"><b>PPN</b></td>
  <td style="text-align: right; padding-right: 23px;">
    <input type="text" class="form-control" name="ppn" id="ppn" value="<?=ubahToRp($ppn)?>" readonly>
  </td>
</tr>
  <?php
} else{
?>
<input type="hidden" name="ppn" id="ppn" value="<?=$ppn?>">
<?php 
} ?>
<tr>
  <td colspan="4" style="text-align: right;"><b>Grand Total</b></td>
  <td style="text-align: right; padding-right: 23px;">
    <!-- <b id="grandTotal"><?=ubahToRp($grandTotal)?></b> -->
    <input type="text" class="form-control" name="grandTotal" id="grandTotal" value="<?=ubahToRp($grandTotal)?>" readonly>
  </td>
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
  <td colspan="4" style="text-align: right;"></td>
   <td style="text-align: right;">
    <button class="btn btn-primary" onclick="prosesBiaya();" <?=$disabled?>>
      <i class="fa fa-save"></i> Save
    </button>
  </td>
</tr> 


