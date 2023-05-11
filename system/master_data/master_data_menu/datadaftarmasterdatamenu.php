<?php
include_once '../../../library/konfigurasiurl.php'; 
include_once $BASE_URL_PHP.'/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP.'/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP.'/library/konfigurasikuncirahasia.php';

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
  'master_data_menu'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}



extract($_REQUEST);


$sqlMenu  = $db->prepare('SELECT * from balistars_menu
 where statusMenu = ? order by indexMenu');
$sqlMenu->execute(['Aktif']);
$dataMenu = $sqlMenu->fetchAll();

$n = 1;
foreach($dataMenu as $row){
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
    <td><?=$row['indexMenu']?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditMenu" 
              style              = "color: white;"
              onclick = "editMenu('<?=$row['idMenu']?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelMenu('<?=$row['idMenu']?>')" <?=$disabled2?>>
        <i class="fa fa-trash"></i>
      </button>
      <button type    = "button"
              title   = "TambahMenuSub" 
              class   = "btn btn-info" 
              onclick = "tambahMenuSub('<?=$row['idMenu']?>')" >
        <i class="fa fa-list"></i>
      </button>
    </td>
    <td style="text-align: center;"><?=wordwrap($row['namaMenu'],50,'<br>')?></td>
    <td style="text-align: center;"> <i class="<?=$row['icon']?>"></i>  <br> <?=wordwrap($row['icon'],50,'<br>')?></td>
  <?php
  $n++;
}
?>
