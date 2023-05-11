<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
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
  'form_memorial'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggal      = explode(' - ', $rentang);
$tanggalAwal  = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]);

$sql=$db->prepare('
  SELECT * FROM balistars_memorial 
  where (tanggalMemorial between ? and ?)
  and statusMemorial=?');
$sql->execute([
  $tanggalAwal,
  $tanggalAkhir,
  'Aktif']);
$hasil=$sql->fetchAll();

$n = 1;
foreach($hasil as $row){
  ?>
  <tr>
    <?php
    $disabled1  = '';
    $disabled2  = '';
    if($dataCekMenu['tipeEdit']=='0'){
       $disabled1 = 'style = "display: none;"';
    }
    if($dataCekMenu['tipeDelete']=='0'){
       $disabled2 = 'style = "display: none;"';
    }
     ?>
    <td style="vertical-align: top;"><?=$n?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditMemorial" 
              style              = "color: white;"
              onclick = "editMemorial('<?=$row['idMemorial']?>')" <?=$disabled1?> >
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelMemorial('<?=$row['idMemorial']?>')" <?=$disabled2?> 
              >
        <i class="fa fa-trash"></i>
      </button>
    </td>
    <td style="vertical-align: top;" ><?=wordwrap(ubahTanggalIndo($row['tanggalMemorial']),50,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['kodeNeracaLajur'],50,'<br>')?></td>
    <td style="vertical-align: top;" >Rp <?=wordwrap(ubahToRp($row['nilaiMemorial']),50,'<br>')?></td>
    <?php 
    if($dataCekMenu['tipeA2']==1){
      ?>
    <td style="vertical-align: top;"><?=wordwrap($row['tipe'],50,'<br>')?></td> 
    <?php
    } ?>
  </tr>
  <?php
  $n++;
}
?>