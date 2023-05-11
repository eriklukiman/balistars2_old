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
  'setor_pettycash'
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

if($idCabang==0){
  $parameter1 =' and balistars_kas_kecil_setor.idCabang !=?';
} else{
  $parameter1 =' and balistars_kas_kecil_setor.idCabang =?';
}

$sql=$db->prepare('
  SELECT * FROM balistars_kas_kecil_setor 
  inner join balistars_bank 
  on balistars_kas_kecil_setor.idBank=balistars_bank.idBank 
  where (tanggalSetor between ? and ?)
  and statusKasKecilSetor=?'
  .$parameter1);
$sql->execute([
  $tanggalAwal,
  $tanggalAkhir,
  'Aktif',
  $idCabang]);
$hasil=$sql->fetchAll();

$n = 1;
foreach($hasil as $row){
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
      <?php 
      if($row['statusFinal']!='Final'){
      ?>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-primary" 
              style              = "color: white;"
              onclick = "finalSetorPettyCash('<?=$row['idSetor']?>')" <?=$disabled2?>>
        <i class="fa fa-check"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-warning" 
              onclick = "editSetorPettyCash('<?=$row['idSetor']?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button>
      <?php 
      } ?>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "bukaSetorPettyCash('<?=$row['idSetor']?>')" <?=$disabled2?>>
        <i class="fa fa-window-close"></i>
      </button>
    </td>
    <td style="vertical-align: top;" ><?=wordwrap(ubahTanggalIndo($row['tanggalSetor']),50,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['namaBank'],50,'<br>')?></td>
    <td style="vertical-align: top;" >Rp <?=wordwrap(ubahToRp($row['jumlahSetor']),50,'<br>')?></td>
    <td style="vertical-align: top;"><?=wordwrap($row['keterangan'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>