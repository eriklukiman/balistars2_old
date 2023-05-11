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
  'master_data_biaya_klik'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sql = $db->prepare('SELECT * FROM balistars_user_detail inner join balistars_menu_sub ON balistars_menu_sub.idMenuSub=balistars_user_detail.idMenuSub WHERE idPegawai=? and namaMenuSub=?');
$sql->execute([$idUserAsli,'Master Biaya Klik']);
$data=$sql->fetch();

$sqlBiayaKlik  = $db->prepare('SELECT * from balistars_biaya_klik inner join balistars_cabang on balistars_biaya_klik.idCabang=balistars_cabang.idCabang
 where statusBiayaKlik = ? 
  order by tanggalBiaya');
$sqlBiayaKlik->execute(['Aktif']);
$dataBiayaKlik = $sqlBiayaKlik->fetchAll();

$n = 1;
foreach($dataBiayaKlik as $row){
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
              class              = "btn btn-warning tombolEditBiayaKlik" 
              style              = "color: white;"
              onclick = "editBiayaKlik('<?=$row['idBiayaKlik']?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelBiayaKlik('<?=$row['idBiayaKlik']?>')" <?=$disabled2?>>
        <i class="fa fa-trash"></i>
      </button>
    </td>
    <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalBiaya']),50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($row['jumlahBiaya']),50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
