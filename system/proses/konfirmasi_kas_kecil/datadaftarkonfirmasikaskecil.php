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
  'konfirmasi_kas_kecil'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);
$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 


$sqlKas=$db->prepare('
  SELECT * FROM balistars_kas_kecil_order 
  inner join balistars_cabang 
  on balistars_kas_kecil_order.idCabang=balistars_cabang.idCabang 
  left join balistars_bank 
  on balistars_kas_kecil_order.bankAsalTransfer= balistars_bank.idBank 
  where (balistars_kas_kecil_order.tanggalOrder between ? and ?) 
  and balistars_kas_kecil_order.idCabang=?
  and statusKasKecilOrder=?');
$sqlKas->execute([
  $tanggalAwal,
  $tanggalAkhir,
  $idCabang,
  'Aktif']);
$dataKas=$sqlKas->fetchAll();

$n = 1;
foreach($dataKas as $row){
  ?>
  <tr>
    <td style="vertical-align: top;"><?=$n?></td>
    <td style="vertical-align: top;">
       <?php 
      $btnwarning = 'btn-warning';
      $rowFinal = $row['idOrderKasKecil'];
      $display = '';
      if($row['statusApproval']=='approved'){
        $btnwarning = 'btn-secondary';
        $rowFinal = '#';
      }
      if($dataCekMenu['tipeEdit']=='0'){
       $display = 'display : none;';
      }
       ?>
      <button type    = "button"
              title   = "Final" 
              class   = "btn <?=$btnwarning?>" 
              onclick = "finalKonfirmasiKasKecil(<?=$rowFinal?>)">
        <i class="fa fa-plus-circle"></i>
      </button>
      <button type               = "button" 
              title              = "buka"
              class              = "btn btn-danger" 
              style              = "color: white; <?=$display?>"
              onclick = "bukaKonfirmasiKasKecil('<?=$row['idOrderKasKecil']?>')">
        <i class="fa fa-window-close"></i>
      </button>
    </td>
    <td style="vertical-align: top;"><?=ubahTanggalIndo($row['tanggalOrder'])?></td>
    <td style="vertical-align: top;"><?=$row['namaCabang']?></td>
    <td style="vertical-align: top;">Rp <?=ubahToRp($row['nilai'])?></td>
    <td><?=$row['keterangan']?></td>
    <td style="vertical-align: top;">Rp <?=ubahToRp($row['nilaiApproved'])?></td>
    <td style="vertical-align: top;"><?=$row['keteranganApproval']?></td>
    <td style="vertical-align: top;"><?=$row['namaBank']?></td>   
  </tr>
  <?php
  $n++;
}
?>
