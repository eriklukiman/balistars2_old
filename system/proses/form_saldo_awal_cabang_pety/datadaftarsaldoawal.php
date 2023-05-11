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
  'form_saldo_awal_cabang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$dataCabangCash=$db->query('SELECT * FROM balistars_cabang_cash_kecil inner join balistars_cabang on balistars_cabang_cash_kecil.idCabang=balistars_cabang.idCabang order by balistars_cabang_cash_kecil.tanggalCabangCashKecil');

$n=1;
foreach($dataCabangCash as $row){
  ?>
  <tr>
    <td><?=$n?></td>
    <td>
      <?php 
      $btnwarning = 'btn-warning';
      $btnprimary = 'btn-primary';
      $klik = $row['idCabangCashKecil'];
      if($row['statusFinal']=='Final'){
        $btnwarning = 'btn-secondary';
        $btnprimary = 'btn-secondary';
        $klik = '#';
      }
       ?>
      
      <button type               = "button" 
              title              = "Edit"
              class              = "btn <?=$btnwarning?> tombolEditCabangCash" 
              style              = "color: white;"
              onclick = "editSaldoAwal(<?=$klik?>)">
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Final" 
              class   = "btn <?=$btnprimary?>" 
              onclick = "finalSaldoAwal(<?=$klik?>)">
        <i class="fa fa-check"></i>
      </button>
    </td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalCabangCashKecil']),50,'<br>')?></td>
    <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
    <td><?=wordwrap($row['keterangan'],50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($row['nilai']),50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
