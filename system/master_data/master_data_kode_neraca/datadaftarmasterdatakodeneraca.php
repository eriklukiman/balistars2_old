<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';

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
  'master_data_kode_neraca'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sqlKodeNeraca  = $db->prepare('SELECT * from balistars_kode_neraca_lajur
 where statusKodeNeracaLajur = ? 
  order by '.$parameterOrder);
$sqlKodeNeraca->execute(['Aktif']);
$dataKodeNeraca = $sqlKodeNeraca->fetchAll();

$n = 1;
foreach($dataKodeNeraca as $row){
  ?>
  <tr>
     <?php
    $disabled1  = '';
    $disabled2  = '';
    if($dataCekMenu['tipeEdit']=='0'){
       $disabled1 = 'style = "display : none;"';
    }
    if($dataCekMenu['tipeDelete']=='0'){
       $disabled2 = 'style = "display : none;"';
    }
     ?>
    <td><?=$n?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditKodeNeraca" 
              style              = "color: white;"
              onclick = "editKodeNeraca('<?=$row['idKodeNeracaLajur']?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cencelKodeNeraca('<?=$row['idKodeNeracaLajur']?>')" <?=$disabled2?>>
        <i class="fa fa-trash"></i>
      </button>
    </td>
    <td><?=wordwrap($row['kodeNeracaLajur'],50,'<br>')?></td>
    <td><?=wordwrap($row['keterangan'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>