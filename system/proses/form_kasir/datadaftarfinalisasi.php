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
  'form_kasir'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);
$tanggal      = explode(' - ', $rentang);
$tanggalAwal  = $tanggal[0];
$tanggalAkhir = $tanggal[1];

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

$sqlPenjualan  = $db->prepare(' SELECT *, timeStamp as waktuPenjualan from balistars_penjualan where statusPenjualan=? and idCabang = ? and (tanggalPenjualan between ? and ?)  order by tanggalPenjualan
  ');
$sqlPenjualan->execute(['Aktif',$dataLogin['idCabang'],$tanggalAwal,$tanggalAkhir]);
$dataPenjualan = $sqlPenjualan->fetchAll();

$n = 1;
$totalPenjualan=0;
foreach($dataPenjualan as $row){
  $waktu=explode(' ',$row['waktuPenjualan']);
  $totalPenjualan+=$row['grandTotal']-$row['nilaiPPN'];
  ?>
  <tr>
    <td><?=$n?></td>
    <td>
      <?php  
      $btnprimary = 'btn-primary';
      $rowFinal = $row['noNota'];
      if($row['statusFinalNota']=='final'){
        $btnprimary = 'btn-secondary';
        $rowFinal = '#';
      }
       ?>
      <button type               = "button" 
              title              = "Finalisasi"
              class              = "btn <?=$btnprimary?> tombolFinalPenjualan" 
              style              = "color: white;"
              onclick = "finalisasiPenjualan('<?=$rowFinal?>')">
        <i class="fa fa-check"></i>
      </button>
      <button type    = "button"
              title   = "Print" 
              class   = "btn btn-success" 
              onclick = "window.open('print_nota/?noNota=<?=$row['noNota']?>', '_blank').focus();">
        <i class="fa fa-print"></i>
      </button>
    </td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalPenjualan']),50,'<br>')?></td>
    <td><?=wordwrap($waktu[0],50,'<br>')?></td>
    <td><?=wordwrap($row['noNota'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaCustomer'],50,'<br>')?></td>
    <td style="text-align: center;">Rp <?=wordwrap(ubahToRp($row['grandTotal']-$row['nilaiPPN']),50,'<br>')?></td>
    <td style="text-align: center;">Rp <?=wordwrap(ubahToRp($totalPenjualan),50,'<br>')?></td>
    <td><?=wordwrap($row['tipePenjualan'],50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
