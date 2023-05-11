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
  'form_mesin_uv'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

$flag='';
extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 

$sql=$db->prepare('SELECT balistars_penjualan_detail.noNota, tanggalPenjualan, namaBahan, ukuran, qty, idPenjualanDetail, idCabang, namaCustomer, idCustomer 
  FROM balistars_penjualan 
  inner join balistars_penjualan_detail 
  on balistars_penjualan.noNota=balistars_penjualan_detail.noNota 
  where (balistars_penjualan.tanggalPenjualan between ? and ?) 
  and balistars_penjualan_detail.jenisOrder=? 
  and balistars_penjualan_detail.statusCancel=? 
  and balistars_penjualan_detail.statusFinal=? 
  and balistars_penjualan.idCabang=? 
  order by balistars_penjualan.noNota');
$sql->execute([
  $tanggalAwal,$tanggalAkhir,
  'UV',
  "ok",
  "final",
  $dataLogin['idCabang']]);
$hasil=$sql->fetchAll();

$totalLuas=0;
$n = 1;
foreach($hasil as $row){
  $sisi=explode('x',$row['ukuran']);
  $panjang=trim($sisi[0]);
  $lebar=trim($sisi[1]);

  $panjang=(float)$panjang;
  $lebar=(float)$lebar;
  $luas=$panjang*$lebar*$row['qty']/10000;
  $luas=round($luas,2);

  $disabled='';
  $sqlCari=$db->prepare('SELECT idPerformaUV from balistars_performa_mesin_uv where idPenjualanDetail=?');
  $sqlCari->execute([$row['idPenjualanDetail']]);
  $dataCari=$sqlCari->fetch();
  if($dataCari['idPerformaUV']>0){
    $disabled='disabled';
  }
  ?>
  <tr>
    <input type="hidden" name="flag" id="flag<?=$row['idPenjualanDetail']?>" value="<?=$flag?>">
    <input type="hidden" name="idPenjualanDetail" id="idPenjualanDetail<?=$row['idPenjualanDetail']?>" value="<?=$row['idPenjualanDetail']?>">
    <input type="hidden" name="idCabang" id="idCabang<?=$row['idPenjualanDetail']?>" value="<?=$dataLogin['idCabang']?>">
    <input type="hidden" name="noNota" id="noNota<?=$row['idPenjualanDetail']?>" value="<?=$row['noNota']?>">
  
    <td><?=$n?></td>
    <td>
      <input type="text" name="tanggalPerforma" id="tanggalPerforma<?=$row['idPenjualanDetail']?>" class="form-control" value="<?=konversiTanggal($row['tanggalPenjualan'])?>" readonly>
    </td>
    <td>
      <?php 
      if($row['idCustomer']!=0){
        $sqlCustomer=$db->prepare('SELECT namaCustomer from balistars_customer where idCustomer=?');
        $sqlCustomer->execute([$row['idCustomer']]);
        $dataCustomer=$sqlCustomer->fetch();
      ?>
      <?=wordwrap($dataCustomer['namaCustomer'],50,'<br>')?>
      <?php
      } else{
      ?>
      <?=wordwrap($row['namaCustomer'],50,'<br>')?>
      <?php
      } ?>
    </td>
    <td>
      <input type="text" name="namaBahan" id="namaBahan<?=$row['idPenjualanDetail']?>" class="form-control" value="<?=$row['namaBahan']?>" <?=$disabled?>>
    </td>
    <td>
      <input type="text" onkeyup="showLuas(<?=$row['idPenjualanDetail']?>)" id="ukuran<?=$row['idPenjualanDetail']?>" name="ukuran" class="form-control" value="<?=$row['ukuran']?>" <?=$disabled?>>
    </td>
    <td>
      <input type="text" name="qty" class="form-control" id="qty<?=$row['idPenjualanDetail']?>" value="<?=$row['qty']?>" readonly>
    </td>
    <td>
      <input type="text" name="luas" class="form-control" id="luas<?=$row['idPenjualanDetail']?>" value="<?=$luas?>" readonly>
    </td>
    <td>
       <?php 
      if($disabled==''){
       ?>
       <button 
       type="button" 
       class="btn btn-primary" 
       onclick="prosesMesinUV('<?=$row['idPenjualanDetail']?>')">
      <i class="fa fa-save"></i></button>
       <?php
      } else{
       ?>
       <button 
       type="button" 
       class="btn btn-warning" 
       onclick="editMesinUV('<?=$dataCari['idPerformaUV']?>')">
      <i class="fa fa-edit"></i></button>
      <?php
      }
      ?>
    </td>
  </tr>
  <?php
  $totalLuas+=$luas;
  $n++;
}
?>
<tr>
  <td colspan="6" style="text-align: center;"><b>Total</b></td>
  <td><?=round($totalLuas)?></td>
  <td></td>
</tr>