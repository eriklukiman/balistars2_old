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
  'pembelian_mesin'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);
$cek=0;
$grandTotal=0;
$sqlItemPembelianMesin = $db->prepare('SELECT * FROM balistars_pembelian_mesin_detail WHERE statusCancel=? and noNota=?');
$sqlItemPembelianMesin->execute(['oke',$noNota]);
$dataItemPembelianMesin = $sqlItemPembelianMesin->fetchAll();
if($dataItemPembelianMesin){
  $cek=1;
}

$sqlPembayaran = $db->prepare('SELECT SUM(nilai) as totalPembayaran FROM balistars_pembelian_mesin_detail  WHERE noNota=? and statusCancel=?');
$sqlPembayaran->execute([$noNota,'oke']);
$dataPembayaran=$sqlPembayaran->fetch();

$n=1;
foreach ($dataItemPembelianMesin as $row) {
  ?>
  <tr>
    <td style="vertical-align: top;"><?=$n?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['namaBarang'],30,'<br>')?></td>
    <td style="vertical-align: top; text-align: right; padding-right: 23px;"><?=$row['qty']?></td>
    <td style="vertical-align: top; text-align: right;"><?=ubahToRp($row['hargaSatuan'])?></td>
    <td style="vertical-align: top; text-align: right;"><?=ubahToRp($row['diskon'])?></td>
    <td style="vertical-align: top; text-align: right; padding-right: 23px;"><?=ubahToRp($row['nilai'])?></td>
    <td style="vertical-align: top;">
      <button type="button" class="btn btn-danger" onclick="cancelBarang('<?=$row['idPembelianDetail']?>')">
        <i class="fa fa-trash"></i>
      </button>
      <button type="button" class="btn btn-warning" onclick="editBarang('<?=$row['idPembelianDetail']?>')">
        <i class="fa fa-edit"></i>
      </button>

    </td>
  </tr>
  <?php
   $n++;
}
?>
<?php 
if($tipePembelian=='A1'){
  if($jenisPPN=='Include'){
    $subTotal = (100/(100+$persenPPN)) * $dataPembayaran['totalPembayaran'];
    $nilaiPPN = ($persenPPN/100) * $subTotal;
    $grandTotal= $dataPembayaran['totalPembayaran'];
  }
  else if($jenisPPN=='Exclude'){
    $subTotal = $dataPembayaran['totalPembayaran'];
    $nilaiPPN = ($persenPPN/100) * $dataPembayaran['totalPembayaran'];
    $grandTotal= ((100+$persenPPN)/100) *$dataPembayaran['totalPembayaran'];
  }
  else{
    $subTotal = $dataPembayaran['totalPembayaran'];
    $grandTotal= $dataPembayaran['totalPembayaran'];
    $nilaiPPN = 0;
  }
} else{
  $subTotal = $dataPembayaran['totalPembayaran'];
  $grandTotal= $dataPembayaran['totalPembayaran'];
  $nilaiPPN = 0;
}

 ?>


<tr>
  <td colspan="5" style="text-align: right;"><b>Sub Total</b></td>
  <td style="text-align: right; padding-right: 23px;">
    <!-- <b id="subTotal"><?=ubahToRp($subTotal)?></b> -->
    <input type="text" class="form-control" name="subTotal" id="subTotal" value="<?=ubahToRp($subTotal)?>" readonly>
  </td>
</tr>
<?php 
if($tipePembelian=='A1' && ($jenisPPN=='Include' || $jenisPPN=='Exclude')){
  ?>
<tr>
  <td colspan="5" style="text-align: right;"><b>PPN</b></td>
  <td style="text-align: right; padding-right: 23px;">
    <input type="text" class="form-control" name="nilaiPPN" id="nilaiPPN" value="<?=ubahToRp($nilaiPPN)?>" readonly>
  </td>
</tr>
  <?php
} else{
?>
<input type="hidden" name="nilaiPPN" id="nilaiPPN" value="<?=$nilaiPPN?>">
<?php 
} ?>
<tr>
  <td colspan="5" style="text-align: right;"><b>Grand Total</b></td>
  <td style="text-align: right; padding-right: 23px;">
    <!-- <b id="grandTotal"><?=ubahToRp($grandTotal)?></b> -->
    <input type="text" class="form-control" name="grandTotal" id="grandTotal" value="<?=ubahToRp($grandTotal)?>" readonly>
  </td>
</tr>
<tr>
  <td colspan="5" style="text-align: right;"><b>Jenis Pembayaran</b></td>
  <td style="text-align: left; padding-right: 23px;">
    <select name="tipe" class="form-control select2" style="width: 100%;" required>
      <?php
      $arrayTipe=array('Giro');
      for($i=0; $i<count($arrayTipe); $i++){
        $selected=selected($arrayTipe[$i],$dataUpdate['tipe']??'');
        ?>
        <option value="<?=$arrayTipe[$i]?>" <?=$selected?>> <?=$arrayTipe[$i]?> </option>
        <?php
      }
      ?>
    </select>
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
  <td colspan="5" style="text-align: right;"></td>
   <td style="text-align: right;">
    <button class="btn btn-primary" onclick="prosesPembelianMesin();" <?=$disabled?>>
      <i class="fa fa-save"></i> Finalisasi
    </button>
  </td>
</tr> 


