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
  'form_mesin_bw'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sqlLogin  = $db->prepare('
  SELECT * FROM balistars_pegawai 
  inner join balistars_user 
  on balistars_pegawai.idPegawai=balistars_user.idPegawai 
  inner join balistars_cabang 
  on balistars_pegawai.idCabang=balistars_cabang.idCabang 
  where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

$tanggal = explode(' - ', $rentang);
$tanggalAwal1 = konversiTanggal($tanggal[0]);
$tanggalAwal=waktuKemarin($tanggalAwal1);
$tanggalAkhir = konversiTanggal($tanggal[1]);

$sqlPerforma=$db->prepare('
  SELECT * FROM balistars_performa_mesin_bw 
  where (tanggalPerforma between ? and ?) 
  and idCabang=? 
  order by tanggalPerforma DESC, idPerformaBW DESC');
$sqlPerforma->execute([
  $tanggalAwal,
  $tanggalAkhir,
  $dataLogin['idCabang']]);
$dataPerforma=$sqlPerforma->fetchAll();

$totalKlik=0;
$dataKlikAfter=0;
$jumlahKlik=0;
$n=0;
foreach($dataPerforma as $row){
  if($n>0){
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
     <!--  <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditPerforma" 
              style              = "color: white;"
              onclick = "editMesinBW('<?=$idPerformaBW?>')" <?=$disabled1?>>
        <i class="fa fa-edit"></i>
      </button> -->
      <?php 
      if($n==1){
        ?>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelMesinBW('<?=$idPerformaBW?>')" <?=$disabled2?>>
        <i class="fa fa-trash"></i>
      </button>
      <?php
      } ?>
    </td>
    <td><?=ubahTanggalIndo($tanggalPerforma,50,'<br>')?></td>
    <td><?=ubahToRp($row['klikBefore'],50,'<br>')?></td>
    <td><?=ubahToRp($dataKlikAfter,50,'<br>')?></td>
    <td><?=ubahToRp(($dataKlikAfter-$row['klikBefore']),50,'<br>')?></td>
  </tr>
  <?php
  $totalKlik+=($row['jumlahKlik']);
  }
  $tanggalPerforma=$row['tanggalPerforma'];
  $idPerformaBW=$row['idPerformaBW'];
  $dataKlikAfter=$row['klikBefore'];
  //$jumlahKlik=$row['jumlahKlik'];
  $n++;
}
?>
