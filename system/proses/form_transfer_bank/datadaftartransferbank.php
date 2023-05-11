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
  'form_transfer_bank'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$tipe='';
extract($_REQUEST);
$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 

$sql = $db->prepare('SELECT * FROM balistars_user_detail inner join balistars_menu_sub ON balistars_menu_sub.idMenuSub=balistars_user_detail.idMenuSub WHERE idUser=? and namaMenuSub=?');
$sql->execute([$idUserAsli,'Transfer Antar Bank']);
$data=$sql->fetch();


$sqlTransfer  = $db->prepare('SELECT *, bankAsal.namaBank as bankAsalTransfer, bankTujuan.namaBank as bankTujuanTransfer from balistars_bank_transfer inner join balistars_bank bankAsal on balistars_bank_transfer.idBankAsal=bankAsal.idBank inner join balistars_bank bankTujuan on balistars_bank_transfer.idBankTujuan=bankTujuan.idBank
 where bankAsal.tipe = ? and (tanggalTransfer between ? and ?)
  order by tanggalTransfer DESC');
$sqlTransfer->execute([$tipe,$tanggalAwal,$tanggalAkhir]);
$dataTransfer = $sqlTransfer->fetchAll();

$n = 1;
foreach($dataTransfer as $row){

  ?>
  <tr>
    <td><?=$n?></td>
    <td>
      <?php 
      $btnwarning = 'btn-warning';
      $btnprimary = 'btn-primary';
      $klik = $row['idTransferBank'];
      $display = '';
      if($row['statusTransfer']=='final'){
        $btnwarning = 'btn-secondary';
        $btnprimary = 'btn-secondary';
        $klik = '#';
      }
      if($data['tipeEdit']=='0'){
       $display = 'display: none;';
    }
       ?>
      
      <button type               = "button" 
              title              = "Edit"
              class              = "btn <?=$btnwarning?> tombolEditTransferBank" 
              style              = "color: white;"
              onclick = "editTransferBank(<?=$klik?>)">  
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Final" 
              class   = "btn <?=$btnprimary?>" 
              onclick = "finalTransferBank(<?=$klik?>)">
        <i class="fa fa-check"></i>
      </button>
      <button type               = "button" 
              title              = "buka"
              class              = "btn btn-danger" 
              style              = "color: white; <?=$display?>"
              onclick = "bukaTransferBank('<?=$row['idTransferBank']?>')">
        <i class="fa fa-window-close"></i>
      </button>
    </td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalTransfer']),50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['nilaiTransfer']),50,'<br>')?></td>
    <td><?=wordwrap($row['bankAsalTransfer'],50,'<br>')?></td>
    <td><?=wordwrap($row['bankTujuanTransfer'],50,'<br>')?></td>
    <td><?=wordwrap($row['keterangan'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
