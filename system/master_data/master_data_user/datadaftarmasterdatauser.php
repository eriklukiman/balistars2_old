<?php
include_once '../../../library/konfigurasiurl.php'; 
include_once $BASE_URL_PHP.'/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP.'/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP.'/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP.'/library/fungsiutilitas.php';
include_once $BASE_URL_PHP.'/system/fungsinavigasi.php';

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
  'master_data_user'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sqlUser  = $db->prepare('SELECT *, balistars_user.idUser as idUserAccount 
  from balistars_user
  inner join balistars_pegawai
  on balistars_pegawai.idPegawai=balistars_user.idPegawai 
  inner join balistars_cabang 
  on balistars_pegawai.idCabang=balistars_cabang.idCabang
  where statusUser = ? 
  and jenisUser = ?
  order by '.$parameterOrder);
$sqlUser->execute(['Aktif', 'Baru']);
$dataUser = $sqlUser->fetchAll();

$n = 1;
foreach($dataUser as $row){
  ?>
  <tr>
    <td><?=$n?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditUser" 
              onclick            = "editUser('<?=$row['idUserAccount']?>')"
              style              = "color: white;">
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelUser('<?=$row['idUserAccount']?>')">
        <i class="fa fa-trash"></i>
      </button>
      <button type="button" 
              title="Menu User" 
              class="btn btn-info" 
              onclick="getFormDetailUser('<?= $row['idPegawai'] ?>', '<?= $row['idUserAccount'] ?>')">
        <i class="fa fa-list"></i>
      </button>
    </td>
    <td><?=wordwrap($row['namaPegawai'],50,'<br>')?></td>
    <td><?=wordwrap($row['tipeUser'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
    <td><?=$row['userName']?></td>
  </tr>
  <?php
  $n++;
}
?>