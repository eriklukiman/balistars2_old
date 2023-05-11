<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
include_once $BASE_URL_PHP.'/library/fungsitanggal.php';

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
  'master_data_sift'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sql = $db->prepare('SELECT * FROM balistars_user_detail inner join balistars_menu_sub ON balistars_menu_sub.idMenuSub=balistars_user_detail.idMenuSub WHERE idPegawai=? and namaMenuSub=?');
$sql->execute([$idUserAsli,'Master Sift']);
$data=$sql->fetch();

$sqlSift  = $db->prepare('SELECT *, balistars_sift.siftNormalNormal, balistars_sift.siftNormalWeekend,balistars_sift.siftPagiNormal,balistars_sift.siftPagiWeekend,balistars_sift.siftMiddleNormal,balistars_sift.siftMiddleWeekend,balistars_sift.siftSiangNormal,balistars_sift.siftSiangWeekend FROM balistars_sift inner join balistars_cabang on balistars_cabang.idCabang=balistars_sift.idCabang
 where statusSift = ? 
  order by tanggalBerlaku DESC');
$sqlSift->execute(['Aktif']);
$dataSift = $sqlSift->fetchAll();

$n = 1;
foreach($dataSift as $row){
  ?>
  <tr>
    <?php
    $disabled1  = '';
    $disabled2  = '';
    if($data['tipeEdit']=='0'){
       $disabled1 = 'style = "display: none;"';
    }
    if($data['tipeDelete']=='0'){
       $disabled2 = 'style = "display: none;"';
    }
     ?>
    <td><?=$n?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditSift" 
              style              = "color: white;"
              onclick = "editSift('<?=$row['idSift']?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cencelSift('<?=$row['idSift']?>')" <?=$disabled2?>>
        <i class="fa fa-trash"></i>
      </button>
    </td>
    <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
    <td style="text-align: center;"><?=ubahTanggalIndo($row['tanggalBerlaku'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftNormalNormal'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftNormalWeekend'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftPagiNormal'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftPagiWeekend'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftMiddleNormal'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftMiddleWeekend'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftMiddle2Normal'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftMiddle2Weekend'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftMiddle3Normal'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftMiddle3Weekend'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftSiangNormal'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['siftSiangWeekend'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
