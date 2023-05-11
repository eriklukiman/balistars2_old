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
  'master_data_target'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);
$tanggal = explode(' - ', $rentang);
$tanggalsatu = konversiTanggal($tanggal[0]);
$tanggaldua = konversiTanggal($tanggal[1]); 

$sql = $db->prepare('SELECT * FROM balistars_user_detail inner join balistars_menu_sub ON balistars_menu_sub.idMenuSub=balistars_user_detail.idMenuSub WHERE idUser=? and namaMenuSub=?');
$sql->execute([$idUserAsli,'Master Target']);
$data=$sql->fetch();

$sqlTarget  = $db->prepare('SELECT * from balistars_target inner join balistars_cabang on balistars_target.idCabang=balistars_cabang.idCabang
 where statusTarget = ? and idJenisPenjualan=? and (tanggalAwal between ? and ?)
  order by tanggalAwal DESC');
$sqlTarget->execute(['Aktif',$idJenisPenjualan,$tanggalsatu,$tanggaldua]);
$dataTarget = $sqlTarget->fetchAll();

$n = 1;
foreach($dataTarget as $row){
  ?>
  <tr>
    <?php
    $disabled1  = '';
    $disabled2  = '';
    if($data['tipeEdit']=='0'){
       $disabled1 = 'style = "display : none;"';
    }
    if($data['tipeDelete']=='0'){
       $disabled2 = 'style = "display : none;"';
    }
     ?>
    <td><?=$n?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditTarget" 
              style              = "color: white;"
              onclick = "editTarget('<?=$row['idTarget']?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelTarget('<?=$row['idTarget']?>')" <?=$disabled2?>>
        <i class="fa fa-trash"></i>
      </button>
    </td>
    <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalAwal']),50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['target']),50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
