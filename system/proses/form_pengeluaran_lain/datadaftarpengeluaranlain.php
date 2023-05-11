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
  'form_pengeluaran_lain'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);
$tanggal = explode(' - ', $rentang);
$tanggalAwal = $tanggal[0];
$tanggalAkhir = $tanggal[1]; 

$sql = $db->prepare('SELECT * FROM balistars_user_detail inner join balistars_menu_sub ON balistars_menu_sub.idMenuSub=balistars_user_detail.idMenuSub WHERE idUser=? and namaMenuSub=?');
$sql->execute([$idUserAsli,'Pengeluaran Lain']);
$data=$sql->fetch();

$sqlPengeluaranLain  = $db->prepare('SELECT * from balistars_pengeluaran_lain inner join balistars_bank on balistars_pengeluaran_lain.idBank=balistars_bank.idBank
 where tipe = ? and (tanggalPengeluaranLain between ? and ?)
  order by tanggalPengeluaranLain DESC');
$sqlPengeluaranLain->execute([$tipe,$tanggalAwal,$tanggalAkhir]);
$dataPengeluaranLain = $sqlPengeluaranLain->fetchAll();

$n = 1;
foreach($dataPengeluaranLain as $row){
  ?>
  <tr>
    <td><?=$n?></td>
    <td>
      <?php 
      $btnwarning = 'btn-warning';
      $btnprimary = 'btn-primary';
      $rowEdit = $row['idPengeluaranLain'];
      $rowFinal = $row['idPengeluaranLain'];
      $display = '';
      if($row['statusFinal']=='Final'){
        $btnwarning = 'btn-secondary';
        $btnprimary = 'btn-secondary';
        $rowEdit = '#';
        $rowFinal = '#';
      }
      if($data['tipeEdit']=='0'){
       $display = 'display : none;';
      }
       ?>
      
      <button type               = "button" 
              title              = "Edit"
              class              = "btn <?=$btnwarning?> tombolEditPengeluaranLain" 
              style              = "color: white;"
              onclick = "editPengeluaranLain(<?=$rowEdit?>)">  
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Final" 
              class   = "btn <?=$btnprimary?>" 
              onclick = "finalPengeluaranLain(<?=$rowFinal?>)">
        <i class="fa fa-check"></i>
      </button>
      <button type               = "button" 
              title              = "buka"
              class              = "btn btn-danger" 
              style              = "color: white; <?=$display?>"
              onclick = "bukaPengeluaranLain('<?=$row['idPengeluaranLain']?>')">
        <i class="fa fa-window-close"></i>
      </button>
    </td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalPengeluaranLain']),50,'<br>')?></td>
    <td><?=wordwrap($row['namaBank'],50,'<br>')?></td>
    <td><?=wordwrap($row['keterangan'],50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['nilai']),50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
