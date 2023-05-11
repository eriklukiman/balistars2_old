<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'form_sewa_gedung'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sqlSewaGedung  = $db->prepare('
  SELECT * FROM balistars_hutang_gedung 
  inner join balistars_gedung 
  on balistars_gedung.idGedung=balistars_hutang_gedung.idGedung 
  where statusHutangGedung=?
  order by tanggalSewa DESC');
$sqlSewaGedung->execute(['Aktif']);
$dataSewaGedung = $sqlSewaGedung->fetchAll();

$n = 1;
foreach($dataSewaGedung as $row){
  $sqlCek = $db->prepare('
    SELECT * FROM balistars_hutang_gedung_pembayaran 
    where idHutangGedung =?');
  $sqlCek->execute([$row['idHutangGedung']]);
  $dataCek = $sqlCek->fetch();
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
    <td><?=$n?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditSewaGedung" 
              style              = "color: white;"
              onclick = "editSewaGedung('<?=$row['idHutangGedung']?>','<?=$row['noNota']?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button>
      <?php 
      if(!$dataCek){
        ?>
        <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelSewaGedung('<?=$row['idHutangGedung']?>','<?=$row['noNota']?>')" <?=$disabled2?>>
        <i class="fa fa-trash"></i>
      </button>
        <?php
      } ?>
      
    </td>
    <td><?=wordwrap($row['namaGedung'],50,'<br>')?></td>
    <td><?=wordwrap($row['notaSewa'],50,'<br>')?></td>
    <td><?=ubahTanggalIndo($row['tanggalSewa'],50,'<br>')?></td>
    <td><?=ubahTanggalIndo($row['tanggalPenyusutan'],50,'<br>')?></td>
    <td>Rp <?=ubahToRp($row['nilaiSewa'],50,'<br>')?></td>
    <td style="text-align: center;"><?=wordwrap($row['penyusutan'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
