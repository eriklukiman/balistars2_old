<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'form_po'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$rentang='';
$flag = '';
extract($_REQUEST);

$sqlItemPo = $db->prepare('SELECT * FROM balistars_po_detail WHERE noPo=? and statusPoDetail=?');
$sqlItemPo->execute([$noPo,'Aktif']);
$dataItemPo = $sqlItemPo->fetchAll();

$sqlTotal = $db->prepare('SELECT SUM(nilai) as total FROM balistars_po_detail  WHERE noPo=? and statusPoDetail=? ');
$sqlTotal->execute([$noPo, 'Aktif']);
$dataTotal=$sqlTotal->fetch();
$grandTotal=$dataTotal['total'];
$n=1;
foreach ($dataItemPo as $row) {
  ?>
  <tr>
    <td style="vertical-align: top;"><?=$n?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['namaBahan'],30,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['ukuran'],30,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['finishing'],30,'<br>')?></td>
    <td style="vertical-align: top; text-align: right; padding-right: 23px;"><?=$row['qty']?></td>
    <td style="vertical-align: top; text-align: right;"><?=ubahToRp($row['hargaSatuan'])?></td>
    <td style="vertical-align: top; text-align: right; padding-right: 23px;"><?=ubahToRp($row['nilai'])?></td>
    <td style="vertical-align: top;">
      <button type="button" class="btn btn-danger" onclick="cancelBarang('<?=$row['idPoDetail']?>')">
        <i class="fa fa-trash"></i>
      </button>
      <button type="button" class="btn btn-warning" onclick="editBarang('<?=$row['idPoDetail']?>')">
        <i class="fa fa-edit"></i>
      </button>
    </td>
  </tr>
  <?php
   $n++;
}
?>
<?php 
$sqlUpdate=$db->prepare('SELECT * FROM balistars_po where noPo = ?');
$sqlUpdate->execute([$noPo]);
$dataUpdate = $sqlUpdate->fetch();

 ?>
 
  
  <tr>
    <td colspan="6" style="text-align: right;"><b>Grand Total <?=$flag?></b></td>
    <td style="text-align: right; padding-right: 25px;">
      <input type="text" class="form-control" name="grandTotal" id="grandTotal"  value="<?=ubahToRp($grandTotal)?>" readonly style="text-align: right; width: 110%;">
    </td>
    <td></td>
  </tr>
  <tr>
    <td colspan="6" style="text-align: right;"></td>
     <td style="text-align: right;">
      <button class="btn btn-primary" onclick="prosesPreorder();" >
        <i class="fa fa-check"></i> Finalisasi
      </button>
    </td>
    <td> 
    <?php 
    if($flag=='update'){
      $style='style="color: white;"';
    }
    else{
       $style='style="color: white;  display: none"';
    }
     ?>  
     <a href="../po?rentang=<?=$rentang?>" 
        class="btn btn-danger btn-xl" 
        <?=$style?>>
        <i class="fa fa-arrow-left">Back</i>
      </a> 
    </td>
  </tr> 
